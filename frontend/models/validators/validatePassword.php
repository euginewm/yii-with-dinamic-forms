<?php

namespace frontend\models\validators;

use common\models\User;
use yii\helpers\Html;
use yii\validators\Validator;
use Yii;
use yii\web\Request;

class validatePassword extends Validator
{
    private $username;

    public function init()
    {
        parent::init();
        $Request = new Request();
        $DynamicModelRequest = $Request->getBodyParam('DynamicModel');
        $this->username = Html::encode($DynamicModelRequest['username']);

        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Please, check your password');
        }
    }

    protected function validateValue($value)
    {
        $user = User::findByUsername($this->username);
        $valid = !empty($user) && $user->validatePassword($value);
        return $valid ? null : [$this->message, []];
    }
}
