<?php
/**
 * Created by PhpStorm.
 * User: eugencherniy
 * Date: 2/9/15
 * Time: 12:12 PM
 */

namespace frontend\controllers\submit_actions;


use frontend\models\ResetPassword;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use Yii;

class RequestPasswordResetAction extends Action
{
    public function run($model, $controller_argumensts = [])
    {
        $model = $model['default_model'];

        try {
            $ResetPassword = new ResetPassword($controller_argumensts['token']);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($ResetPassword->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->controller->goHome();
        }

        Yii::$app->getSession()->setFlash('error', 'Sorry, we could not reset password you.');
        return false;
    }
}
