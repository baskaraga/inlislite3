<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var common\models\Collectioncategorys $model
 */

$this->title = Yii::t('app', 'Catalog Detail Configuration');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Setting'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Katalog'), 'url' => Url::to(['/setting/katalog'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="settingparameters-create">

    <?= $this->render('_form', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        'model' => $model,
    ]) ?>

</div>
