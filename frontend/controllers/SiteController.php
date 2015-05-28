<?php
namespace frontend\controllers;

use Yii;
use common\widgets\FormRender;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * CONTROLLER ACTIONS
     */

    /**
     * Action Index
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionContact()
    {
        if ($result = FormRender::processForm('ContactForm')) {
            return $result;
        }
        return $this->render('contact');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionTest()
    {
        if (FormRender::processForm('TestForm')) {
            Yii::$app->session->setFlash('error', (print_r('Form is processed', 1)));

            /*
                    $model = FormRenderer::getModel('TestForm');
                    var_dump($model, '-----------------------');

                    $model = FormRenderer::getModel('TestForm', 'FirstModel');
                    var_dump($model, '-----------------------');*/
        }

        return $this->render('test');
    }
}
