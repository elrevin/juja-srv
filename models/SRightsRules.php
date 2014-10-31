<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "s_rights_rules".
 *
 * @property integer $id
 * @property string $model_name
 * @property integer $user_group_id
 * @property integer $user_id
 * @property integer $rights
 *
 * @property SUsers $user
 * @property SUsersGroups $userGroup
 */
class SRightsRules extends \app\base\db\ActiveRecord
{
    const RIGHTS_NONE = 0;
    const RIGHTS_READ = 1;
    const RIGHTS_WRITE = 2;
    const RIGHTS_ALL = 3
    ;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_rights_rules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_group_id', 'user_id', 'rights'], 'integer'],
            [['model_name'], 'required'],
            [['model_name'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_name' => 'Model Name',
            'user_group_id' => 'Id User Group',
            'user_id' => 'Id User',
            'rights' => 'Rights',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(SUsers::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserGroup()
    {
        return $this->hasOne(SUsersGroups::className(), ['id' => 'user_group_id']);
    }

    /**
     * Возвращает права текущего пользователя на модель
     * @param $modelName
     * @param int $idRecord
     * @return int|mixed
     */
    public static function findRights($modelName, $idRecord = 0)
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->getIdentity()->isSU) {
                // Админам можно все
                return self::RIGHTS_ALL;
            }

            if ($idRecord == 0) {
                $rights = self::find()->where(
                    ")user_group_id = ".Yii::$app->user->getIdentity()->getUserData()->group_id." OR ".
                    "user_id = ".Yii::$app->user->id.") AND model_name = '{$modelName}'"
                )->orderBy(['user_id' => SORT_DESC])->limit(1)->one();
                return $rights->rights;
            }
        }
        return self::RIGHTS_NONE;
    }
}
