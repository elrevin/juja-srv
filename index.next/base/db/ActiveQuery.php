<?php
namespace app\base\db;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class ActiveQuery extends \yii\db\ActiveQuery
{
    public $additionTables = [];
    public $pointers = [];
    public $colorFields = [];
    public $selectFields = [];
    public $calcFields = [];
    private $tableAliases = [];

    protected function tableName($modelClass = '')
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = ($modelClass ? $modelClass : $this->modelClass);
        return $modelClass::tableName();
    }
    protected function getSimpleFilterCondition($type, $field, $comparison, $value, $expression = '')
    {
        /**
         * @var $modelClass ActiveRecord
         */
        $modelClass = $this->modelClass;
        $tableName = $this->tableName();
        $tableName = str_replace('.', '_', $tableName);
        $res = [];

        if ($field == $modelClass::getMasterModelRelFieldName()) {
            $type = 'numeric';
        }

        $fieldConf = $modelClass::getStructure($field);
        if (!$type) {
            switch($fieldConf['type']) {
                case 'int':
                case 'float':
                case 'file':
                    $type = 'numeric';
                    break;
                case 'text':
                case 'html':
                case 'string':
                case 'tinystring':
                    $type = 'string';
                    break;
                case 'select':
                    $type = 'list';
                    break;
                case 'pinter':
                    $type = 'pointer';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                case 'datetype':
                    $type = 'datetime';
                    break;
            }
        }

        $condField = !$expression ? "`" . $tableName . "`.`" . $field . "`" : $expression;
        if ($type == 'string' || $type == 'tinystring') {
            if ($comparison == 'end') {
                $res = ['like', $condField, "%".$value, false];
            } elseif ($comparison == 'start') {
                $res = ['like', ($condField), $value."%", false];
            } elseif ($comparison == 'eq' || $comparison == '==') {
                $res = ['=', ($condField), $value];
            } else {
                $res = ['like', ($condField), $value];
            }
        } elseif ($type == 'numeric') {
            if ($comparison == 'lt' || $comparison == '<') {
                $res = ['<', ($condField), $value];
            } elseif ($comparison == 'gt' || $comparison == '>') {
                $res = ['>', ($condField), $value];
            } elseif ($comparison == 'eq' || $comparison == '==') {
                $res = ['=', ($condField), $value];
            } elseif ($comparison == 'noteq' || $comparison == '!=') {
                $res = ['<>', ($condField), $value];
            }
        } elseif ($type == 'date' || $type == 'datetime') {
            if ($comparison == 'lt' || $comparison == '<') {
                $res = ['<', ($condField), $value];
            } elseif ($comparison == 'gt' || $comparison == '>') {
                $res = ['>', ($condField), $value];
            } elseif ($comparison == 'eq' || $comparison == '==') {
                $res = ['=', ($condField), $value];
            } elseif ($comparison == 'noteq' || $comparison == '!=') {
                $res = ['<>', ($condField), $value];
            }
        } elseif ($type == 'list') {
            $res = ['=', ($condField), $value];
        } elseif ($type == 'pointer') {
            if (is_array($fieldConf['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$fieldConf['relativeModel']['moduleName'].'\models\\'.$fieldConf['relativeModel']['name'];
            } else {
                $relatedModelClass = $fieldConf['relativeModel'];
            }
            $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
            if ($relatedIdentifyFieldConf) {
                $relatedTableName = $this->tableName($relatedModelClass);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $relatedTableName = $relatedTableClearName."_".$field;
                $relatedFieldName = $relatedIdentifyFieldConf['name'];
                $res = ['like', ($relatedTableName.".".$relatedFieldName), $value];
            }
        }
        return $res;
    }

    protected function getFilterCondition($filter, $expression = '')
    {
        $condition = [];
        if (!isset($filter['value'])) {
            return ['=', 'id',-8];
        }
        if (!is_array($filter['value'])) {
            $filter['value'] = [$filter['value']];
        }

        foreach ($filter['value'] as $value) {
            $condition[] = $this->getSimpleFilterCondition(
                (isset($filter['type']) ? $filter['type'] : null),
                $filter['field'],
                (isset($filter['comparison']) ? $filter['comparison'] : ''),
                $value,
                $expression
            );
        }
        $count = count($condition);
        if ($count > 1) {
            array_unshift($condition, 'or');
        } elseif ($count == 1) {
            $condition = $condition[0];
        }
        return $condition;
    }

    protected function getTableAlias($tableAlias, $tableName, $postfix)
    {
        $tableName = str_replace('.', '_', $tableName);
        $tableAlias = ( $tableAlias ? $tableAlias : $tableName.($postfix ? "__{$postfix}" : '' ) );

        if (!$tableAlias && $postfix && in_array($tableAlias, $this->tableAliases)) {
            $n = 1;

            while (in_array($tableAlias."_".$n, $this->tableAliases)) {
                $n++;
            }

            $tableAlias .= "_".$n;
        }

        if (!in_array($tableAlias, $this->tableAliases)) {
            $this->tableAliases[] = $tableAlias;
        }
        return $tableAlias;
    }

    /**
     * @param string $fieldName
     * @param string $modelClass
     * @param string $fieldAlias если в конце указать *, то вместо нее будет подставленно имя поля
     * @param int $postfix
     * @param string $tableAlias
     * @param bool $joinOnly
     * @throws Exception
     */
    private function getField($fieldName, $modelClass = '', $fieldAlias = '', $postfix = 0, $tableAlias = '', $joinOnly = false)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = ($modelClass ? $modelClass : $this->modelClass);

        $fieldName = ($fieldName ? $fieldName : $modelClass::getIdentifyFieldConf()['name']);

        $tableName = $this->tableName($modelClass);

        $tableAlias = $this->getTableAlias($tableAlias, $tableName, $postfix);

        $fieldAlias = ($fieldAlias ? $fieldAlias : $fieldName);

        $structure = $modelClass::getStructure();

        $field = false;

        /**
         * @var $linkModelName ActiveRecord
         */
        $linkModelName = $modelClass::getLinkModelName();
        if ($structure) {
            $field = $structure[$fieldName];
        } elseif ($fieldName == $modelClass::getLinkTableIdField()) {
            $field = [
                'title' => '',
                'type' => 'linked',
            ];
        } elseif ($linkModelName) {
            $modelClass = $linkModelName;
            $structure = $modelClass::getStructure();
            $field = $structure[$fieldName];
            $tableAlias = '__link_model_table';
        }

        if (!$field) {
            throw new Exception("Field {$fieldName} not found in {$this->modelClass}");
        }

        if ($field['type'] == 'fromlinked') {
            $modelClass = $linkModelName;
            $structure = $modelClass::getStructure();
            $field = $structure[$fieldName];
        }

        if (isset($field['calc']) && $field['calc']) {
            // Поле вычисляемое
            $replPatterns = [
                "/\\{\\{\\s*tablename\\s*\\}\\}/i",
            ];
            $replStrings = [
                $tableAlias,
            ];
            if ($postfix) {
                // Это не первая итерация, и для работы вычисляемого поля могут понадобиться другие поля типа pointer,
                // но обработки таких полей не должно быть, только подключаем таблицы

                foreach ($structure as $name => $fld) {
                    if ($fld['type'] == 'pointer') {
                        if (is_array($fld['relativeModel'])) {
                            $relatedModelClass = '\app\modules\\'.$fld['relativeModel']['moduleName'].'\models\\'.$fld['relativeModel']['name'];
                        } else {
                            $relatedModelClass = $fld['relativeModel'];
                        }
                        $relatedTableName = $this->tableName($relatedModelClass);
                        $relatedTableAlias = str_replace('.', '_', $relatedTableName)."__".($postfix+1);

                        $this->getField($name, $modelClass, '', $postfix, $tableAlias, true);
                        $replPatterns[] = "/\\{\\{\\s*{$name}_tablename\\s*\\}\\}/i";
                        $replStrings[] = $relatedTableAlias;
                    }
                }
            }

            $expr = preg_replace("/\\{\\{\\s*tablename\\s*\\}\\}/i", $tableAlias, $field['expression']);
            $this->select[] = "({$expr}) as `{$fieldAlias}`";
            $this->calcFields[$fieldAlias] = "( {$expr} )";

            if ($field['type'] == 'file') {
                // Да, такое иногда нужно %-)
                $relatedModelClass = '\app\modules\files\models\Files';
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                $relatedTableName = $this->tableName($relatedModelClass);
                $relatedTableAlias = $this->getTableAlias("", $relatedTableName, $postfix+1);

                if (!in_array($relatedTableName." as ".$relatedTableAlias, ArrayHelper::getColumn($this->join, 'name'))) {
                    $this->join[] = [
                        'name' => $relatedTableName." as ".$relatedTableAlias,
                        'on' => "{$tableAlias}.`{$fieldName}` = `{$relatedTableAlias}`.id"
                    ];
                }

                $this->select[] = "{$relatedTableAlias}.`{$relatedIdentifyFieldConf['name']}` as `valof_{$fieldAlias}`";
                $this->select[] = "{$relatedTableAlias}.`name` as `fileof_{$fieldAlias}`";
                $this->pointers[$fieldName] = [
                    "table" => $relatedTableAlias,
                    "field" => $relatedIdentifyFieldConf['name'],
                    "file_field" => 'name'
                ];
            }
        } elseif ($field['type'] != 'pointer' && $field['type'] != 'file' && $field['type'] != 'select' && $field['type'] != 'linked' && $field['type'] != 'color') {
            if (array_key_exists('addition', $field) && $field['addition']) {
                if (!in_array($field['additionTable'], $this->additionTables)) {
                    $this->additionTables[] = $field['additionTable'];
                    $this->join[] = [
                        'name' => $field['additionTable'],
                        'on' => "`".$field['additionTable']."`.`master_table_id` = ".$tableAlias.".id AND `".$field['additionTable']."`.`master_table_name` = '".$tableName."'"
                    ];
                }
                $this->select[] = "`{$field['additionTable']}`.`{$fieldName}` AS `{$fieldAlias}`";
            } else {
                $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
            }
        } elseif ($field['type'] == 'color') {
            if ($field['colorFormat'] == 'dec') {
                $this->colorFields[] = $fieldName;
            }
            $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
        } elseif ($field['type'] == 'pointer') {
            if (is_array($field['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$field['relativeModel']['moduleName'].'\models\\'.$field['relativeModel']['name'];
            } else {
                $relatedModelClass = $field['relativeModel'];
            }

            $relatedTableName = $this->tableName($relatedModelClass);
            $relatedTableAlias = $this->getTableAlias("", $relatedTableName, $postfix+1);
            $relatedTableName = explode('.', $relatedTableName);
            foreach ($relatedTableName as $i => $v) {
                $relatedTableName[$i] = "`{$v}`";
            }
            $relatedTableName = implode(".", $relatedTableName);
            if (!$this->join || !in_array($relatedTableName." as ".$relatedTableAlias, ArrayHelper::getColumn($this->join, 'name'))) {
                $this->join[] = [
                    'name' => $relatedTableName." as ".$relatedTableAlias,
                    'on' => "{$tableAlias}.`{$fieldName}` = `{$relatedTableAlias}`.id"
                ];
            }

            if (!$joinOnly) {
                $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
                $this->getField('', $relatedModelClass, "valof_{$fieldAlias}", $postfix + 1, $relatedTableAlias);
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                $this->pointers[$fieldName] = [
                    "table" => $relatedTableAlias,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
            }
        } elseif ($field['type'] == 'linked') {
            $relatedIdentifyFieldConf = call_user_func([$linkModelName, 'getIdentifyFieldConf']);
            if (trim($linkModelName, '\\') == 'app\modules\files\models\Files') {
                $relatedModelClass = '\app\modules\files\models\Files';
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                $relatedTableName = $this->tableName($relatedModelClass);
                $relatedTableAlias = $this->getTableAlias("", $relatedTableName, $postfix+1);

                $this->join[] = [
                    'name' => $relatedTableName." as ".$relatedTableAlias,
                    'on' => "{$tableAlias}.`{$fieldName}` = `{$relatedTableAlias}`.id"
                ];

                $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
                $this->select[] = "{$relatedTableAlias}.`{$relatedIdentifyFieldConf['name']}` as `valof_{$fieldAlias}`";
                $this->select[] = "{$relatedTableAlias}.`name` as `fileof_{$fieldAlias}`";
                $this->pointers[$fieldName] = [
                    "table" => $relatedTableAlias,
                    "field" => $relatedIdentifyFieldConf['name'],
                    "file_field" => 'name'
                ];
            } else {
                $this->select[] = "`__link_model_table`.`id` AS `{$fieldAlias}`";
                $this->getField('', $linkModelName, "valof_{$fieldAlias}", $postfix + 1, '__link_model_table');
                $this->pointers[$fieldName] = [
                    "table" => $linkModelName,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
            }
        } elseif ($field['type'] == 'file') {
            $relatedModelClass = '\app\modules\files\models\Files';
            $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
            $relatedTableName = $this->tableName($relatedModelClass);
            $relatedTableAlias = $this->getTableAlias("", $relatedTableName, $postfix+1);

            $this->join[] = [
                'name' => $relatedTableName." as ".$relatedTableAlias,
                'on' => "{$tableAlias}.`{$fieldName}` = `{$relatedTableAlias}`.id"
            ];

            $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
            $this->select[] = "{$relatedTableAlias}.`{$relatedIdentifyFieldConf['name']}` as `valof_{$fieldAlias}`";
            $this->select[] = "{$relatedTableAlias}.`name` as `fileof_{$fieldAlias}`";
            $this->pointers[$fieldName] = [
                "table" => $relatedTableAlias,
                "field" => $relatedIdentifyFieldConf['name'],
                "file_field" => 'name'
            ];
        } elseif ($field['type'] == 'select') {
            $this->select[] = "{$tableAlias}.`{$fieldName}` AS `{$fieldAlias}`";
            $options = [];
            $keyIndex = 1;
            foreach ($field['selectOptions'] as $key => $value) {
                $options[] = "WHEN :option{$keyIndex}_{$fieldAlias}_key THEN :option{$keyIndex}_{$fieldAlias}_value";
                $this->params[":option{$keyIndex}_{$fieldAlias}_key"] = $key;
                $this->params[":option{$keyIndex}_{$fieldAlias}_value"] = $value;
                $keyIndex++;
            }
            $this->select[] = "(CASE ".$tableAlias.".`".$fieldName."` ".implode(' ', $options)." END) AS `valof_".$fieldName."`";
            $this->selectFields[$fieldName] = [
                "valField" => "valof_".$fieldName
            ];
        }
    }

    /**
     * Возвращает массив записей модели для отображения в панели управления.
     * В аргументе $params передается ассоциативный массив параметров списка
     * записей:
     *      'start' - начальная запись (первый параметр LIMIT)
     *      'limit' - количество записей
     *      'filter' - фильтр записей (массив, который отправляет стандартный фильтр grid библиотеки ExtJS)
     *      'sort' - массив настроек сортировки, каждый элемент - ассоциативный массив:
     *              'property' - имя поля,
     *              'direction' - Направление (ASC или DESC) по умолчанию ASC
     *          При обработке этого масиива учитывается тип поля, соответственно поля pointer и select обрабатываются
     *          особым образом
     *      'where' - условия, используется в качестве аргумента метода andWhere
     *      'identifyOnly' - true если требуется выгрузить только идентифицирующее поле (например для выпадающих списков)
     *      'masterId' - id родительской записи, если запрошены данные детализации
     *      'dataKey' - Ключ в возвращаемом массиве, который будет содержать данные
     *
     * @param array $params Параметры запроса
     * @return ActiveRecord[]
     */
    public function getList($params)
    {
        /**
         * @var $modelClass ActiveRecord
         */
        $modelClass = $this->modelClass;
        $tableName = $this->tableName();
        if (strpos($tableName, '.') !== false) {
            $tableName = explode('.', $tableName);
            $tableName = "`{$tableName[0]}`.`{$tableName[1]}`";
        } else {
            $tableName = "`{$tableName}`";
        }
        $tableAlias = str_replace(".", "_", $tableName);
        $modelClass::checkStructure();
        $modelClass::addAdditionFields();
        $structure = $modelClass::getStructure();
        $identifyFieldName = '';

        $this->join = [];

        if (isset($params['query']) && $params['query']) {
            // Быстрый фильтр по identyfy полю
            $identifyFieldConf = $modelClass::getIdentifyFieldConf();
            $params['filter'] = ($params['filter'] ? $params['filter'] : []);
            $params['filter'] = array_merge($params['filter'], [
                [
                    'type' => 'string',
                    'value' => $params['query'],
                    'field' => $identifyFieldConf['name'],
                ],
            ]);
        }

        $this->select[] = "{$tableAlias}.id";

        $params = $modelClass::beforeList($params);
        $_tmpModelClass = $modelClass;
        if (!$structure && $modelClass::getLinkModelName()) {
            /**
             * @var $_tmpModelClass ActiveRecord
             */
            $_tmpModelClass = $modelClass::getLinkModelName();
            $structure = $_tmpModelClass::getStructure();

            $structure[$modelClass::getLinkTableIdField()] = [
                'title' => '',
                'type' => 'linked',
            ];
        }

        foreach ($structure as $fieldName => $fieldConf) {
            if (isset($params['identifyOnly']) && $params['identifyOnly']) {
                if (isset($fieldConf['identify']) && $fieldConf['identify']) {
                    $identifyFieldName = $fieldName;
                }
            }
            if (!isset($fieldConf['calc'])) {
                $fieldConf['calc'] = false;
            }

            if (!isset($fieldConf['addition'])) {
                $fieldConf['addition'] = false;
            }

            $this->getField($fieldName, $modelClass, '', 0, $tableAlias);
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && $modelClass::getRecursive()) {
            $fieldName = 'parent_id';
            $relatedModelClass = $modelClass::className();
            $relatedIdentifyFieldConf = $modelClass::getIdentifyFieldConf();
            if ($relatedIdentifyFieldConf) {
                $relatedTableName = $this->tableName($relatedModelClass);
                $relatedTableAlias = str_replace('.', '_', $relatedTableName);
                $this->select[] = $tableAlias.".`".$fieldName."`";
                $this->select[] = $relatedTableAlias."_".$fieldName.".`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $this->pointers[$fieldName] = [
                    "table" => $relatedTableAlias."_".$fieldName,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
                $this->join[] = [
                    'name' => $relatedTableName." as ".$relatedTableAlias."_".$fieldName,
                    'on' => $tableAlias.".`".$fieldName."` = ".$relatedTableAlias."_".$fieldName.".id"
                ];
            }
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && $modelClass::getHiddable()) {
            $fieldName = 'hidden';
            $this->select[] = $tableAlias.".`".$fieldName."`";
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && $modelClass::getParentModel()) {
            $parentModelName = $modelClass::getParentModel();

            $fieldName = $modelClass::getMasterModelRelFieldName();
            $relatedIdentifyFieldConf = call_user_func([$parentModelName, 'getIdentifyFieldConf']);
            if ($relatedIdentifyFieldConf) {
                $relatedTableName = $this->tableName($parentModelName);
                $relatedTableAlias = str_replace('.', '_', $relatedTableName)."_".$fieldName;
                $this->select[] = $tableAlias.".`".$fieldName."`";

                $this->getField('', $parentModelName, "valof_{$fieldName}", 0, $relatedTableAlias);

                //$this->select[] = "`".$relatedTableAlias."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $this->pointers[$fieldName] = [
                    "table" => $relatedTableAlias,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
                $this->join[] = [
                    'name' => $relatedTableName." as ".$relatedTableAlias,
                    'on' => $tableAlias.".`".$fieldName."` = `".$relatedTableAlias."`.id"
                ];
            }
        }

        $filteredFields = [];

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                $filteredFields[] = $filter['field'];
                if (isset($this->calcFields[$filter['field']])) {
                    $this->andWhere($this->getFilterCondition($filter, $this->calcFields[$filter['field']]));
                } else {
                    $this->andWhere($this->getFilterCondition($filter));
                }
            }
        }

        if(
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_CHECK
        ) {

            //Вызываем рак мозга у запроса - нам надо в секцию FROM запроса затолкать таблицу, которую мы подключаем, а основную сджоинить
            $relatedTableName = $this->tableName($modelClass::getLinkModelName());

            $this->from($relatedTableName." as __link_model_table");
            $this->select[] = "IF((".$tableAlias.".`id` IS NOT NULL AND ".
                $tableAlias.".`".$modelClass::getMasterModelRelFieldName()."` = ".$params['masterId']."), 1, 0) AS `check`";

            array_unshift($this->join, [
                "name" => $tableName." ".$tableAlias,
                "on"   => "`__link_model_table`.`id` = ".$tableAlias.".`".$modelClass::getLinkTableIdField()."` AND ".$tableAlias.".`".$modelClass::getMasterModelRelFieldName()."` = ".$params['masterId'],
            ]);
            if (!call_user_func([$modelClass::getLinkModelName(), 'getPermanentlyDelete'])) {
                $this->andWhere("`__link_model_table`.del = 0");
            }
        } elseif (
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_BUTTON
        ) {
            $relatedTableName = $relatedTableName = $this->tableName($modelClass::getLinkModelName());
            array_unshift($this->join, [
                "name" => "`".$relatedTableName."` as __link_model_table",
                "on"   => $tableAlias.".`".$modelClass::getLinkTableIdField()."` = `__link_model_table`.`id`",
            ]);

            $this->andWhere($tableAlias.".`".$modelClass::getMasterModelRelFieldName()."` = ".$params['masterId']);
            if (!call_user_func([$modelClass::getLinkModelName(), 'getPermanentlyDelete'])) {
                $this->andWhere("`__link_model_table`.del = 0");
            }
        } else {

            if (isset($params['masterId']) && $params['masterId'] && ($modelClass::getMasterModel() || $modelClass::getParentModel())) {
                $this->andWhere($tableAlias.'.'.$modelClass::getMasterModelRelFieldName().' = ' . intval($params['masterId']));
            }
            $this->from("{$tableName} as {$tableAlias}");
        }

        if ($this->join) {
            $join = $this->join;
            $this->join = [];
            foreach ($join as $item) {
                if (isset($item['name']) && isset($item['on'])) {
                    $this->leftJoin($item['name'], $item['on']);
                } else {
                    $this->join[] = $item;
                }
            }
        }

        if (isset($params['where'])) {
            $this->andWhere($params['where']);
        }

        if ($modelClass::getRecursive() && array_key_exists('parentId', $params)) {
            $this->andWhere(['parent_id' => $params['parentId']]);
        }

        if (!$modelClass::getPermanentlyDelete()) {
            $this->andWhere(["{$tableAlias}.del" => 0]);
        }

        // Начало говнокода, который надо будет извести
        $totalCount = false;
        if (!$modelClass::isSortable()) {
            $tmpQuery = clone $this;
            $tmpQuery->params = [];
            $totalCount = intval($tmpQuery->count());
        }
        // Конец говнокода, который надо будет извести

        $orderBy = [];
        if ($modelClass::isSortable()) {
            $orderBy["{$tableAlias}.sort_priority"] = SORT_ASC;
        } elseif (isset($params['sort']) && $params['sort']) {
            foreach ($params['sort'] as $sort) {

                if (isset($sort['property'])) {
                    $dir = SORT_ASC;

                    if (isset($sort['direction'])) {
                        $dir = (strtolower($sort['direction']) == 'desc' ? SORT_DESC : SORT_ASC);
                    }
                    if (isset($pointers[$sort['property']])) {
                        $orderBy["`".$pointers[$sort['property']]['table']."`.`".$pointers[$sort['property']]['field']."`"] = $dir;
                    } elseif (isset($calcFields[$sort['property']])) {
                        $orderBy["`".$sort['property']."`"] = $dir;
                    } else {
                        $orderBy[$tableAlias.".`".$sort['property']."`"] = $dir;
                    }
                }
            }
        }

        if(
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_CHECK
        ) {
            $orderBy = array_merge($orderBy, ["`__link_model_table`.id" => SORT_ASC]);
        }

        if ($modelClass::isSortable()) {
            $orderBy = array_merge($orderBy, [$tableAlias.".`sort_priority`" => SORT_ASC]);
        }

        if (!$orderBy && $modelClass::getDefaultSort() && !$modelClass::isSortable()) {
            $orderBy = $modelClass::getDefaultSort();
        }

        $this->orderBy(($orderBy ? $orderBy : null));

        if (!$modelClass::isSortable()) {
            if (isset($params['limit']) && $params['limit']) {
                $this->limit($params['limit']);
            }
            if (isset($params['start']) && $params['limit']) {
                $this->offset($params['start']);
            }
        }

        /**
         * @var $list array
         */
        $list = $this->asArray()->all();

        if (isset($params['identifyOnly']) && $params['identifyOnly'] && $identifyFieldName) {
            $_list = [];
            foreach ($list as $rowIndex => $row) {
                if (array_key_exists($identifyFieldName, $row)) {
                    $_list[] = [
                        'id' => $row['id'],
                        $identifyFieldName => $row[$identifyFieldName],
                    ];
                }
            }
            $list = $_list;
        } else {
            if ($this->pointers) {
                foreach ($list as $key => $item) {
                    foreach ($this->pointers as $fieldName => $some) {
                        if (isset($some['file_field'])) {
                            // Файл или изображение
                            $list[$key][$fieldName] = Json::encode([
                                'id' => $item[$fieldName],
                                'value' => $item['valof_'.$fieldName],
                                'fileName' => $item['fileof_'.$fieldName],
                            ]);
                        } else {
                            $list[$key][$fieldName] = Json::encode([
                                'id' => $item[$fieldName],
                                'value' => $item['valof_'.$fieldName]
                            ]);
                        }
                    }
                }
            }
            if ($this->colorFields) {
                foreach ($list as $key => $item) {
                    foreach ($this->colorFields as $fieldName) {
                        if ($list[$key][$fieldName]) {
                            $some = explode(',', $list[$key][$fieldName]);
                            $list[$key][$fieldName] = "#".str_pad(dechex(intval($some[0])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[1])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[2])), 2, STR_PAD_LEFT);
                        }
                    }
                }
            }

            if ($this->selectFields) {
                foreach ($list as $key => $item) {
                    foreach ($this->selectFields as $fieldName => $some) {
                        $list[$key][$fieldName] = Json::encode([
                            'id' => $item[$fieldName],
                            'value' => $item['valof_'.$fieldName]
                        ]);
                    }
                }
            }
        }

        $dataKey = (isset($params['dataKey']) ? $params['dataKey'] : 'data');
        $res = [$dataKey => $modelClass::afterList($list)];

        if ($modelClass::getRecursive() && isset($params['all']) && $params['all']) {
            // Получаем все дерево
            // todo me: Надо бы это как-то оптимизировать
            foreach ($res[$dataKey] as $i => $data) {
                $subQuery = new ActiveQuery($this->modelClass);
                $children = $subQuery->getList(array_merge($params, [
                    'parentId' => $data['id']
                ]));
                if ($children[$dataKey]) {
                    $res[$dataKey][$i][$dataKey] = $children[$dataKey];
                    $res[$dataKey][$i]['leaf'] = false;
                } else {
                    $res[$dataKey][$i]['leaf'] = true;
                }
            }
        }

        if ($totalCount !== false) {
            $res['total'] = $totalCount;
        }
        return $res;
    }
}