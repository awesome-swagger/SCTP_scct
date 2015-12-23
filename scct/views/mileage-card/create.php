<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\mileagecard */

$this->title = 'Create MileageCard';
$this->params['breadcrumbs'][] = ['label' => 'MileageCard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
