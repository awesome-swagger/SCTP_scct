<?php
/**
 * Created by PhpStorm.
 * User: tzhang
 * Date: 10/11/2017
 * Time: 1:16 PM
 */
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;

?>
<div id="assignedaddsurveyordialogtitle">
    <div id="add-surveyor-dropDownList-form">
        <?php yii\widgets\Pjax::begin(['id' => 'addSurveyorForm']) ?>
        <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_VERTICAL,
        ]); ?>
        <div class="addsurveryContainer">
            <div id="addsurveyorSearchcontainer">
                <?= $form->field($model, 'modalSearch')->textInput(['value' => $searchFilterVal, 'id' => 'addSurveyorSearchCge', 'placeholder'=>'Search'])->label('Surveyor / Inspector'); ?>
            </div>
            <?php echo Html::img('@web/logo/filter_clear_black.png', ['id' => 'SurveyorModalCleanFilterButton']) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <?php yii\widgets\Pjax::end() ?>
    </div>
</div>
<div id="dispatchSurveyorsTable">
    <?php Pjax::begin([
        'id' => 'addSurveyorsGridviewPJAX',
        'timeout' => 10000,
        'enablePushState' => false  ]) ?>

    <?= GridView::widget([
        'id' =>'surveyorGV',
        'dataProvider' => $addSurveyorsDataProvider,
        'export' => false,
        'pjax' =>true,
        'pjaxSettings' => [
            'options' => [
                'id' => 'addSurveyorsGridview',
            ],
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\CheckboxColumn',
                'header' => 'Select',
                'contentOptions' => ['class' => 'cgeAddSurveyor'],
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    if (!empty($addSurveyorsDataProvider)) {
                        return ['UserID' => $model["UserID"]];
                    }
                },
            ],
            [
                'label' => 'Name',
                'attribute' => 'Name',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'User Name',
                'attribute' => 'UserName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <?php Pjax::end() ?>
</div>
<div id="addSurveyorsDispatchBtn">
    <?php echo Html::button('DISPATCH', [ 'class' => 'btn btn-primary modalDispatchCgeBtn', 'id' => 'addSurveyorDispatchButton' ]);?>
</div>
<script type="text/javascript">

    // set trigger for search box in the add surveyor modal
    $(document).ready(function () {
		$('.modalDispatchCgeBtn').prop('disabled', true);
		applyCgeDispatchTableListeners();
		
		//search filter listener
        $('#addSurveyorSearchCge').keypress(function (event) {
            var key = event.which;
            if (key == 13) {
                var searchFilterVal = $('#addSurveyorSearchCge').val();
                if (event.keyCode == 13) {
                    event.preventDefault();
                    reloadCgeAssetsModal(searchFilterVal);
                }
            }
        });
		
		//SurveyorModal CleanFilterButton listener
		$('#SurveyorModalCleanFilterButton').click(function () {
			$("#addSurveyorSearchCge").val("");
			var searchFilterVal = $('#addSurveyorSearchCge').val();
			reloadCgeAssetsModal(searchFilterVal);
		});

        $('.modalDispatchCgeBtn').click(function () {
            var form = $("#cgeActiveForm");
            if (!assignedUserIDs || assignedUserIDs.length == 1) {
                // Ajax post request to dispatch action
                $.ajax({
                    timeout: 99999,
                    url: '/dispatch/cge/dispatch',
                    data: {dispatchMap: dispatchMapGridData, dispatchAsset: dispatchAssetsData},
                    type: 'POST',
                    beforeSend: function () {
                        $('#addSurveyorCgeModal').modal("hide");
                        $('#loading').show();
                    }
                }).done(function () {
                    $.pjax.reload({
                        container:'#cgeGridview',
                        timeout: 99999,
                        type: 'GET',
                        url: form.attr("action"),
                        data: form.serialize()
                    });
                    $('#cgeGridview').on('pjax:success', function() {
						resetCge_Global_Variable();
                        $("#cgeDispatchButton").prop('disabled', true);
                        $('#loading').hide();
                    });
                    $('#cgeGridview').on('pjax:error', function(e) {
                        e.preventDefault();
                    });
                });
            }
        });
    });
	
	function applyCgeDispatchTableListeners()
    {
        $(".cgeAddSurveyor input[type=checkbox]").click(function () {
            assignedUserIDs = $("#addSurveyorsGridview #surveyorGV").yiiGridView('getSelectedRows');
            dispatchMapGridData = getCgeDispatchMapGridData(assignedUserIDs);
            dispatchAssetsData = getCgeDispatchAssetsData(assignedUserIDs);
            if (assignedUserIDs.length == 1) {
                $('.modalDispatchCgeBtn').prop('disabled', false); //TO DISABLED
            } else {
                $('.modalDispatchCgeBtn').prop('disabled', true); //TO DISABLED
            }
        });
    };

    function reloadCgeAssetsModal(searchFilterVal) {
		$('#loading').show();
        $.pjax.reload({
            type: 'POST',
            url: '/dispatch/add-surveyor-modal/add-surveyor-modal?modalName=cge',
            container: '#addSurveyorsGridviewPJAX', // id to update content
            data: {searchFilterVal: searchFilterVal},
            timeout: 99999,
            push: false,
            replace: false
        }).done(function () {
			applyCgeDispatchTableListeners();
            $("body").css("cursor", "default");
            $('.modalDispatchCgeBtn').prop('disabled', true);
			$('#loading').hide();
        });
    }

    
</script>