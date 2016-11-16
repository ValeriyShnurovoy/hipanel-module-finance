<?php

use hipanel\modules\finance\grid\BillGridView;
use hipanel\widgets\IndexPage;
use hipanel\widgets\Pjax;
use yii\helpers\Html;

$this->title = Yii::t('hipanel:finance', 'Bills');
$this->params['subtitle'] = array_filter(Yii::$app->request->get($model->formName(), [])) ? Yii::t('hipanel', 'filtered list') : Yii::t('hipanel', 'full list');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php Pjax::begin(array_merge(Yii::$app->params['pjax'], ['enablePushState' => true])) ?>
    <?php $page = IndexPage::begin(compact('model', 'dataProvider')) ?>

        <?php $page->setSearchFormData(compact('billTypes', 'billGroupLabels')) ?>

        <?php $page->beginContent('main-actions') ?>
            <?php if (Yii::$app->user->can('bill.create')) : ?>
                <?= Html::a(Yii::t('hipanel:finance', 'Add payment'), 'create', ['class' => 'btn btn-sm btn-success']) ?>
                <?= Html::a(Yii::t('hipanel:finance', 'Import payments'), 'import', ['class' => 'btn btn-sm btn-default']) ?>
            <?php endif ?>
            <?php if (Yii::$app->user->can('deposit')) : ?>
                <?= Html::a(Yii::t('hipanel:finance', 'Recharge account'), ['@pay/deposit'], ['class' => 'btn btn-sm btn-success']) ?>
            <?php endif ?>
        <?php $page->endContent() ?>

        <?php $page->beginContent('show-actions') ?>
            <?= $page->renderLayoutSwitcher() ?>
            <?= $page->renderSorter([
                'attributes' => [
                    'seller', 'client',
                    'sum', 'balance',
                    'type', 'descr',
                ],
            ]) ?>
            <?= $page->renderPerPage() ?>
        <?php $page->endContent() ?>

        <?php $page->beginContent('bulk-actions') ?>
            <?php if (Yii::$app->user->can('bill.create')) : ?>
                <?= $page->renderBulkButton(Yii::t('hipanel', 'Copy'), 'copy') ?>
            <?php endif ?>
            <?php if (Yii::$app->user->can('bill.update')) : ?>
                <?= $page->renderBulkButton(Yii::t('hipanel', 'Update'), 'update') ?>
            <?php endif ?>
            <?php if (Yii::$app->user->can('bill.delete')) : ?>
                <?= $page->renderBulkButton(Yii::t('hipanel', 'Delete'), 'delete', 'danger') ?>
            <?php endif ?>
        <?php $page->endContent() ?>

        <?php $page->beginContent('table') ?>
        <?php $page->beginBulkForm() ?>
            <?= BillGridView::widget([
                'boxed' => false,
                'dataProvider' => $dataProvider,
                'filterModel'  => $model,
                'columns'      => [
                    'checkbox', 'client_id', 'time', 'sum_editable', 'balance',
                    'type_label', 'description',
                ],
            ]) ?>
        <?php $page->endBulkForm() ?>
        <?php $page->endContent() ?>
    <?php $page->end() ?>
<?php Pjax::end() ?>
