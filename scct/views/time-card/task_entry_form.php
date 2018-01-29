<?php
/**
 * Created by PhpStorm.
 * User: tzhang
 * Date: 2018/1/25
 * Time: 16:42
 */

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\widgets\TimePicker;
use kartik\datetime\DateTimePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="time-entry-form">
    <?php Pjax::begin(['id' => 'taskEntryModal', 'timeout' => false]) ?>
    <?php $form = ActiveForm::begin([
        'id' => 'TaskEntryForm',//$model->formName(),
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'action' => Url::to('/time-card/add-task-entry'),
        'formConfig' => ['labelSpan' => 1, 'deviceSize' => ActiveForm::SIZE_SMALL],
    ]); ?>
    <div class="form-group kv-fieldset-inline" id="time_entry_form">
        <div class="row">
            <?= Html::activeLabel($model, 'StartTime', [
                'label' => 'Start Time',
                'class' => 'col-sm-2 control-label'
            ]) ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'StartTime', [
                    'showLabels' => false
                ])->widget(TimePicker::classname(), [
                    'pluginOptions' => ['placeholder' => 'Enter time...', 'showMeridian' => false,]
                ]); ?>
            </div>

            <?= Html::activeLabel($model, 'EndTime', [
                'label' => 'End Time',
                'class' => 'col-sm-2 control-label'
            ]) ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'EndTime', [
                    'showLabels' => false
                ])->widget(TimePicker::classname(), [
                    'pluginOptions' => ['placeholder' => 'Enter time...', 'showMeridian' => false,]
                ]); ?>
            </div>
        </div>
        <div class="row">
            <?= Html::activeLabel($model, 'Date', [
                'label' => 'Date',
                'class' => 'col-sm-2 control-label'
            ]) ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'Date', [
                    'showLabels' => false
                ])->widget(\kartik\widgets\DatePicker::classname(), [
                    'options' => ['placeholder' => 'Enter Date...'],
                    'type' => \kartik\widgets\DatePicker::TYPE_COMPONENT_APPEND,
                ]); ?>
            </div>

            <?= Html::activeLabel($model, 'TaskName', [
                'label' => 'Task Name',
                'class' => 'col-sm-2 control-label'
            ]) ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'TaskName', [
                    'showLabels' => false
                ])->dropDownList($allTask); ?>
            </div>
        </div>

        <div class="row">
            <?= Html::activeLabel($model, 'ChargeOfAccountType', [
                'label' => 'Account Type',
                'class' => 'col-sm-2 control-label'
            ]) ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'ChargeOfAccountType', [
                    'showLabels' => false
                ])->dropDownList($chartOfAccountType); ?>
            </div>
        </div>
        <?= Html::activeHiddenInput($model, 'TimeCardID', ['value' => $timeCardID]); ?>
    </div>
    <br>
    <br>
    <div class="form-group">
        <?= Html::Button('Submit', ['class' => 'btn btn-success', 'id' => 'create_task_entry_submit_btn']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    <?php Pjax::end() ?>

    <script>
        $('#create_task_entry_submit_btn').click(function (event) {
            TaskEntryCreation();
            $(this).closest('.modal-dialog').parent().modal('hide');//.dialog("close");
            event.preventDefault();
            return false;
        });
    </script>
</div>