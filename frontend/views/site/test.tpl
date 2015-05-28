{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{assign var=title value="Test"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title;*}
<div class="site-test">
    <h1>{Html::encode($title)}</h1>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('TestForm')}
        </div>
    </div>

    <hr/>

</div>
