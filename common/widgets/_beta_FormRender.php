<?php
/**
 * Render Form with XML Declaration
 */

namespace common\widgets;

use Yii;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\console\Exception;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\base\View;
use yii\web\Response;
use yii\web\UrlManager;
use yii\helpers\Url;

class _beta_FormRender
{
    private static $modelFields;
    private static $formItemsNames;
    private static $filename;
    private static $RuleItems;
    private static $Model;

    private static $formSubmitAction;

    private static $formOptions;
    private static $additionalFormOptions;

    public static function processForm($formName, $controller_argumensts = [])
    {
        $result = false;

        $model = self::getModel($formName);

        if (self::ajaxValidate($model) && $model->validate()) {
            if (!empty(self::$additionalFormOptions['formSubmitAction'])) {
                $actionData = explode('::', self::$additionalFormOptions['formSubmitAction']);
                $ActionClass = 'frontend\controllers\submit_actions\\' . $actionData[0];
                $ActionClass = new $ActionClass($formName, Yii::$app->controller);
                return call_user_func_array([$ActionClass, $actionData[1]], [$model, $controller_argumensts]);
            } else {
                $result = $model;
            }
        }

        return $result;
    }

    public static function getViewName()
    {
        return (!empty(self::$additionalFormOptions['viewName'])) ?
            self::$additionalFormOptions['viewName'] : NULL;
    }

    /**
     * @param $formName string xml file with form
     */
    public static function renderForm($formName)
    {
        self::$filename = $formName;
        $form = ActiveForm::begin(self::$formOptions);

        $model = self::getModel($formName);
        foreach (self::$formItemsNames as $item) {
            $renderField = $form->field($model, $item['name']);
            print call_user_func_array([$renderField, $item['type']], $item['arguments']);
        }
        print self::renderHiddenInputs();
        print self::renderSubmitButton('Submit');

        ActiveForm::end();
    }

    public static function getModel($filename)
    {
        self::$filename = $filename;

        if (!empty(self::$Model)) {
            $model = &self::$Model;
        } else {
            self::$Model = self::prepareModel();
            $model = &self::$Model;
        }

        $model->load(Yii::$app->request->post());

        return $model;
    }

    /**
     * @param $model Model
     * @return array|null
     */
    public static function ajaxValidate($model)
    {
        if (Yii::$app->request->isAjax) {
            if ($error = ActiveForm::validate($model)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->content = $error;
                Yii::$app->response->send();
            }
        }

        return true;
    }

    private static function parseXML($formName)
    {
        $formFileXML = '../forms/' . $formName . '.xml';
        if (!file_exists($formFileXML)) {
            throw new \Exception('Form file ' . $formFileXML . ' does not exists');
        }
        $xml = simplexml_load_file($formFileXML);
        self::$formItemsNames = [];
        foreach ($xml->children() as $formItems) {
            switch ($formItems->getName()) {
                case 'formItems':
                    self::collectFormItems($formItems);
                    break;

                case 'formOptions':
                    self::collectFormOptions($formItems);
                    break;

                case 'formSubmitAction':
                    self::$formSubmitAction = (string)$formItems;
                    break;
            }
        }
    }

    private static function getWidgetArguments($widgetCallbackArgumentsChildren)
    {
        $callbackArguments = [];
        foreach ($widgetCallbackArgumentsChildren as $argument) {
            $argumentAttributes = (array)$argument;
            $argumentAttributes = $argumentAttributes['@attributes'];
            $argument = (string)$argument;

            switch ($argumentAttributes['type']) {
                case 'array':
                    $callbackArguments = [
                        $argumentAttributes['key'] => self::argumentsValueRender($argumentAttributes['valueRender'], $argument),
                    ];
                    break;

                case 'string':
                default:
                    // nothing
                    break;
            }
        }

        return $callbackArguments;

    }

    private static function getWidgetCallback($widgetCallback, $widgetCallbackAttributes)
    {
        $widgetCallbackAttributes = $widgetCallbackAttributes['@attributes'];

        switch ($widgetCallbackAttributes['type']) {
            case 'object':
                return $widgetCallback::$widgetCallbackAttributes['method']();
                break;
        }

        return null;
    }

    private static function collectHTMLAttributes($ItemHTMLAttributes)
    {
        $arguments = [];
        foreach ($ItemHTMLAttributes as $HTMLAttributes) {
            $arguments = array_merge($arguments, self::HtmlEncodeData((array)$HTMLAttributes->children()));
//            $arguments[] = self::HtmlEncodeData((array)$HTMLAttributes->children());
        }
        return $arguments;
    }

    private static function collectFormItems($formItems)
    {
        foreach ($formItems as $item) {
            $arguments = [];
            switch ((string)$item->type) {
                case 'widget':
                    $arguments[] = self::getWidgetCallback((string)$item->widgetCallback, (array)$item->widgetCallback->attributes());
                    $arguments[] = self::getWidgetArguments($item->widgetCallbackArguments->children());
                    break;

                default:
                    $arguments[] = array_merge($arguments, self::collectHTMLAttributes($item->HTMLAttributes));
                    break;

            }

            if (!empty($item->validation->rule)) {
                foreach ($item->validation->rule as $rule) {
                    $ruleAttributes = (array)$rule->attributes();
                    $ruleAttributes = (!empty($ruleAttributes['@attributes'])) ? $ruleAttributes['@attributes'] : [];
                    self::$RuleItems[] = [(string)$item->name, trim((string)$rule), $ruleAttributes];
                }
            }

            self::$formItemsNames[] = [
                'name' => (string)$item->name,
                'type' => (string)$item->type,
                'arguments' => $arguments,
            ];

            self::$modelFields[] = (string)$item->name;
        }
    }

    /**
     * Collect form options by XML formOptions
     * @param $formItems array
     */
    private static function collectFormOptions($formItems)
    {
        $Reflection = new \ReflectionClass('yii\bootstrap\ActiveForm');
        self::$formOptions = ['options' => []];
        foreach ($formItems as $item) {
            if ($Reflection->hasProperty('useToken')) {
                self::$formOptions[(string)$item->getName()] = (string)$item;
            } else {
                self::$additionalFormOptions[(string)$item->getName()] = (string)$item;
            }
        }

        self::$formOptions['enableAjaxValidation'] = (!empty(self::$formOptions['enableAjaxValidation'])
            && self::$formOptions['enableAjaxValidation'] == 'true'
        ) ? true : false;

        // get action from XML formOptions
        if (!empty(self::$formOptions['action'])) {
            self::$formOptions['action'] = Url::to([self::$formOptions['action']]);
        } else {
            // get action from context
            self::$formOptions['action'] = Url::to([Yii::$app->controller->id . '/' . Yii::$app->controller->action->id]);
        }

        // Set token param
        if (!empty(self::$additionalFormOptions['token'])) {
            self::$formOptions['action'] = Url::to([self::$formOptions['action'], 'token=' . self::$additionalFormOptions['token']]);
        }

        if (!empty(self::$formOptions['enctype'])) {
            self::$formOptions['options']['enctype'] = self::$formOptions['enctype'];
            unset(self::$formOptions['enctype']);
        }
    }


    /**
     * Render View template without Controller
     *
     * @param $templateName string the file name with form field template in //form-fields directory
     * @param $variables array
     * @return string
     */
    private static function renderFormField($templateName, $variables = [])
    {
        // todo: provide template finder
        $render = new View();
        return $render->render($templateName, $variables);
    }

    /**
     * @param $renderName string name of render type
     * @param $data string data
     * @return string rendered data
     */
    private static function argumentsValueRender($renderName, $data)
    {
        // todo: support any data than string (json, etc.)
        switch ($renderName) {
            case 'template':
                return self::renderFormField($data);
                break;

            default:
                return $data;
                break;

        }
    }

    /**
     * @param $name string html attribute "name" for button
     * @param string $value html value for button
     * @return string Html string
     */
    private static function renderSubmitButton($name, $value = 'Submit')
    {
        $output = '<div class="form-group">';
        $output .= Html::submitButton($value, ['class' => 'btn btn-primary', 'name' => $name]);
        $output .= '</div>';

        return $output;
    }

    /**
     * The values will be HTML-encoded using [[Html::encode()]].
     *
     * @param $dataArray [$key => $value] array with data to Html::encode
     * @return array Html-encided data array
     */
    private static function HtmlEncodeData($dataArray)
    {
        $HTMLEncodedData = [];
        foreach ($dataArray as $key => $value) {
            $HTMLEncodedData[$key] = Html::encode($value);
        }
        return $HTMLEncodedData;
    }

    private static function prepareModel()
    {
        // todo; provide dynamic validators search
        defined('VALIDATOR_NAMESPACE') or define('VALIDATOR_NAMESPACE', 'frontend\models\validators');
        defined('VALIDATOR_RULE_INDEX') or define('VALIDATOR_RULE_INDEX', 1);
        self::parseXML(self::$filename);
        self::collectHiddenFields();
        $model = new DynamicModel(self::$modelFields);
        // prepare rules
        foreach ((array)self::$RuleItems as $ruleItem) {
            if (class_exists(VALIDATOR_NAMESPACE . '\\' . $ruleItem[VALIDATOR_RULE_INDEX])) {
                $ruleItem[VALIDATOR_RULE_INDEX] = VALIDATOR_NAMESPACE . '\\' . $ruleItem[VALIDATOR_RULE_INDEX];
            }
            call_user_func_array([$model, 'addRule'], $ruleItem);
        }
        return $model;
    }

    private static function collectHiddenFields()
    {
        $hiddenInputs = [];
        // defualt hidden fields
        $formID = !empty(self::$additionalFormOptions['id']) ? self::$additionalFormOptions['id'] : self::$filename;
        $hiddenInputs[] = [
            'name' => 'form_id',
            'value' => $formID,
        ];
        self::$modelFields[] = 'form_id';
        // todo: collect hidden field group
        return $hiddenInputs;
    }

    private static function renderHiddenInputs()
    {
        $output = '';
        foreach (self::collectHiddenFields() as $key => $hiddenField) {
            $output .= Html::hiddenInput($hiddenField['name'], $hiddenField['value']);
        }
        return $output;
    }
}
