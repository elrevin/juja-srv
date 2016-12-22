<?php
namespace app\base\db;
use app\base\db\fields\Child;
use app\base\db\fields\Color;
use app\base\db\fields\Extended;
use app\base\db\fields\File;
use app\base\db\fields\Linked;
use app\base\db\fields\Pointer;
use app\base\db\fields\Select;
use app\base\db\fields\Simple;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class ActiveQuery extends \yii\db\ActiveQuery
{
    private $_tableAliases = [];
    private $_joins = [];
    private $_pointers = [];
    private $_pointersCount = 0;
    private $_fields = [];
    private $_calcFields = [];

    public function createCommand($db = null)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;

        if ($this->from) {
            if (is_array($this->from)) {
                if (ArrayHelper::isAssociative($this->from)) {
                    $from = array_keys($this->from)[0];
                } else {
                    $from = $this->from[0];
                }
            } else {
                $from = $this->from;
            }
        } else {
            $from = $modelClass::tableName();
        }

        if (!$modelClass::getPermanentlyDelete()) {
            $this->andWhere("{$from}.del = 0");
        }

        if ($modelClass::isSortable()) {
            $this->orderBy(["{$from}.sort_priority" => SORT_ASC]);
        }

        return parent::createCommand($db);
    }

    function getTableAlias($tableName, $level = 0)
    {
        $levelAlias = "L{$level}";
        if (array_key_exists($levelAlias, $this->_tableAliases)) {
            $aliases = $this->_tableAliases[$levelAlias];
            if (array_key_exists($tableName, $aliases)) {
                return $aliases[$tableName];
            }
        }

        $alias = "{$tableName}".($level ? "_{$levelAlias}" : "");
        $alias = str_replace(".", "_", $alias);

        if (!array_key_exists($levelAlias, $this->_tableAliases)) {
            $this->_tableAliases[$levelAlias] = [];
        }

        $this->_tableAliases[$levelAlias][$tableName] = $alias;
        return $alias;
    }

    function getField($modelClass, $field = '', $fieldAlias = '', $coef = 0, $level = 0)
    {
        /**
         * @var $modelClass ActiveRecord
         */
        $modelClass = ($modelClass ? $modelClass : $this->modelClass);

        $field = ($field ? $field : $modelClass::getIdentifyFieldConf()['name']);

        if (is_array($field)) {
            $fieldConf = $field;
            $field = $fieldConf['name'];
        } else {
            $fieldConf = $modelClass::getStructure($field);
        }

        $fieldAlias = ($fieldAlias ? $fieldAlias : $field);

        if ($fieldConf['type'] == 'fromlinked') {
            $modelClass = $modelClass::getLinkModelName();
            $tableAlias = '__link_model_table';
            $fieldConf = $modelClass::getStructure($field);
            $fieldAlias = ($fieldAlias ? $fieldAlias : $field);
        } else {
            $tableName = $modelClass::tableName();
            $tableAlias = $this->getTableAlias($tableName, $coef);
        }

        if ($fieldConf['expression']) {
            $fieldConf['expression'] = preg_replace("/\\{\\{\\s*tablename\\s*\\}\\}/i", $tableAlias, $fieldConf['expression']);
        }

        if ($fieldConf['addition']) {
            $relatedTableName = $fieldConf['additionTable'];
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $coef);
            $this->_joins[] = [
                'alias' => $relatedTableAlias,
                'name' => $relatedTableName,
                'on' => "`{$relatedTableAlias}`.master_table_id = `{$tableAlias}`.id AND `{$relatedTableAlias}`.master_table_name = '{$tableName}'",
            ];
            $tableAlias = $relatedTableAlias;
            $tableName = $relatedTableName;
        }

        if (in_array($fieldConf['type'], ['int', 'float', 'date', 'datetime', 'string', 'tinystring', 'text', 'html', 'bool', 'code'])) {
            $fieldObject = new Simple();
            $fieldObject->alias = $fieldAlias;
            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->type = $fieldConf['type'];
            $fieldObject->expression = $fieldConf['expression'];
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['color'])) {
            $fieldObject = new Color();
            $fieldObject->alias = $fieldAlias;
            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->expression = $fieldConf['expression'];
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['linked'])) {
            if (trim($modelClass::getLinkModelName(), '\\') == 'app\modules\files\models\Files') {
                $fieldObject = new File();

                $fieldObject->level = $coef;
                $fieldObject->name = $field;
                $fieldObject->alias = $fieldAlias;
                $fieldObject->tableAlias = $tableAlias;
                $fieldObject->type = 'file';

                $idField = new Simple();
                $idField->level = $coef;
                $idField->name = $field;
                $idField->tableAlias = $tableAlias;
                $idField->alias = $fieldAlias;
                $idField->expression = $fieldConf['expression'];
                $idField->type = 'int';
                $fieldObject->idField = $idField;

                /**
                 * @var $relatedModelClass ActiveRecord
                 */
                $relatedModelClass = '\app\modules\files\models\Files';

                $relatedIdentifyFieldConf = $relatedModelClass::getIdentifyFieldConf();

                $relatedTableName = $relatedModelClass::tableName();
                $this->_pointersCount++;
                $relatedTableAlias = $this->getTableAlias($relatedTableName, $this->_pointersCount);

                if ($fieldConf['expression']) {
                    $this->_joins[] = [
                        'alias' => $relatedTableAlias,
                        'name' => $relatedTableName,
                        'on' => ["`{$relatedTableAlias}`.id" => new Expression("(".$fieldConf['expression'].")")],
                    ];
                } else {
                    $this->_joins[] = [
                        'alias' => $relatedTableAlias,
                        'name' => $relatedTableName,
                        'on' => "`{$tableAlias}`.`{$fieldAlias}` = `{$relatedTableAlias}`.id",
                    ];
                }

                $fieldObject->valueField = $this->getField($relatedModelClass, $relatedIdentifyFieldConf['name'], "valof_{$fieldAlias}", $this->_pointersCount);
                $fieldObject->fileField = $this->getField($relatedModelClass, 'name', "fileof_{$fieldAlias}", $this->_pointersCount);
                $this->_pointers[$field] = $fieldObject;
                return $fieldObject;
            }
            $fieldObject = new Simple();
            $fieldObject->alias = $fieldAlias;
            $fieldObject->level = $coef;
            $fieldObject->name = 'id';
            $fieldObject->tableAlias = '__link_model_table';
            $fieldObject->type = $fieldConf['type'];
            $fieldObject->expression = $fieldConf['expression'];
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['select'])) {
            $fieldObject = new Select();
            $fieldObject->alias = $fieldAlias;
            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->expression = $fieldConf['expression'];
            $fieldObject->options = $fieldConf['selectOptions'];
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['pointer'])) {
            $fieldObject = new Pointer();

            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->alias = $fieldAlias;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->type = 'pointer';

            $idField = new Simple();
            $idField->level = $coef;
            $idField->name = $field;
            $idField->alias = ($level ? $fieldAlias."_id" : $fieldAlias);
            $idField->tableAlias = $tableAlias;
            $idField->expression = $fieldConf['expression'];
            $idField->type = 'int';
            $fieldObject->idField = $idField;

            /**
             * @var $relatedModelClass ActiveRecord
             */
            if (is_array($fieldConf['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$fieldConf['relativeModel']['moduleName'].'\models\\'.$fieldConf['relativeModel']['name'];
            } else {
                $relatedModelClass = $fieldConf['relativeModel'];
            }

            $relatedIdentifyFieldConf = $relatedModelClass::getIdentifyFieldConf();
            if ($relatedIdentifyFieldConf) {
                $relatedFieldName = $relatedIdentifyFieldConf['name'];
            }

            $relatedTableName = $relatedModelClass::tableName();
            $this->_pointersCount++;
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $this->_pointersCount);

            if ($fieldConf['expression']) {
                $this->_joins[] = [
                    'alias' => $relatedTableAlias,
                    'name' => $relatedTableName,
                    'on' => ["`{$relatedTableAlias}`.id" => new Expression("(".$fieldConf['expression'].")")],
                ];
            } else {
                $this->_joins[] = [
                    'alias' => $relatedTableAlias,
                    'name' => $relatedTableName,
                    'on' => "`{$tableAlias}`.`{$field}` = `{$relatedTableAlias}`.id",
                ];
            }

            $fieldObject->valueField = $this->getField($relatedModelClass, $relatedIdentifyFieldConf['name'], ($level ? $fieldAlias : "valof_{$fieldAlias}"), $this->_pointersCount, $level+1);
            $this->_pointers[$field] = $fieldObject;
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['file'])) {
            $fieldObject = new File();

            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->alias = $fieldAlias;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->type = 'file';

            $idField = new Simple();
            $idField->level = $coef;
            $idField->name = $field;
            $idField->tableAlias = $tableAlias;
            $idField->alias = $fieldAlias;
            $idField->expression = $fieldConf['expression'];
            $idField->type = 'int';
            $fieldObject->idField = $idField;

            /**
             * @var $relatedModelClass ActiveRecord
             */
            $relatedModelClass = '\app\modules\files\models\Files';

            $relatedIdentifyFieldConf = $relatedModelClass::getIdentifyFieldConf();

            $relatedTableName = $relatedModelClass::tableName();
            $this->_pointersCount++;
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $this->_pointersCount);

            if ($fieldConf['expression']) {
                $this->_joins[] = [
                    'alias' => $relatedTableAlias,
                    'name' => $relatedTableName,
                    'on' => ["`{$relatedTableAlias}`.id" => new Expression("(".$fieldConf['expression'].")")],
                ];
            } else {
                $this->_joins[] = [
                    'alias' => $relatedTableAlias,
                    'name' => $relatedTableName,
                    'on' => "`{$tableAlias}`.`{$fieldAlias}` = `{$relatedTableAlias}`.id",
                ];
            }

            $fieldObject->valueField = $this->getField($relatedModelClass, $relatedIdentifyFieldConf['name'], "valof_{$fieldAlias}", $this->_pointersCount);
            $fieldObject->fileField = $this->getField($relatedModelClass, 'name', "fileof_{$fieldAlias}", $this->_pointersCount);
            $this->_pointers[$field] = $fieldObject;
            return $fieldObject;
        } elseif (in_array($fieldConf['type'], ['fromextended'])) {
            $fieldObject = new Extended();
            $fieldObject->level = $coef;
            $fieldObject->name = $field;
            $fieldObject->alias = $fieldAlias;
            $fieldObject->tableAlias = $tableAlias;
            $relatedModelClass = $modelClass::getExtendedModelName();
            $relatedTableName = $relatedModelClass::tableName();
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $coef);

            $extendedModelRelFieldName = $modelClass::getExtendedModelRelFieldName();

            $this->_joins[] = [
                'alias' => $relatedTableAlias,
                'name' => $relatedTableName,
                'on' => "`{$tableAlias}`.`{$extendedModelRelFieldName}` = `{$relatedTableAlias}`.id",
            ];

            $fieldObject->parentField = $this->getField($modelClass::getExtendedModelName(), $field, $fieldAlias, $coef);
            return $fieldObject;
        }
    }

    /**
     * Если фильтр пришел в формате ExtJS, надо перевести его в удобоваримый
     * @param array $filter
     * @return array
     */
    public function prepareFilter($filter)
    {
        return [
            'field' => $filter['field'],
            'operation' => (isset($filter['comparison']) ? $filter['comparison'] : ''),
            'value' => (isset($filter['value']) ? $filter['value'] : null),
            'type' => (isset($filter['type']) ? $filter['type'] : null)
        ];
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
     *      'defaultFilterCondition'
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

        $params = $modelClass::beforeList($params);

        $modelClass::checkStructure();
        $modelClass::addAdditionFields();

        $identifyFieldName = '';

        $this->join = [];

        $tableName = $modelClass::tableName();
        $tableAlias = $this->getTableAlias($tableName);

        $this->select = ["id"=>"{$tableAlias}.id"];

        $structure = $modelClass::getStructure();

        if ($modelClass::getLinkModelName()) {
            if (!$structure) {
                /**
                 * @var $modelClass ActiveRecord
                 */
                $modelClass = $modelClass::getLinkModelName();
                $structure = $modelClass::getStructure();

                $structure[$modelClass::getLinkTableIdField()] = [
                    'title' => '',
                    'type' => 'linked',
                    'showInGrid' => false,
                    'name' => $modelClass::getLinkTableIdField(),
                    'expression' => false,
                    'addition' => false,
                ];
            } else {
                $hasLinked = false;
                foreach ($structure as $item) {
                    if ($item['type'] == 'linked') {
                        $hasLinked = true;
                    }
                }
                if (!$hasLinked) {
                    $structure = array_merge([
                        $modelClass::getLinkTableIdField() => [
                            'type' => 'linked',
                            'showInGrid' => false,
                            'name' => $modelClass::getLinkTableIdField(),
                            'expression' => false,
                            'addition' => false,
                        ]],
                        $structure
                    );
                }
            }
        }

        $aliasesForExpressions = [];
        
        foreach ($structure as $fieldName => $fieldConf) {
            if (isset($params['identifyOnly']) && $params['identifyOnly']) {
                if (isset($fieldConf['identify']) && $fieldConf['identify']) {
                    $identifyFieldName = $fieldName;
                }
            }

            $field = $this->getField($modelClass, $fieldConf, '', 0);
            if (!$field->expression) {
                $this->select = array_merge($this->select, $field->getSelect());
            }
            $this->params = array_merge($this->params, $field->params);
            $this->_fields[$fieldName] = $field;
            if ($field->type == 'pointer') {
                $aliasesForExpressions["{$field->name}_tablename"] = $field->valueField->tableAlias;
            }
        }

        if (($modelClass::getParentModel() || $modelClass::getMasterModel()) && $modelClass::getMasterModelRelFieldName() && !isset($this->_fields[$modelClass::getMasterModelRelFieldName()])) {
            $fieldAlias = $modelClass::getMasterModelRelFieldName();

            $fieldObject = new Pointer();

            $fieldObject->level = 0;
            $fieldObject->name = $fieldAlias;
            $fieldObject->alias = $fieldAlias;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->type = 'pointer';

            $idField = new Simple();
            $idField->level = 0;
            $idField->name = $fieldAlias;
            $idField->alias = $fieldAlias;
            $idField->tableAlias = $tableAlias;
            $idField->type = 'int';
            $fieldObject->idField = $idField;

            /**
             * @var $relatedModelClass ActiveRecord
             */
            $relatedModelClass = ($modelClass::getParentModel() ? $modelClass::getParentModel() : $modelClass::getMasterModel());

            $relatedIdentifyFieldConf = $relatedModelClass::getIdentifyFieldConf();
            if ($relatedIdentifyFieldConf) {
                $relatedFieldName = $relatedIdentifyFieldConf['name'];
            }

            $relatedTableName = $relatedModelClass::tableName();
            $this->_pointersCount++;
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $this->_pointersCount);

            $this->_joins[] = [
                'alias' => $relatedTableAlias,
                'name' => $relatedTableName,
                'on' => "`{$tableAlias}`.`{$fieldAlias}` = `{$relatedTableAlias}`.id",
            ];

            $fieldObject->valueField = $this->getField($relatedModelClass, $relatedIdentifyFieldConf['name'], "valof_{$fieldAlias}", $this->_pointersCount);
            $this->_pointers[$fieldAlias] = $fieldObject;

            $this->_fields[$fieldAlias] = $fieldObject;
            $aliasesForExpressions["{$fieldObject->name}_tablename"] = $fieldObject->valueField->tableAlias;
            $this->select = array_merge($this->select, $fieldObject->getSelect());
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && $modelClass::getRecursive()) {
            $fieldAlias = 'parent_id';

            $fieldObject = new Pointer();

            $fieldObject->level = 0;
            $fieldObject->name = $fieldAlias;
            $fieldObject->alias = $fieldAlias;
            $fieldObject->tableAlias = $tableAlias;
            $fieldObject->type = 'pointer';

            $idField = new Simple();
            $idField->level = 0;
            $idField->name = $fieldAlias;
            $idField->alias = $fieldAlias;
            $idField->tableAlias = $tableAlias;
            $idField->type = 'int';
            $fieldObject->idField = $idField;

            /**
             * @var $relatedModelClass ActiveRecord
             */
            $relatedModelClass = $modelClass::className();

            $relatedIdentifyFieldConf = $relatedModelClass::getIdentifyFieldConf();
            if ($relatedIdentifyFieldConf) {
                $relatedFieldName = $relatedIdentifyFieldConf['name'];
            }

            $relatedTableName = $relatedModelClass::tableName();
            $this->_pointersCount++;
            $relatedTableAlias = $this->getTableAlias($relatedTableName, $this->_pointersCount);

            $this->_joins[] = [
                'alias' => $relatedTableAlias,
                'name' => $relatedTableName,
                'on' => "`{$tableAlias}`.`{$fieldAlias}` = `{$relatedTableAlias}`.id",
            ];

            $fieldObject->valueField = $this->getField($relatedModelClass, $relatedIdentifyFieldConf['name'], "valof_{$fieldAlias}", $this->_pointersCount);
            $this->_pointers[$fieldAlias] = $fieldObject;

            $this->_fields[$fieldAlias] = $fieldObject;
            $aliasesForExpressions["{$fieldObject->name}_tablename"] = $fieldObject->valueField->tableAlias;
            $this->select = array_merge($this->select, $fieldObject->getSelect());
        }
        
        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && $modelClass::getHiddable()) {
            $this->select[] = "`{$tableAlias}`.`hidden`";
        }

        if(
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_CHECK
        ) {

            //Вызываем рак мозга у запроса - нам надо в секцию FROM запроса затолкать таблицу, которую мы подключаем, а основную сджоинить

            /**
             * @var $linkModelName ActiveRecord
             */
            $linkModelName = $modelClass::getLinkModelName();
            $relatedTableName = $linkModelName::tableName();

            $this->from(["__link_model_table" => $relatedTableName]);
            $this->select[] = "IF((`{$tableAlias}`.`id` IS NOT NULL AND ".
                "`{$tableAlias}`.`".$modelClass::getMasterModelRelFieldName()."` = {$params['masterId']}), 1, 0) AS `__check`";

            array_unshift($this->_joins, [
                "name" => $tableName,
                "alias" => $tableAlias,
                "on"   => "`__link_model_table`.`id` = `{$tableAlias}`.`".$modelClass::getLinkTableIdField()."` AND `{$tableAlias}`.`".$modelClass::getMasterModelRelFieldName()."` = {$params['masterId']}",
            ]);
            if (!$linkModelName::getPermanentlyDelete()) {
                $this->andWhere("`__link_model_table`.del = 0");
            }
        } elseif (
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_BUTTON
        ) {
            /**
             * @var $linkModelName ActiveRecord
             */
            $linkModelName = $modelClass::getLinkModelName();
            $relatedTableName = $linkModelName::tableName();

            array_unshift($this->_joins, [
                "name" => $relatedTableName,
                "alias" => '__link_model_table',
                "on"   => "`{$tableAlias}`.`".$modelClass::getLinkTableIdField()."` = `__link_model_table`.`id`",
            ]);

            $this->andWhere("`{$tableAlias}`.`".$modelClass::getMasterModelRelFieldName()."` = {$params['masterId']}");
            if (!$linkModelName::getPermanentlyDelete()) {
                $this->andWhere("`__link_model_table`.del = 0");
            }
            $this->from(["$tableName" => "$tableAlias"]);

        } else {

            if (isset($params['masterId']) && $params['masterId'] && ($modelClass::getMasterModel() || $modelClass::getParentModel())) {
                $this->andWhere("`{$tableAlias}`.`{$modelClass::getMasterModelRelFieldName()}` = ".intval($params['masterId']));
            }
            $this->from([$tableName => $tableAlias]);
        }

        /**
         * @var $field Simple
         */
        foreach ($this->_fields as $field) {
            if ($field->expression) {
                foreach ($aliasesForExpressions as $name => $alias) {
                    $field->expression = preg_replace("/\\{\\{\\s*".$name."\\s*\\}\\}/i", $alias, $field->expression);
                }
                $this->select = array_merge($this->select, $field->getSelect());
            }
        }


        if ($this->_joins) {
            $joins = [];
            foreach ($this->_joins as $join) {
                if (!in_array($join['alias'], $joins)) {
                    $this->leftJoin([$join['alias'] => $join['name']], $join['on']);
                    $joins[] = $join['alias'];
                }
            }
        }

        if (isset($params['where'])) {
            $this->andWhere($params['where']);
        }

        if ($modelClass::getRecursive() && array_key_exists('parentId', $params)) {
            $this->andWhere(["`{$tableAlias}`.parent_id" => $params['parentId']]);
        }

        if (!$modelClass::getPermanentlyDelete()) {
            $this->andWhere(["`{$tableAlias}`.del" => 0]);
        }

        if (isset($params['defaultId']) && $params['defaultId']) {
            $this->orWhere(["`{$tableAlias}`.id" => $params['defaultId']]);
        }

        if ($modelClass::$defaultFilter) {
            if (isset($params['filter'])){
                $params['filter'] = array_merge($modelClass::$defaultFilter, $params['filter']);
            } else {
                $params['filter'] = $modelClass::$defaultFilter;
            }
        }
        
        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                $filter = $this->prepareFilter($filter);
                $fieldName = $filter['field'];
                if (isset($this->_fields[$fieldName])) {
                    /**
                     * @var $field Simple
                     */
                    $field = $this->_fields[$fieldName];
                    $this->andWhere($field->getWhere($filter['operation'], $filter['value'], $filter['type']));
                }
            }
        }

        if (isset($params['defaultFilterCondition'])) {
            foreach ($params['defaultFilterCondition'] as $filter) {
                $fieldName = $filter['field'];
                if (isset($this->_fields[$fieldName])) {
                    /**
                     * @var $field Simple
                     */
                    $field = $this->_fields[$fieldName];
                    $this->andWhere($field->getWhere($filter['operation'], (isset($filter['value']) ? $filter['value'] : null), (isset($filter['type']) ? $filter['type'] : null)));
                }
            }
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
            $orderBy["`{$tableAlias}`.sort_priority"] = SORT_ASC;
        } elseif (isset($params['sort']) && $params['sort']) {
            foreach ($params['sort'] as $sort) {

                if (isset($sort['property'])) {
                    $dir = SORT_ASC;

                    if (isset($sort['direction'])) {
                        $dir = (strtolower($sort['direction']) == 'desc' ? SORT_DESC : SORT_ASC);
                    }
                    if (isset($this->_pointers[$sort['property']])) {
                        /**
                         * @var $fieldObject Pointer
                         */
                        $fieldObject = $this->_pointers[$sort['property']];
                        $orderBy["`{$fieldObject->getValueField()->tableAlias}`.`{$fieldObject->getValueField()->alias}`"] = $dir;
                    } else {
                        $prop = explode(".", $sort['property']);
                        if (count($prop) > 1) {
                            $orderBy["`{$prop[0]}`.`{$prop[1]}`"] = $dir;
                        } else {
                            if (isset($this->_fields[$sort['property']])) {
                                $field = $this->_fields[$sort['property']];

                                $orderBy[$field->getOrder()] = $dir;
                            } elseif ($sort['property' == 'id']) {
                                $orderBy["`{$tableAlias}`.id"] = $dir;
                            }
                        }
                    }
                }
            }
        }
        $groupBy = [];
        if (isset($params['group']) && $params['group']) {
            foreach ($params['group'] as $group) {

                if (isset($group['property'])) {
                    if (isset($this->_pointers[$group['property']])) {
                        /**
                         * @var $fieldObject Pointer
                         */
                        $fieldObject = $this->_pointers[$group['property']];
                        $groupBy[] ="`{$fieldObject->getValueField()->tableAlias}`.`{$fieldObject->getValueField()->alias}`";
                    } else {
                        $prop = explode(".", $group['property']);
                        if (count($prop) > 1) {
                            $groupBy[] = "`{$prop[0]}`.`{$prop[1]}`";
                        } else {
                            if (isset($this->_fields[$group['property']])) {
                                $field = $this->_fields[$group['property']];
                                $groupBy[] = $field->getGroup()."_";
                            }
                        }
                    }
                }
            }
        }

        if(
            $modelClass::getMasterModelRelationsType() == $modelClass::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY &&
            $modelClass::getSlaveModelAddMethod() == $modelClass::SLAVE_MODEL_ADD_METHOD_CHECK
        ) {
            $orderBy["`__link_model_table`.id"] = SORT_ASC;
        }

        if (!$orderBy && $modelClass::getDefaultSort() && !$modelClass::isSortable()) {
            $orderBy = $modelClass::getDefaultSort();
        }

        $this->orderBy($orderBy);
        $this->groupBy($groupBy);

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
            /**
             * @var $field Simple
             */
            foreach ($this->_fields as $field) {
                foreach ($list as $key => $item) {
                    if ($item) {
                        $list[$key][$field->alias] = $field->getListVal($list[$key]);
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