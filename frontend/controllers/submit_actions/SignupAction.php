<?php
namespace frontend\controllers\submit_actions;

use common\models\User;
use yii\base\Action;
use Yii;

class SignupAction extends Action
{
    public function run($model, $controller_argumensts = [])
    {
        $model = $model['default_model'];

        $user = new User();
        $user->username = $model->username;
        $user->email = $model->email;
        $password = Yii::$app->security->generateRandomString(Yii::$app->params['user.signupPasswordLength']);
        $user->setPassword($password);
        $user->generateAuthKey();
        if ($user->save()) {
            $sendEmail = Yii::$app->mailer->compose(
                    ['html' => 'signup-html', 'text' => 'signup-txt'],
                    ['user' => $user, 'password' => $password]
                )
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                ->setTo($model->email)
                ->setSubject('Signup confirmation for ' . Yii::$app->name)
                ->send();

            if ($sendEmail) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for the access details.');
                return $this->controller->redirect(['user/login']);
            }
        }

        Yii::$app->getSession()->setFlash('error', 'Sorry, we could not register you.');
        return false;
    }
}
