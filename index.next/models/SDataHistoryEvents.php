<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "s_data_history_events".
 *
 * @property integer $id
 * @property string $time
 * @property integer $user_id
 * @property string $ip
 * @property string $event
 * @property string $model
 * @property integer $record_id
 *
 * @property SDataHistory[] $sDataHistories
 * @property SUsers $user
 */
class SDataHistoryEvents extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_data_history_events';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time'], 'safe'],
            [['user_id', 'record_id'], 'integer'],
            [['ip', 'event', 'model'], 'string', 'max' => 1024]
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'time' => 'Time',
            'user_id' => 'User ID',
            'ip' => 'Ip',
            'event' => 'Event',
            'model' => 'Model',
            'record_id' => 'Record ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSDataHistories()
    {
        return $this->hasMany(SDataHistory::className(), ['event_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(SUsers::className(), ['id' => 'user_id']);
    }
}
