<?php

namespace frontend\controllers\submit_actions;

use yii\base\Action;
use Yii;

class ContactAction extends Action
{
    public function run($model)
    {
        $model = $model['default_model'];

        $isSendMail = Yii::$app->mailer->compose()
            ->setTo(Yii::$app->params['adminEmail'])
            ->setFrom([$model->email => $model->name])
            ->setSubject($model->subject)
            ->setTextBody($model->body)
            ->send();

        if ($isSendMail) {
            Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
        } else {
            Yii::$app->session->setFlash('error', 'There was an error sending email.');
        }

        return $this->controller->refresh();
    }
}
