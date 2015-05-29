<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "s_users_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $time
 * @property string $event
 * @property string $ip
 *
 * @property SUsers $user
 */
class SUsersLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_users_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required', 'message' => 'Не заполнен user_id'],
            [['user_id'], 'integer'],
            [['time'], 'safe', 'message' => 'time'],
            [['event'], 'string', 'max' => 1024],
            [['ip'], 'required', 'message' => 'Не заполнен ip'],
        ];
    }

    public function beforeValidate()
    {
       if ($this->isNewRecord) {
           $this->ip = Yii::$app->request->userIP;
       }
        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(SUsers::className(), ['id' => 'user_id']);
    }
}
