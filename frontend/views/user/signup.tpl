{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{use class="yii"}
{assign var=title value="Signup"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title;*}
<div class="site-signup">
    <h1>{Html::encode(Yii::t('app',$title))}</h1>

    <p>Please fill out the following fields to signup:</p>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('SignupForm')}
        </div>
    </div>
</div>
