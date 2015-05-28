{use class="yii\helpers\Html"}
{*$this->title = Yii::t('admin', 'Create {modelClass}', ['modelClass' => 'Admin']);*}
{*$this->params['breadcrumbs'][] = ['label' => Yii::t('admin', 'Admins'), 'url' => ['index']];*}
{*$this->params['breadcrumbs'][] = $this->title;*}
<div class="admin-create">

    <h1>{Html::encode($this->title)}</h1>

    {$this->render('_form')}

</div>
