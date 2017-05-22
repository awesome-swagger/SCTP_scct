<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\controllers\ProjectController;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
$column = [
    ['class' => 'kartik\grid\SerialColumn'],

    //'ProjectID',
    [
        'label' => 'Project Name',
        'attribute' => 'ProjectName',
    ],
    [
        'label' => 'Project Type',
        'attribute' => 'ProjectType',
    ],
    [
        'label' => 'Project State',
        'attribute' => 'ProjectState',
    ],
    [
        'label' => 'Start Date',
        'attribute' => 'ProjectStartDate',
        'value' => function ($model) {
            return date("m/d/Y", strtotime($model['ProjectStartDate']));
        }
    ],
    [
        'label' => 'End Date',
        'attribute' => 'ProjectEndDate',
        'value' => function ($model) {
            return date("m/d/Y", strtotime($model['ProjectEndDate']));
        }
    ],

    ['class' => 'kartik\grid\ActionColumn',
        'template' => '{view} {update}',
        'urlCreator' => function ($action, $model, $key, $index) {
            if ($action === 'view') {
                $url = '/project/view?id=' . $model["ProjectID"];
                return $url;
            }
            if ($action === 'update') {
                $url = '/project/update?id=' . $model["ProjectID"];
                return $url;
            }
            if ($action === 'deactivate') {
                $url = '/project/deactivate?id=' . $model["ProjectID"];
                return $url;
            }
        },
        'buttons' => [
            'deactivate' => function ($url, $model, $key) {
                $url = '/project/deactivate?id=' . $model["ProjectID"];
                $options = [
                    'title' => Yii::t('yii', 'Deactivate'),
                    'aria-label' => Yii::t('yii', 'Deactivate'),
                    'data-confirm' => Yii::t('yii', 'Are you sure you want to deactivate this item?'),
                    'data-method' => 'Post',
                    'data-pjax' => '0',
                ];
                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
            },
        ]
    ],
];
?>
<div class="project-index">

    <h3 class="title"><?= Html::encode($this->title) ?></h3>
    <p>
        <?php if ($canCreateProjects): ?>
            <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
        <?php else: ?>
            <?= Html::a('Create Project', null, ['class' => 'btn btn-success', 'disabled' => 'disabled']) ?>
        <?php endif; ?>
    </p>
    <div id="projectSearchContainer">
        <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'formConfig' => ['labelSpan' => 7, 'deviceSize' => ActiveForm::SIZE_SMALL],
            'method' => 'get',
            'action' => Url::to(['project/index']),
            'options' => [
                'id' => 'UserForm',
            ]
        ]); ?>
        <label id="projectFilter">
            <?= $form->field($model, 'filter')->label("Search"); ?>
        </label>
        <?php ActiveForm::end(); ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'export' => false,
        'bootstrap' => false,
        'columns' => $column
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
</div>
