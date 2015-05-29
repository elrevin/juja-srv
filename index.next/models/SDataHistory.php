<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "s_data_history".
 *
 * @property integer $id
 * @property integer $event_id
 * @property string $field
 * @property string $value
 *
 * @property SDataHistoryEvents $event
 */
class SDataHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_data_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id'], 'integer'],
            [['value'], 'string'],
            [['field'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'field' => 'Field',
            'value' => 'Value',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(SDataHistoryEvents::className(), ['id' => 'event_id']);
    }
}
