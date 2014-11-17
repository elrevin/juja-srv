<?php
namespace app\base\db;

use Yii;
use yii\db;
class ActiveRecord extends db\ActiveRecord
{
    /**
     * Структура модели.
     * Массив в первом уровне в качестве ключей элементов используются имена полей в таблице
     * каждый такой элемент - вложенный ассоциативный массив:
     *      'title' - Название поля,
     *
     *      'type' - тип поля:
     *          'int' - целое число,
     *          'float' - число с точкой,
     *          'string' - строка, в mysql varchar(1024),
     *          'text' - многострочный текст, редактируется textarea, в mysql - longtext
     *          'html' - многострочный текст с форматированием, редактируется tinymce, в mysql - longtext
     *          'date' - дата
     *          'datetime' - дата и время
     *          'pointer' - ссылка на запись в другой модели, в mysql - int(11) и внешний ключ ссылающийся на другую модель, имя
     *              которой указано в relativeModel
     *          'select' - одно из предустановленных значений, значения указываются в selectOptions
     *          'file' - файл, в mysql - int(11) с внешним ключем на модель s_files
     *          'img' - изображение, в mysql - int(11) с внешним ключем на модель s_files
     *          'bool' - флаг, в mysql tinyint(1)
     *
     *      'settings' - Дополнительные настройки (ассоциативный массив):
     *          'maxLength' - максимальная длина (для строк)
     *          'min' - минимальное значение,
     *          'max' - максимальное значение,
     *          'round' - количество цифр после точки (при сохранении применяется округление),
     *          'width' - ширина столбца,
     *
     *      'group' - Название группы полей,
     *
     *      'relativeModel' - имя связанной модели (полное имя класса, включая namespace),
     *
     *      'selectOptions' - Ассоциативный массив возможных значений,
     *
     *      'showCondition' - Условия отображения поля - ассоциативный массив, в котором ключи - это имена других полей,
     *        а значения это ассоциативный массив условий:
     *            'operation' - операция:
     *                  'eq' - равно,
     *                  'noteq' - не равно
     *                  'gt' - больше
     *                  'lt' - меньше
     *                  'set' - установленно
     *                  'notset' - не установленно
     *             'value' - значение
     *
     *          пример условия:
     *          [
     *              'payd' => [ // поле payd
     *                  'operation' => 'set' // установленно
     *              ]
     *          ]
     *          При таком условии текущее поле будет отображаться если установлен флаг-поле payd
     *
     *          [
     *              'payd' => [
     *                  'operation' => 'set'
     *              ],
     *              'sum' => [
     *                  'operation' => 'gt',
     *                  'value' => '5000'
     *              ]
     *          ]
     *          При таких условиях данное поле будет отображаться если установлен флаг-поле payd и значение поля sum > 5000
     *
     *      'identify' - если true, то поле однозначно идентифицирует запись, например поле 'title' - название
     *
     *      'required' - поле обязательно для заполнения
     *
     *      'allowGroupEdit' - если равно false, то разрешено групповое редактирование
     *
     * @var array
     */
    protected static $structure = [];

    /**
     * Если true - записи удаляются перманентно, если false, метятся, как удаленные путем установки del=1
     * @var bool
     */
    protected static $permanentlyDelete = true;

    /**
     * если true, записи можно скывать, установкой поля hidden = 1
     * @var bool
     */
    protected static $hiddable = false;

    /**
     * Название справочника
     * @var string
     */
    protected static $modelTitle = '';

    /**
     * Если модуль рекурсивная (древовидная), то это свойство = true
     * @var bool
     */
    protected static $recursive = false;

    /**
     * Имя класса "master" модель
     * @var string
     */
    protected static $masterModel = '';

    /**
     * Имя класса родительской модели
     * @var string
     */
    protected static $parentModel = '';

    /**
     * Имя модуля, которой принадлежит модель
     * @var string
     */
    protected static $moduleName = '';

    /**
     * Имя класса модели, без namespace
     * @var string
     */
    protected static $modelName = '';

    /**
     * Название записи в едиственном чистел в именительном падеже, нпрмер "характеристика"
     * @var string
     */
    protected static $recordTitle = '';

    /**
     * Название записи в единственном числе в винительном падеже, например "характеристику"
     * @var string
     */
    protected static $accusativeRecordTitle = '';

    /**
     * Создавать интерфейс только для существующих предков, актуально только для детализаций.
     * Если это свойство равно true, то таб-панель для данной модели будет создана только если
     * запись, с которой модель связана будет существовать, иными словами после при редактировании
     * существующей записи, либо после ее создания.
     * @var bool
     */
    public static $createInterfaceForExistingParentOnly = true;

    public static function find()
    {
        $cond = static::defaultWhere();
        if ($cond) {
            return parent::find()->andWhere(static::defaultWhere());
        }
        return parent::find();
    }

    protected  static function defaultWhere()
    {
        if (!static::$permanentlyDelete) {
            return "`".static::tableName()."`.del = 0";
        }
        return [];
    }

    public static function getPermanentlyDelete () {
        return static::$permanentlyDelete;
    }

    public static function getHiddable () {
        return static::$hiddable;
    }

    public static function getModelTitle () {
        return static::$modelTitle;
    }

    public static function getRecursive () {
        return static::$recursive;
    }

    public static function getMasterModel () {
        return static::$masterModel;
    }

    public static function getParentModel ()
    {
        return static::$parentModel;
    }

    /**
     * Возвращает имя модуля, которому принадлежит модель
     * @return mixed
     */
    public static function getModuleName()
    {
        $className = static::className();
        $classNameSpace = trim(preg_replace("/([a-zA-Z0-9_]+)$/", '', $className), '\\');
        $classNameSpace = str_replace('app\modules\\', '', $classNameSpace);
        $moduleName = str_replace('\models', '', $classNameSpace);
        static::$moduleName = $moduleName;
        return $moduleName;
    }

    /**
     * Возвращает имя класса моудели, без пространства имен.
     * @return string
     */
    public static function getModelName()
    {
        $className = trim(static::className(), '\\');
        static::$modelName = str_replace('app\modules\\'.static::getModuleName().'\models\\', '', $className);
        return static::$modelName;
    }

    /**
     * Возвращает имя класса модели (с указанием пространства имен), которая является подчиненной данной модели, если таковая есть
     * если подчиненных моделей нет, то возвращается false
     * @return bool|mixed|string
     */
    public static function getChildModel()
    {
        $className = static::className();
        $classPath = "@app/modules/".static::getModuleName()."/models";
        $classNameSpace = '\app\modules\\'.static::getModuleName().'\\models';
        $files = scandir(Yii::getAlias($classPath));
        foreach ($files as $file) {
            if (preg_match("/^[a-zA-Z0-9]+\\.php$/", $file)) {
                $modelName = str_replace(".php", "", $file);
                if ($modelName != $className) {
                    $modelName = $classNameSpace.'\\'.str_replace(".php", "", $file);
                    $parentModel = call_user_func([$modelName, 'getParentModel']);
                    if ($parentModel == $className) {
                        return $modelName;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Возвращает модели-детализации, для которых вкачестве мастер-модели указана текущая (если таковые есть).
     * Возвращаются имена классов, включая пространства имен, в массиве.
     * Если модели-деталицации не найдены, возвращается false
     * @return array|bool
     */
    public static function getDetailModels() {
        $className = trim(static::className(), '\\');
        $classPath = "@app/modules/".static::getModuleName()."/models";
        $classNameSpace = '\app\modules\\'.static::getModuleName().'\\models';
        $files = scandir(Yii::getAlias($classPath));
        $result = [];
        foreach ($files as $file) {
            if (preg_match("/^[a-zA-Z0-9]+\\.php$/", $file)) {
                $modelName = str_replace(".php", "", $file);
                if ($modelName != $className) {
                    $modelName = $classNameSpace.'\\'.str_replace(".php", "", $file);
                    $masterModel = call_user_func([$modelName, 'getMasterModel']);
                    if (trim($masterModel, '\\') == $className) {
                        $result[] = $modelName;
                    }
                }
            }
        }

        return ($result ? $result : false);
    }

    /**
     * Возвращяет либо всю структуру таблицы (если fieldName == ''), либо данные об одном поле, имя которого указанно в
     * fieldName
     * @param string $fieldName
     * @return array
     */
    public static function getStructure($fieldName = '')
    {
        if ($fieldName) {
            return static::$structure[$fieldName];
        }
        return static::$structure;
    }

    /**
     * Возвращает конфигурацию поля отмеченного флагом identify, в конфигурацию добавляется ключ "name" содержащий имя поля
     *
     * @return array|null
     */
    public static function getIdentifyFieldConf ()
    {
        foreach (static::$structure as $fieldName => $fieldConf) {
            if ($fieldConf['identify']) {
                $fieldConf['name'] = $fieldName;
                return $fieldConf;
            }
        }
        return null;
    }

    /**
     * Возвращает массив записей модели для отображения в панели управления.
     * В аргументе $params передается ассоциативный массив параметров списка
     * записей:
     *      'start' - начальная запись (первый параметр LIMIT)
     *      'limit' - количество записей
     *      'filter' - фильтр записей (массив, который отправляет стандартный фильтр grid библиотеки ExtJS)
     *      'sort' - массив настроек сортировки, каждый элемент - ассоциативный массив, в котором ключ - имя поля, а
     *          значение - направление сортировки (константы  SORT_DESC или SORT_ASC, так же можно указать строки: 'ASC' и 'DESC')
     *          При обработке этого масиива учитывается тип поля, соответственно поля pointer и select обрабатываются
     *          особым образом
     *      'where' - условия, используется в качестве аргумента метода andWhere
     *      'identifyOnly' - true если требуется выгрузить только идентифицирующее поле (например для выпадающих списков)
     *      'parentId' - id родительской записи, если запрошены данные детализации
     *
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($params)
    {
        $select = ["`".static::tableName()."`.`id`"];
        $join = [];
        $pointers = [];

        foreach (static::$structure as $fieldName => $fieldConf) {
            if (isset($params['identifyOnly']) && $params['identifyOnly']) {
                if ((isset($fieldConf['identify']) && !$fieldConf['identify']) || (!isset($fieldConf['identify']))) {
                    continue;
                }
            }

            if ($fieldConf['type'] == 'pointer') {
                $relatedIdentifyFieldConf = call_user_func([$fieldConf['relativeModel'], 'getIdentifyFieldConf']);
                if ($relatedIdentifyFieldConf) {
                    $relatedTableName = call_user_func([$fieldConf['relativeModel'], 'tableName']);
                    $select[] = "`".static::tableName()."`.`".$fieldName."`";
                    $select[] = "`".$relatedTableName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                    $pointers[$fieldName] = [
                        "table" => $relatedTableName,
                        "field" => $relatedIdentifyFieldConf['name']
                    ];
                    $join[] = [
                        'name' => $relatedTableName,
                        'on' => "`".static::tableName()."`.`".$fieldName."` = `".$relatedTableName."`.id"
                    ];
                }
            } else {
                // Простые типы данных
                $select[] = "`".static::tableName()."`.`".$fieldName."`";
            }
        }

        $query = static::find()->select($select);

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                if ($filter['type'] == 'string') {
                    $query->andWhere(['like', "`".static::tableName()."`.`".$filter['field']."`", $filter['value']]);
                } elseif ($filter['type'] == 'numeric') {
                    if ($filter['comparison'] == 'lt') {
                        $query->andWhere(['<', "`".static::tableName()."`.`".$filter['field']."`", $filter['value']]);
                    } elseif ($filter['comparison'] == 'gt') {
                        $query->andWhere(['>', "`".static::tableName()."`.`".$filter['field']."`", $filter['value']]);
                    } elseif ($filter['comparison'] == 'eq') {
                        $query->andWhere(["`".static::tableName()."`.`".$filter['field']."`" => $filter['value']]);
                    }
                }
            }
        }

        if (isset($params['where'])) {
            $query->andWhere($params['where']);
        }

        if (isset($params['parentId']) && $params['parentId'] && static::$masterModel) {
            $query->andWhere('master_table_id = '.intval($params['parentId']));
        }

        if (isset($params['limit'])) {
            $query->limit($params['limit']);
        }

        if (isset($params['start'])) {
            $query->offset($params['start']);
        }

        if (isset($params['sort'])) {
            $orderBy = [];
            foreach ($params['sort'] as $key => $dir) {
                if (is_string($dir)) {
                    $dir = ($dir == 'DESC' ? SORT_DESC : SORT_ASC);
                }

                if (isset($pointers[$key])) {
                    $orderBy["`".$pointers[$key]['table']."`.`".$pointers[$key]['field']."`"] = $dir;
                } else {
                    $orderBy["`".static::tableName()."`.`".$key."`"] = $dir;
                }
            }
            $query->orderBy($orderBy);
        }

        foreach ($join as $item) {
            $query->leftJoin($item['name'], $item['on']);
        }

        $list = $query->asArray()->all();

        if ($pointers) {
            foreach ($list as $key => $item) {
                foreach ($pointers as $fieldName => $some) {
                    $list[$key][$fieldName] = \yii\helpers\Json::encode([
                        'id' => $item[$fieldName],
                        'value' => $item['valof_'.$fieldName]
                    ]);
                }
            }
        }
        return ['data' => $list];
    }

    /**
     * Приводит значение value к типу поля fieldName
     * @param $fieldName
     * @param $value
     * @return int|null|string
     */
    protected static function setType ($fieldName, $value)
    {
        $type = static::$structure[$fieldName]['type'];

        if ($type == 'int' || $type == 'file' || $type == 'img') {
            return intval($value);
        } elseif ($type == 'float') {
            return floatval($value);
        } elseif ($type == 'string' || $type == 'text' || $type == 'html') {
            return strval($value);
        } elseif ($type == 'date') {
            $value = strval($value);
            if (preg_match("/^\\d\\d\\d\\d-\\d\\d-\\d\\d$/", $value)) {
                return $value;
            } else {
                return null;
            }
        } elseif ($type == 'datetime') {
            $value = strval($value);
            if (preg_match("/^\\d\\d\\d\\d-\\d\\d-\\d\\d \\d\\d:\\d\\d:\\d\\d$/", $value)) {
                return $value;
            } else {
                return null;
            }
        } elseif ($type == 'select') {
            $value = strval($value);
            if (isset(static::$structure[$fieldName]['selectOptions']) && isset(static::$structure[$fieldName]['selectOptions'][$value])) {
                return $value;
            } else {
                return null;
            }
        } elseif ($type == 'bool') {
            return $value ? 1 : 0;
        } elseif ($type == 'pointer') {
            return $value['id'];
        }

        return null;
    }

    /**
     * "Накладывает" массив данных или JSON строку на модель
     * @param $data
     */
    public function mapJson($data)
    {
        if (is_string($data)) {
            $data = \yii\helpers\Json::decode($data);
        }
        if ($data) {
            foreach ($data as $key => $val) {
                if (isset(static::$structure[$key])) {
                    $this->$key = static::setType($key, $val);
                }
            }
        }
    }

    /**
     * Сохраняет данные переданные в массиве $data
     * @param array $data
     * @param bool $add
     * @param int $parentId
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function saveData($data, $add = false, $parentId = 0)
    {
        if (static::$masterModel) {
            $this->master_table_id = $parentId;
        }
        $this->mapJson($data);
        if ($this->save()) {
            if ($add) {
                return static::getList([
                    "limit" => 1,
                    "sort" => [
                        "`".static::tableName()."`.id" => SORT_DESC
                    ]
                ]);
            } else {
                return static::getList([
                    "where" => "`".static::tableName()."`.id"
                ]);
            }
        } else {
            return false;
        }
    }

    /**
     * Возвращает настройки пользовательского интерфейса.
     * Если $configOnly == true, то возвращается только массив настроек, если false, то возвращается полностью javascript редактора.
     *
     * @param bool $configOnly
     * @return string
     */
    public static function getUserInterface($configOnly = false)
    {
        $modelStructure = static::getStructure();
        $fields = [];
        foreach ($modelStructure as $fieldName => $config) {
            $relativeModel = [];
            if ($config['type'] == 'pointer') {
                // Для полей типа pointer получаем конфигурацию связанной модели
                if (strncmp($config['relativeModel'], '\app\modules', 12) == 0) {
                    $relativeModelFullName = $config['relativeModel'];
                    $relativeModel = str_replace('\app\modules\\', '', str_replace('\models', '', $config['relativeModel']));
                    $relativeModel = explode('\\', $relativeModel);
                    $relativeModel[2] = call_user_func([$relativeModelFullName, 'getIdentifyFieldConf']);
                    if (!$relativeModel[2]) {
                        continue;
                    }
                    $relativeModel[3] = $relativeModel[2]['type'];
                    $relativeModel[2] = $relativeModel[2]['name'];
                } else {
                    continue;
                }
            }

            $i = count($fields);
            $fields[$i] = array_merge([
                'name' => $fieldName
            ], $config);

            if ($relativeModel) {
                // есть связанная модель, добавляем ее конфигурацию в конфигурацию поля
                $fields[$i]['relativeModel'] = [
                    'moduleName' => $relativeModel[0],
                    'name' => $relativeModel[1],
                    'identifyFieldName' => $relativeModel[2],
                    'identifyFieldType' => $relativeModel[3]
                ];
            }
        }

        $getDataAction = [static::getModuleName(), 'main', 'list'];

        $userRights = 0;

        $modelName = static::getModelName();

        if (Yii::$app->user->can('backend-delete-record', ['modelName' => static::className()])) {
            $userRights = 3;
        } elseif (Yii::$app->user->can('backend-save-record', ['modelName' => static::className()])) {
            $userRights = 2;
        } elseif (Yii::$app->user->can('backend-list', ['modelName' => static::className()])) {
            $userRights = 1;
        }

        $conf = [
            'fields' => $fields,
            'getDataAction' => $getDataAction,
            'modelName' => $modelName,
            'userRights' => $userRights,
            'createInterfaceForExistingParentOnly' => static::$createInterfaceForExistingParentOnly,
            'title' => static::getModelTitle(),
            'recordTitle' => static::$recordTitle,
            'accusativeRecordTitle' => static::$accusativeRecordTitle
        ];

        $tabs = [];
        $detailModels = static::getDetailModels();
        if ($detailModels) {
            foreach ($detailModels as $item) {
                // Получаем конфиг модели-детализации
                $tabConfig = call_user_func([$item, 'getUserInterface'], true);
                $tabConfig['modelName'] = str_replace('app\modules\\'.static::getModuleName().'\models\\', '', trim($item, '\\'));
                $tabs[] = $tabConfig;
            }
        }

        if ($tabs) {
            $conf['tabs'] = $tabs;
        }

        if ($configOnly) {
            return $conf;
        }

        return ("
          var module = Ext.create('App.core.SingleModelEditor', ".\yii\helpers\Json::encode($conf).");
        ");
    }
}