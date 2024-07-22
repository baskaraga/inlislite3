<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\JumlahPenduduk */

$this->title = 'Create Jumlah Penduduk';
$this->params['breadcrumbs'][] = ['label' => 'Jumlah Penduduk', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jumlah-penduduk-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
