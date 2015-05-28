<?php
/**
 * Created by PhpStorm.
 * User: Eugen
 * Date: 18-Feb-15
 * Time: 02:11 PM
 */

namespace common\widgets;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\View;
use yii\widgets\ActiveForm;

// todo: move code to another place
interface iFormParser
{
    public static function readFormOptions();

    // todo: fill method list
}

class FormParserXML implements iFormParser
{
    public static $filename;
    private static $xml;

    public static $defaultModelName = 'default_model';


    public static function read($filename)
    {
        self::$filename = $filename;
        // todo: get actual file path here...
        $formFileXML = '../forms/' . self::$filename . '.xml';
        if (!file_exists($formFileXML)) {
            throw new \Exception('Form file ' . $formFileXML . ' does not exists');
        }
        self::$xml = simplexml_load_file($formFileXML);
    }

    public static function readFormOptions()
    {
        $options = [];
        foreach (self::$xml->children() as $formItems) {
            if ($formItems->getName() == 'formOptions') {
                foreach ($formItems as $item) {
                    $options[(string)$item->getName()] = (string)$item;
                }
            }
        }

        return $options;
    }

    public static function readFormItems()
    {
        $items = [];
        foreach (self::$xml->children() as $formItems) {
            if ($formItems->getName() == 'formItems') {
                foreach ($formItems as $item) {

                    $readArguments = function ($item) {
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
                        return $arguments;
                    };

                    $readRuleItems = function ($item) {
                        $RuleItems = [];
                        if (!empty($item->validation->rule)) {
                            foreach ($item->validation->rule as $rule) {
                                $ruleAttributes = (array)$rule->attributes();
                                $ruleAttributes = (!empty($ruleAttributes['@attributes'])) ? $ruleAttributes['@attributes'] : [];
                                $RuleItems[] = [(string)$item->name, trim((string)$rule), $ruleAttributes];
                            }
                        }
                        return $RuleItems;
                    };

                    $readModelName = function($item) {
                        $modelName = (string)$item->model;
                        if(empty($modelName)) {
                            $modelName = self::$defaultModelName;
                        }
                        return $modelName;
                    };

                    $items[] = [
                        'name' => (string)$item->name,
                        'type' => (string)$item->type,
                        'arguments' => $readArguments($item),
                        'model' => $readModelName($item),
                        'rules' => $readRuleItems($item),
                    ];
                }
            }
        }
        return $items;
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

    private static function renderFormField($templateName, $variables = [])
    {
        // todo: provide template finder
        $render = new View();
        return $render->render($templateName, $variables);
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
        }
        return $arguments;
    }

    private static function HtmlEncodeData($dataArray)
    {
        $HTMLEncodedData = [];
        foreach ($dataArray as $key => $value) {
            $HTMLEncodedData[$key] = Html::encode($value);
        }
        return $HTMLEncodedData;
    }
}

class FormRender extends FormParserXML
{
    private static $models = [];
    private static $formSubmitAction = '';

    private static $formOptions = [
        'options' => [],
        'id' => '',
        'action' => '',
        'method' => 'POST',
    ];
    private static $formItems = [];
    public static $scope = [
        'frontend\controllers\submit_actions\\',
        'frontend\actions\\',
        'backend\controllers\submit_actions\\',
        'backend\actions\\',
        'common\submit_actions\\',
        'common\actions\\',
    ];

    /**
     * Render form from FromParser class to HTML string
     *
     * @param $formName string form name. File name with form name and extension: FORM_NAME.EXT
     * @print Html output with form and form fields
     */
    public static function renderForm($formName)
    {
        $form = ActiveForm::begin(self::getFormOptions($formName));

        /**
         * Render form fields
         */
        foreach (self::getFormItems() as $item) {
            $renderField = $form->field(self::getModel(null, $item['model']), $item['name']);
            print call_user_func_array([$renderField, $item['type']], $item['arguments']);
        }

        /**
         * Render form hidden inputs
         */
        $renderHiddenInputs = function ($formName) {
            // todo: render hidden inputs by form too...
            $formOptions = self::getFormOptions($formName);
            // default hidden field with Form ID
            $hiddenFields[] = [
                'name' => 'form_id',
                'value' => $formOptions['id'],
            ];
            $output = '';
            foreach ($hiddenFields as $key => $hiddenField) {
                $output .= Html::hiddenInput($hiddenField['name'], $hiddenField['value']);
            }
            return $output;
        };
        print $renderHiddenInputs($formName);

        /**
         * Render form buttons
         */
        // todo: render submit button
        $renderSubmitButton = function ($name = 'submit', $value = 'Submit') {
            $output = '<div class="form-group">';
            $output .= Html::submitButton(Yii::t('app', $value), ['class' => 'btn btn-primary', 'name' => $name]);
            $output .= '</div>';
            return $output;
        };
        print $renderSubmitButton();

        ActiveForm::end();
    }

    private static function getModelNamesList($formName)
    {
        // todo: get actual list, no static
        return ['default_model', 'FirstModel'];
    }

    private static function getModelInstanceList($formName)
    {
        $modelInstances = [];
        foreach (self::getModelNamesList($formName) as $modelName) {
            $modelInstances[] = self::getModel($formName, $modelName);
        }
        return $modelInstances;
    }

    /**
     * @param $formName string form name. File name with form name and extension: FORM_NAME.EXT
     * @return bool
     */
    public static function processForm($formName, $controller_arguments = [])
    {
        $result = false;

        $error = false;
        self::ajaxValidate($formName, self::getModelNamesList($formName));
        if (Yii::$app->request->post()) {
            foreach (self::getModelNamesList($formName) as $modelName) {
                $model = self::getModel($formName, $modelName);
                if (!$model->validate()) {
                    $error = true;
                }
            }

            if (!$error && Yii::$app->request->post()) {
                if (!empty(self::$formSubmitAction)) {
                    $actionData = explode('::', self::$formSubmitAction);
                    $actionClass = self::findClass($actionData[0]);
                    $actionMethod = (!empty($actionData[1])) ? $actionData[1] : 'run';
                    $ActionClass = new $actionClass($formName, Yii::$app->controller);
                    $result = call_user_func_array([$ActionClass, $actionMethod], [self::$models, $controller_arguments]);
                }
            }
        }

        return $result;
    }

    private static function findClass($className)
    {
        $i = -1;
        $classNamespace = false;
        while (!$classNamespace) {
            if (class_exists(self::$scope[++$i] . $className)) {
                $classNamespace = self::$scope[$i];
            }
        }
        return $classNamespace . $className;
    }

    private static function ajaxValidate($formName)
    {
        $formOptions = self::getFormOptions($formName);
        if (!empty($formOptions['enableAjaxValidation']) && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;


            Yii::$app->response->data[] = call_user_func_array(['yii\widgets\ActiveForm', 'validate'], self::getModelInstanceList($formName));
            Yii::$app->response->send();
        }
    }

    /**
     * Get DynamicModel by model name
     * @param string $formName
     * @param string $modelName
     * @return DynamicModel
     */
    public static function getModel($formName = '', $modelName = '')
    {
        $formName = !empty($formName) ? $formName : parent::$filename;
        if (empty($modelName)) $modelName = self::$defaultModelName;
        if (empty(self::$models[$modelName])) {
            self::prepareModel($formName, $modelName);
        }

        $model = &self::$models[$modelName];
        $model->load(Yii::$app->request->post());

        return $model;
    }

    public static function prepareModel($formName, $modelName)
    {
        $getFieldNames = function ($formName, $modelName) {
            $result = [];
            parent::read($formName);
            foreach (self::getFormItems() as $fields) {
                if ($fields['model'] == $modelName) {
                    $result[] = $fields['name'];
                }
            }
            return $result;
        };

        $getFieldRules = function ($formName, $modelName) {
            $result = [];
            parent::read($formName);
            foreach (self::getFormItems() as $fields) {
                if ($fields['model'] == $modelName) {
                    $result[] = $fields['rules'];
                }
            }
            return $result;
        };

        /**
         * Create model with fields
         */
        $model = new DynamicModel($getFieldNames($formName, $modelName));

        /**
         * Add Rules to model
         */
        foreach ($getFieldRules($formName, $modelName) as $ruleItems) {
            foreach ($ruleItems as $rule) {
                call_user_func_array([$model, 'addRule'], $rule);
            }
        }

        self::$models[$modelName] = $model;
    }

    /**
     * Get form options
     * @return array
     */
    private static function getFormOptions($formName)
    {
        parent::read($formName);

        $formOptions = array_merge(parent::readFormOptions(), self::$formOptions);

        $prepareFormOptionID = function (&$formOptions) {
            $formOptions['id'] = parent::$filename;
        };

        $prepareFormOptionEnctype = function (&$formOptions) {
            $formOptions['options']['enctype'] = $formOptions['enctype'];
            unset($formOptions['enctype']);
        };

        $prepareFormOptionAction = function (&$formOptions) {
            if (empty($formOptions['action'])) {
                $formOptions['action'] = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
            }
            $formOptions['action'] = Url::to([$formOptions['action']]);
        };

        $getFormOptionActionClass = function (&$formOptions) {
            self::setSubmitActionClass($formOptions['formSubmitAction']);
            unset($formOptions['formSubmitAction']);
        };

        $prepareFormOptionID($formOptions);
        $prepareFormOptionEnctype($formOptions);
        $prepareFormOptionAction($formOptions);
        $getFormOptionActionClass($formOptions);

        return $formOptions;
    }

    private static function setSubmitActionClass($formSubmitAction = '')
    {
        self::$formSubmitAction = $formSubmitAction;
    }

    /**
     * Get form items without action buttons and hidden inputs
     * @return array
     */
    private static function getFormItems()
    {
        $formItems = array_merge(parent::readFormItems(), self::$formItems);
        return $formItems;
    }
}