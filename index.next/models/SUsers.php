<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "s_users".
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property integer $group_id
 * @property string $name
 * @property string $hash
 * @property string $email
 * @property int $block
 * @property string $last_login
 * @property string $restore_code
 * @property string $restore_code_expires
 * @property integer $su
 *
 * @property SUsersGroups $group
 */
class SUsers extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'required'],
            [['group_id'], 'integer'],
            [['last_login', 'restore_code_expires'], 'safe'],
            [['username', 'password', 'name', 'hash', 'email'], 'string', 'max' => 255],
            [['restore_code'], 'string', 'max' => 64]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(SUsersGroups::className(), ['id' => 'group_id']);
    }
}
