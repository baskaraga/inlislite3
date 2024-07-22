<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\web\JsExpression;
// use yii\widgets\DetailView;

use kartik\date\DatePicker;
use kartik\widgets\Select2;
use kartik\detail\DetailView;
use dosamigos\highcharts\HighCharts;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\SurveyIsianSearch $searchModel
 */


$now = date("d-m-Y");
$forChart = array();

$this->title = Yii::t('app', 'Perhitungan IPLM & TGM');
Yii::$app->view->params['subTitle'] = '<h3 style="padding-top: 15px;">'.Yii::t('app', 'Selamat Datang').'<br>'.Yii::t('app', 'Perhitungan IPLM & TGM').'<h3>';

