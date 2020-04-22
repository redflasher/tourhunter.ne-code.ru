<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class TransferForm extends Model
{
    public $username;
    public $balance;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            ['username', 'required', 'message' => 'Enter username'],
            ['username', 'validateUsername'],
            ['username', 'validateRecepient'],
            ['balance', 'required', 'message' => 'Enter balance'],
            ['balance', 'number', 'min' => 0.01],
            ['balance', 'validateBalance'],
        ];
    }

    /**
    * @return Error message, if user try make money transfer to yourself
    */
    public function validateUsername($attribute, $params)
    {
        if(in_array($this->$attribute, [Yii::$app->user->identity->username]))
            $this->addError($attribute, 'Not transfer yourself');
    }

    /**
    * @return Error message, if user try make money transfer to not exists user
    */
    public function validateRecepient($attribute, $params)
    {
        if( !User::findOne(['username' => $this->$attribute]) )
            $this->addError($attribute, 'Recepient not exists.');
    }

    /**
    * @return Error message, if user balance will be less then -1000.00
    */
    public function validateBalance($attribute, $params)
    {
        $transferSum = (float)$this->$attribute;
        $userBalance = (float)Yii::$app->user->identity->balance;

        if( $userBalance - $transferSum < -1000.00 )
            $this->addError($attribute, 'Your balance can not be less then -1000.00');
    }
}
