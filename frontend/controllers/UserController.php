<?php

namespace frontend\controllers;

use common\widgets\FormRender;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\User;

class UserController extends Controller
{
    public $defaultAction = 'dashboard';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login','signup','request-password-reset','reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionDashboard()
    {
        return $this->render('dashboard');
    }

    /**
     * Action Login
     * @return array|string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['user']);
        }

        $result = FormRender::processForm('LoginForm');
        if ($result) {
            $model = FormRender::getModel('LoginForm');
            $user  = User::findByUsername($model->username);
            if (!is_null($user) && Yii::$app->user->login($user, $model->rememberMe ? 3600 * 24 * 30 : 0)) {
                return $this->redirect([$this->defaultAction]);
            }

            Yii::$app->getSession()->setFlash('error', 'Incorrect username or password.');
        }
        return $this->render('login');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSignup()
    {
        if ($result = FormRender::processForm('SignupForm')) {
            return $result;
        }
        return $this->render('signup');
    }

    public function actionRequestPasswordReset()
    {
        if($result = FormRender::processForm('PasswordResetRequestForm')) {
            return $result;
        }
        return $this->render('requestPasswordResetToken');
    }

    public function actionResetPassword($token)
    {
        if($result = FormRender::processForm('ResetPasswordForm', ['token' => $token])) {
            return $result;
        }
        return $this->render('resetPassword');
    }
}
