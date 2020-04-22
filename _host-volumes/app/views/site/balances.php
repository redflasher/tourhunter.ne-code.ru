<?php

use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = "Balances";

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'User',
            'attribute' => 'username',
        ],
        [
            'label' => 'Balance',
            'attribute' => 'balance',
        ],
    ],
]);
