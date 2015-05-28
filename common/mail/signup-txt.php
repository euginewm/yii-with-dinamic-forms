<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$loginLink = Yii::$app->urlManager->createAbsoluteUrl(['user/login']);
?>
Hello <?= $user->username ?>,

Your password is: <?= $password ?>

Follow the link below to sign in into your account:

<?= $loginLink ?>
