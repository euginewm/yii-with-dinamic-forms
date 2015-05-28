{use class="yii\helpers\Html"}
{assign var=title value="About"}
{set title=$title}
{*$this->params['breadcrumbs'][] = $this->title;*}

<div class="site-about">
    <h1>{Html::encode($title)}</h1>

    <p>This is the About page. You may modify the following file to customize its content:</p>

    <code>Custom text: The __FILE__ constant is not defined and not allowed in Smarty.const</code>
</div>
