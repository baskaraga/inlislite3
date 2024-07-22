<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "jumlah_penduduk".
 *
 * @property int $id
 * @property int $tahun
 * @property int $jumlah
 */
class JumlahPenduduk extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jumlah_penduduk';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun', 'jumlah'], 'required'],
            [['tahun', 'jumlah'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tahun' => 'Tahun',
            'jumlah' => 'Jumlah',
        ];
    }
}
