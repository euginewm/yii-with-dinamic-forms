{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{assign var=title value="Login"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title*}
<div class="site-login">
    <h1>{Html::encode($title)}</h1>

    <p>Please fill out the following fields to login:</p>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('LoginForm')}
            {Html::a('reset it', ['user/request-password-reset'])}
        </div>
    </div>
</div>
