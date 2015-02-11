<?php

namespace app\modules\backend\models;

use Yii;

/**
 * Модель для таблицы "news_tags", справочник .
 *
 * @property integer $id
 * @property pointer $tag
 * @property integer $master_table_id
 */
class NewsTags extends \app\modules\backend\models\base\NewsTags
{
}