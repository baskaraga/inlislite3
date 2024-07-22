<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\JumlahPenduduk;

/**
 * JumlahPendudukSearch represents the model behind the search form of `frontend\models\JumlahPenduduk`.
 */
class JumlahPendudukSearch extends JumlahPenduduk
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'tahun', 'jumlah'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
{
    $query = JumlahPenduduk::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'attributes' => [
                'id',
                'tahun',
                'jumlah',
            ],
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        // uncomment the following line if you do not want to return any records when validation fails
        // $query->where('0=1');
        return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'tahun' => $this->tahun,
        'jumlah' => $this->jumlah,
    ]);

    return $dataProvider;
}
}
