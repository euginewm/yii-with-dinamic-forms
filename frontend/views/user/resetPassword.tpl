{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{assign var=title value="Reset Password"}
{set title=$title}
<div class="site-reset-password">
    <h1>{Html::encode($title)}</h1>

    <p>Please choose your new password:</p>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('ResetPasswordForm')}
        </div>
    </div>
</div>
