<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "s_users_groups".
 *
 * @property integer $id
 * @property string $title
 * @property integer $cp_access
 *
 * @property SUsers[] $sUsers
 */
class SUsersGroups extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_users_groups';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_access'], 'integer'],
            [['title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'cp_access' => 'Cp Access',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSUsers()
    {
        return $this->hasMany(SUsers::className(), ['group_id' => 'id']);
    }
}
