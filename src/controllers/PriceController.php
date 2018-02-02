<?php

namespace hipanel\modules\finance\controllers;

use hipanel\actions\IndexAction;
use hipanel\actions\RedirectAction;
use hipanel\actions\SmartCreateAction;
use hipanel\actions\SmartDeleteAction;
use hipanel\actions\SmartUpdateAction;
use hipanel\actions\ValidateFormAction;
use hipanel\actions\ViewAction;
use hipanel\base\CrudController;
use hipanel\helpers\ArrayHelper;
use hipanel\modules\finance\actions\PriceUpdateAction;
use hipanel\modules\finance\models\TargetObject;
use hipanel\modules\finance\models\Plan;
use hipanel\modules\finance\models\Price;
use Yii;
use yii\base\Event;

class PriceController extends CrudController
{
    public function actions()
    {
        return array_merge(parent::actions(), [
            'index' => [
                'class' => IndexAction::class,
                'on beforePerform' => function (Event $event) {
                    $action = $event->sender;
                    $action->getDataProvider()->query
                        ->addSelect('main_object_id')
                        ->joinWith('object')
                        ->joinWith('plan');
                },
            ],
            'view' => [
                'class' => ViewAction::class,
            ],
            'create' => [
                'class' => SmartCreateAction::class,
                'data' => function ($action, $data) {
                    $plan = null;
                    if ($plan_id = Yii::$app->request->get('plan_id')) {
                        $plan = Plan::findOne(['id' => $plan_id]);
                    }

                    return compact('plan');
                },
                'success' => Yii::t('hipanel.finance.price', 'Prices were successfully created')
            ],
            'create-suggested' => [
                'class' => SmartCreateAction::class,
                'scenario' => 'create',
                'POST' => [
                    'save' => true,
                    'success' => [
                        'class' => RedirectAction::class,
                        'url' => function (RedirectAction $action) {
                            return ['@plan/view', 'id' => $action->collection->getModel()->plan_id];
                        },
                    ],
                ],
                'success' => Yii::t('hipanel.finance.price', 'Prices were successfully created')
            ],
            'update' => [
                'class' => PriceUpdateAction::class,
                'success' => Yii::t('hipanel.finance.price', 'Prices were successfully updated'),
                'on beforeFetch' => function (Event $event) {
                    /** @var \hipanel\actions\SearchAction $action */
                    $action = $event->sender;
                    $dataProvider = $action->getDataProvider();
                    $dataProvider->query->joinWith('object');
                },
            ],
            'delete' => [
                'class' => SmartDeleteAction::class,
                'success' => Yii::t('hipanel.finance.price', 'Prices were successfully deleted')
            ],
            'set-note' => [
                'class' => SmartUpdateAction::class,
                'success' => Yii::t('hipanel', 'Note changed'),
            ],
            'validate-form' => [
                'class' => ValidateFormAction::class,
            ],
        ]);
    }

    public function actionSuggest($object_id, $plan_id, $type = 'default')
    {
        $plan = Plan::findOne(['id' => $plan_id]);

        $suggestions = (new Price)->batchQuery('suggest', [
            'object_id' => $object_id,
            'plan_id' => $plan_id,
            'type' => $type,
        ]);

        $models = [];
        foreach ($suggestions as $suggestion) {
            $object = ArrayHelper::remove($suggestion, 'object');

            $price = new Price($suggestion);
            $price->populateRelation('object', new TargetObject($object));

            $models[] = $price;
        }

        return $this->render('suggested', [
            'type' => $type,
            'model' => reset($models),
            'models' => $models,
            'plan' => $plan,
        ]);
    }
}