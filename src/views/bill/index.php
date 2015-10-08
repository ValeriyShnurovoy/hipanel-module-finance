<?php

use hipanel\modules\finance\grid\BillGridView;
use hipanel\widgets\ActionBox;

$this->title                   = Yii::t('app', 'Bills');
$this->params['breadcrumbs'][] = $this->title;
$this->params['subtitle']      = array_filter(Yii::$app->request->get($model->formName(), [])) ? 'filtered list' : 'full list';

?>

<?php $box = ActionBox::begin(['model' => $model, 'dataProvider' => $dataProvider, 'bulk' => Yii::$app->user->can('manage')]) ?>
    <?php $box->beginActions() ?>
        <?php
            if (Yii::$app->user->can('manage')) {
                print $box->renderCreateButton(Yii::t('app', 'Add payment')) . '&nbsp;';
            }
            print $box->renderCreateButton(Yii::t('app', 'Recharge account'));
        ?>
        <?= $box->renderSearchButton() ?>
        <?= $box->renderSorter([
            'attributes' => [
                'seller',
                'client',
                'sum',
                'balance',
                'type',
                'descr'
            ],
        ]) ?>
        <?= $box->renderPerPage(); ?>
    <?php $box->endActions() ?>

    <?php if (Yii::$app->user->can('manage')) { ?>
        <?= $box->renderBulkActions([
            'items' => [
                $box->renderBulkButton(Yii::t('app', 'Edit'), 'edit'),
                $box->renderDeleteButton(),
            ],
        ]) ?>
    <?php } ?>
    <?= $box->renderSearchForm(compact('type')) ?>
<?php $box->end() ?>

<?php $box->beginBulkForm() ?>
    <?= BillGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $model,
        'columns'      => [
            'checkbox', 'client_id', 'time', 'sum', 'balance',
            'type_label', 'description',
        ],
    ]) ?>
<?php $box->endBulkForm() ?>