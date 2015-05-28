{use class="yii\helpers\Html"}
{assign var=title value="$name"}
{set title=$title}

<div class="site-error">

    <h1>{Html::encode($title)}</h1>

    <div class="alert alert-danger">
        {nl2br(Html::encode($message))}
    </div>

    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>

</div>
