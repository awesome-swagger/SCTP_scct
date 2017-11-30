<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clients';
$this->params['breadcrumbs'][] = $this->title;
$column = [
    ['class' => 'kartik\grid\SerialColumn'],
    [
        'label' => 'AccountID',
        'attribute' => 'ClientAccountID',
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'filter' => '<input class="form-control" name="filterclientaccountID" value="' . Html::encode($searchModel['ClientName']) . '" type="text">'
    ],
    //'ClientID',
    [
        'label' => 'Name',
        'attribute' => 'ClientName',
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'filter' => '<input class="form-control" name="filterclientname" value="' . Html::encode($searchModel['ClientName']) . '" type="text">'
    ],
    [
        'label' => 'Client City',
        'attribute' => 'ClientCity',
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'filter' => '<input class="form-control" name="filtercity" value="' . Html::encode($searchModel['ClientCity']) . '" type="text">'
    ],
    [
        'label' => 'Client State',
        'attribute' => 'ClientState',
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'filter' => '<input class="form-control" name="filterstate" value="' . Html::encode($searchModel['ClientState']) . '" type="text">'
    ],
    ['class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update}',
        'urlCreator' => function ($action, $model, $key, $index) {
            if ($action === 'view') {
                $url = '/client/view?id=' . $model["ClientID"];
                return $url;
            }
            if ($action === 'update') {
                $url = '/client/update?id=' . $model["ClientID"];
                return $url;
            }
        },
    ],
];
?>
<div class="client-index">

    <h3 class="title"><?= Html::encode($this->title) ?></h3>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p style="float: left;">
        <?= Html::a('Create Client', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 5, 'deviceSize' => ActiveForm::SIZE_SMALL],
        'method' => 'get',
        'action' => Url::to(['client/index']),
        'options' => [
            'id' => 'ClientForm',
        ]
    ]); ?>

    <label id="clientFilter" style="width: 40%;;">
        <?= $form->field($model, 'filter')->textInput(['value' => $filter, 'id' => 'clientSearchField' ])->label("Search"); ?>
        <?php echo Html::img('@web/logo/filter_clear_black.png', ['id' => 'clientSearchCleanFilterButton']) ?>
    </label>
    <?php ActiveForm::end(); ?>

    <?php Pjax::begin(['id' => 'clientGridview', 'timeout' => false]) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'export' => false,
        'bootstrap' => false,
        'columns' => $column,
        'id' => 'clientGV'
    ]); ?>
    <div id="UserPagination">
        <?php
        echo LinkPager::widget([
            'pagination' => $pages,
        ]);
        ?>
    </div>
    <div class="GridviewTotalNumber">
        <?php echo "Showing " . ($pages->offset + 1) . " to " . ($pages->offset + $pages->getPageSize()) . " of " . $pages->totalCount . " entries"; ?>
    </div>
    <?php Pjax::end() ?>
</div>
