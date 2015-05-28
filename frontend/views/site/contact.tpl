{use class="yii\helpers\Html"}
{use class="common\widgets\FormRender"}
{assign var=title value="Contact"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title;*}
<div class="site-contact">
    <h1>{Html::encode($title)}</h1>

    <p>
        If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.
    </p>

    <div class="row">
        <div class="col-lg-5">
            {FormRender::renderForm('ContactForm')}
        </div>
    </div>

    <hr/>

</div>
