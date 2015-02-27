<?php
namespace app\modules\site\models;

use yii\helpers\Json;

/**
 * @inheritdoc
 */
class SiteStructure extends \app\modules\site\models\base\SiteStructure
{
    public $urls = null;
    protected static function beforeList($params)
    {
        static::$structure['module']['type'] = 'string';
        static::$structure['template']['type'] = 'string';
        return $params;
    }

    protected static function afterList($list)
    {
        static::$structure['module']['type'] = 'pointer';
        static::$structure['template']['type'] = 'pointer';

        $modules = Modules::getModulesList(false);

        foreach ($list as $key => $item) {
            $moduleName = '';
            if (array_key_exists('module', $list[$key]) && $list[$key]['module']) {
                $moduleName = $modules[$list[$key]['module']]['title'];
                $list[$key]['module'] = Json::encode([
                    'id' => $modules[$list[$key]['module']]['id'],
                    'value' => $modules[$list[$key]['module']]['title']
                ]);
            }
            $templates = Templates::getTemplatesList($moduleName, false);
            if ($templates && isset($templates[$list[$key]['template']])) {
                $list[$key]['template'] = Json::encode([
                    'id' => $templates[$list[$key]['template']]['id'],
                    'value' => $templates[$list[$key]['template']]['title']
                ]);
            } else {
                $list[$key]['template'] = null;
            }
        }
        return $list;
    }

    public static function generateSelfUrls($parentId = null, $url = "")
    {
        $ret = [];
        if (!$parentId) {
            $data = static::find()->where(['parent_id' => $parentId, 'module' => 'site'])->one();
            $ret[] = [
                "id" => $data->id,
                "url" => "/"
            ];
            $ret = array_merge($ret, static::generateSelfUrls($data->id, ''));
        } else {
            $data = static::find()->where(['parent_id' => $parentId, 'module' => 'site'])->all();
            foreach ($data as $item) {
                $ret[] = [
                    "id" => $item->id,
                    "url" => $url."/".$item->url
                ];
                $ret = array_merge($ret, static::generateSelfUrls($item->id, $url."/".$item->url));
            }
        }
        foreach ($ret as $key => $item) {
            $ret[$key]['url'] = trim($item['url'], '/');
        }

        return $ret;
    }

    public function saveData($data, $add = false, $masterId = 0)
    {
        $this->urls = null;
        static::$structure['module']['type'] = 'string';
        static::$structure['template']['type'] = 'string';
        $modules = Modules::getModulesList();
        if (is_string($data)) {
            $data = Json::decode($data);
        }
        if ($data['module']) {
            $data['module'] = $modules[$data['module']['id'] - 1]['name'];
            $templates = Templates::getTemplatesList($data['module']);
            if ($data['template']) {
                $data['template'] = $templates[$data['template']['id'] - 1]['name'];
            }
        }

        $ret = parent::saveData($data, $add, $masterId);
        static::$structure['module']['type'] = 'pointer';
        static::$structure['template']['type'] = 'pointer';

        return $ret;
    }

}