<?php

namespace common\actions;

use common\widgets\FormRender;
use yii\base\Action;
use Yii;

class FormRenderAction extends Action
{
    public $FormName;
    public $ViewName;

    public function run()
    {
        $formName = $this->getFormName();
        if (!empty($formName) && $result = FormRender::processForm($formName)) {
            return $result;
        }

        return $this->controller->render($this->getViewName());
    }

    private function getFormName()
    {

        if (empty($this->FormName)) {
            $form_id = Yii::$app->request->post('form_id');
            if (!empty($form_id)) {
                $this->FormName = $form_id;
            }
        }

        return $this->FormName;
    }

    private function getViewName()
    {
        if (empty($this->ViewName)) {
            // find view name by XML option "viewName"
            $this->ViewName = FormRender::getViewName();
            if (empty($this->ViewName)) {
                // find view by controller/action view
                $this->ViewName = '/' . $this->controller->id . '/' . $this->controller->action->id;
            }
        }

        if ($this->ViewName{0} != '/') $this->ViewName = '/' . $this->ViewName;
        return $this->ViewName;
    }
}
