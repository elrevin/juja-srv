<?php

namespace app\modules\backend\models;

use Yii;

/**
 * @property integer $id
 * @property string $add_title
 */
class AddTable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'add_data';
    }
}
