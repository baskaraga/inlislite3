<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\JumlahPendudukSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Jumlah Penduduk';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jumlah-penduduk-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Create Jumlah Penduduk', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            // Make sure to use the attribute names here
            [
                'attribute' => 'id',
                'label' => 'ID',
                'enableSorting' => true,
            ],
            [
                'attribute' => 'tahun',
                'label' => 'Tahun',
                'enableSorting' => true,
            ],
            [
                'attribute' => 'jumlah',
                'label' => 'Jumlah',
                'enableSorting' => true,
            ],
            
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
