<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\models\mileagecard */

//$this->title = $model->MileageCardID;
$this->params['breadcrumbs'][] = ['label' => 'MileageCard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mileagecard-view">

    <h1><?= Html::encode($this->title) ?></h1>

	<?php 
			$approveUrl = urldecode(Url::to(['mileage-card/approve', 'id' => $model["MileageCardID"]]));
	?>
    <p>
		<?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Approve', $approveUrl, [
											 'class' => 'btn btn-primary', 
											 'data' => [
														'confirm' => 'Are you sure you want to approve this item?']
													])?>
    </p>
	
	<!--Sunday TableView-->
	<h2 class="mileage_entry_header">Sunday</h2>

	<?php Pjax::begin(); ?>
	<?= GridView::widget([
		'dataProvider' => $SundayProvider,
		'columns' => [
			'MileageCardType',
			'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',			
		],
	]);	
	?>
	<?php Pjax::end();?>
	<?php 
			$url = urldecode(Url::to(['mileage-card/create-mileage-entry', 'id' => $model["MileageCardID"]]));
	?>
	<p>
		<?= Html::button('Create New', ['value'=>$url, 'class' => 'btn btn-success', 'id' => 'MileagemodalButtonSunday']) ?>
		<span class="totalhours"><?php echo "Total mileage is : ".$Total_Mileage_Sun?></span>
	</p>
	
	<?php
		Modal::begin([
			'header' => '<h4>Sunday</h4>',
			'id' => 'MileagemodalSunday',
			'size' => 'modal-lg',
		]);

		echo "<div id='modalContentMileageSunday'></div>";

		Modal::end();
	?>
	<br />     

	<?php  

        // JS: Update response handling
        $this->registerJs(
			'jQuery(document).ready(function($){
				$(document).ready(function () {
					$("body").on("beforeSubmit", "form#SundayEntry", function () {
						var form = $(this);
						// return false if form still have some validation errors
						if (form.find(".has-error").length) {
							return false;
						}
						// submit form
						$.ajax({
							url    : form.attr("action"),
							type   : "post",
							data   : form.serialize(),
							success: function (response) {
								$("#modalSunday").modal("toggle");
								$.pjax.reload({container:"#SundayEntry"}); //for pjax update
							},
							error  : function () {
								console.log("internal server error");
							}
						});
						return false;
					});
				});
			});'
		); ?>
	
	<!--Monday TableView-->
	<h2 class="mileage_entry_header">Monday</h2>
	<?php  
		Modal::begin([
				'header' => '<h4>Monday</h4>',
				'id' => 'modal',
				'size' => 'modal-lg',
		]);
		
		echo "<div id='modalContent'></div>";
		
		Modal::end();
	?>
	
	<?php Pjax::begin(); ?>
	<?= GridView::widget([
		'dataProvider' => $MondayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
			
			[   
				'class' => 'yii\grid\ActionColumn', 
				'template' => '{view} {update}',
				'headerOptions' => ['width' => '5%', 'class' => 'activity-view-link',],        
					'contentOptions' => ['class' => 'padding-left-5px'],

				'buttons' => [
					'view' => function ($url, $model, $key) {
						return Html::a('<span class="glyphicon glyphicon-eye-open"></span>','apicall', [
								'id' => 'activity-view-link',
								'title' => Yii::t('yii', 'View'),
								'data-toggle' => 'modal',
								'data-target' => '#activity-modal',
								'data-id' => $key,
								'data-pjax' => '0',

						]);
					},
				],
			],
		],
	])?>
	
	<?php Pjax::end();?>
	
	<?php

	Modal::begin([
		'header' => '<h4 class="modal-title">Create New</b></h4>',
		'toggleButton' => ['label' => 'Create New'],
		'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
	]);

	echo 'Say hello...';

	Modal::end();
	?>
	<br />     

	<?php $this->registerJs(
		"$('.activity-view-link').click(function() {
			$.get(
				'imgview',         
				{
					id: $(this).closest('tr').data('key')
				},
				function (data) {
					$('.modal-body').html(data);
					$('#activity-modal').modal();
				}  
			);
		});"
	); ?>
	
	<?php Modal::begin([
		'id' => 'activity-modal',
		'header' => '<h4 class="modal-title">View</h4>',
		'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',

	]); ?>
	<?php Modal::end(); ?>
	
	<!--Tuesday TableView-->
	<h2 class="mileage_entry_header">Tuesday</h2>
	<?= GridView::widget([
		'dataProvider' => $TuesdayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
		]
	])?>
	
	<!--Wednesday TableView-->
	<h2 class="mileage_entry_header">Wednesday</h2>
	<?= GridView::widget([
		'dataProvider' => $WednesdayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
		]
	])?>
	
	<!--Thursday TableView-->
	<h2 class="mileage_entry_header">Thursday</h2>
	<?= GridView::widget([
		'dataProvider' => $ThursdayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
		]
	])?>
	
	<!--Friday TableView-->
	<h2 class="mileage_entry_header">Friday</h2>
	<?= GridView::widget([
		'dataProvider' => $FridayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
		]
	])?>
	
	<!--Saturday TableView-->
	<h2 class="mileage_entry_header">Saturday</h2>
	<?= GridView::widget([
		'dataProvider' => $SaturdayProvider,
		'columns' => [
			'MileageCardType',
            'MileageCardApprovedFlag',
			'MileageCardApprovedBy',
            'MileageCardCreateDate',
            'MileageCardCreatedBy',
            'MileageCardModifiedDate',
            'MileageCardModifiedBy',
            'MileagCardBusinessMiles',
            'MileagCardPersonalMiles',
		]
	])?>

</div>
