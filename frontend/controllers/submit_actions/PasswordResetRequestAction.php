<?php
/**
 * Created by PhpStorm.
 * User: eugencherniy
 * Date: 2/9/15
 * Time: 11:56 AM
 */

namespace frontend\controllers\submit_actions;


use common\models\User;
use yii\base\Action;
use Yii;

class PasswordResetRequestAction extends Action
{
    public function run($model)
    {
        $model = $model['default_model'];

        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $model->email,
        ]);

        if ($user) {
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                $sendEmail = Yii::$app->mailer->compose(['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'], ['user' => $user])
                    ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                    ->setTo($model->email)
                    ->setSubject('Password reset for ' . Yii::$app->name)
                    ->send();

                if ($sendEmail) {
                    Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                    return $this->controller->goHome();
                } else {
                    Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
                }
            }
        }

        Yii::$app->getSession()->setFlash('error', 'Sorry, we could not reset password you.');
        return false;
    }
}
