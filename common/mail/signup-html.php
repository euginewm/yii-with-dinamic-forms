<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$loginLink = Yii::$app->urlManager->createAbsoluteUrl(['user/login']);
?>
<div class="signup">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Your password is: <?= Html::encode($password) ?></p>

    <p>Follow the link below to sign in into your account:</p>

    <p><?= Html::a(Html::encode($loginLink), $loginLink) ?></p>
</div>
