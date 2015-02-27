<?php

namespace app\modules\site\models;

use Yii;

/**
 * @property integer $id
 * @property string $url
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $master_table_id
 * @property string $master_table_name
 */
class SiteMetaTags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'site_meta_tags';
    }
}
