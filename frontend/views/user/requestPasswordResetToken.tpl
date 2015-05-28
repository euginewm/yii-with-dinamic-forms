{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{assign var=title value="Password Reset Request"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title;*}
<div class="site-request-password-reset">
    <h1>{Html::encode($title)}</h1>

    <p>Please fill out your email. A link to reset password will be sent there.</p>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('PasswordResetRequestForm')}
        </div>
    </div>
</div>
