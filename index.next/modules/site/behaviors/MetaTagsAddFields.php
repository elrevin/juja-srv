<?php
namespace app\modules\site\behaviors;

class MetaTagsAddFields extends \app\base\db\AdditionsFieldsBehavior
{
    protected static $additionModel = '\app\modules\site\models\SiteMetaTags';

    protected static $fields = [
        'url' => [
            'title' => 'URL',
            'type' => 'string',
            'group' => 'Мета данные',
        ],
        'meta_title' => [
            'title' => 'Заголовок (title)',
            'type' => 'string',
            'group' => 'Мета данные',
        ],
        'meta_description' => [
            'title' => 'Описание страницы (description)',
            'type' => 'text',
            'group' => 'Мета данные',
        ],
        'meta_keywords' => [
            'title' => 'Ключевые слова (keywords)',
            'type' => 'text',
            'group' => 'Мета данные',
        ],
    ];
}