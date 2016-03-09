<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\controllers\TimeCard;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Time Cards';
$this->params['breadcrumbs'][] = $this->title;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script type="text/javascript">
    $('#multiple_approve_btn').click(function() {
		var keys = $('#w1').yiiGridView('getSelectedRows'); // returns an array of pkeys, and #grid is your grid element id
		alert('Total price is ');
		/*$.post({
		   url: '/time-card/approvem', // your controller action
		   dataType: 'json',
		   data: {keylist: keys},
		   success: function(data) {
			  if (data.status === 'success') {
				  alert('Total price is ');
			  }
		   },
		});*/
	});
</script>

<div class="timecard-index">

    <h3><?= Html::encode($this->title) ?></h3>

	<?php 
			//$approveUrl = urldecode(Url::to(['time-card/approve', 'id' => $model["TimeCardID"]]));
			$approveUrl = "";
	?>
    <p id="multiple_time_card_approve_btn">
       <?= Html::button('Approve', [
											 'class' => 'btn btn-primary multiple_approve_btn',
											 'id' => 'multiple_approve_btn',
											 /*'data' => [
														'confirm' => 'Are you sure you want to approve this item?']*/
													])?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

			'UserFirstName',
			'UserLastName',
			'TimeCardStartDate',
			'TimeCardEndDate',
			'TimeCardHoursWorked',
			'TimeCardApproved',

            ['class' => 'yii\grid\ActionColumn',
				'template' => '{view}',
				'urlCreator' => function ($action, $model, $key, $index) {
								if ($action === 'view') {
								$url ='index.php?r=time-card%2Fview&id='.$model["TimeCardID"];
								return $url;
								}
							},
							'buttons' => [
								'delete' => function ($url, $model, $key) {
									$url ='/index.php?r=time-card%2Fdelete&id='.$model["TimeCardID"];
										$options = [
										'title' => Yii::t('yii', 'Delete'),
										'aria-label' => Yii::t('yii', 'Delete'),
										'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
										'data-method' => 'Delete',
										'data-pjax' => '0',
										];
										return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
								},
							],
			],
			[
				'class' => 'yii\grid\CheckboxColumn',
			],
        ],
    ]); ?>

</div>
