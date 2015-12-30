<?php
namespace app\base\db;

use app\models\SDataHistory;
use app\models\SDataHistoryEvents;
use app\modules\files\models\Files;
use Yii;
use yii\db;
use yii\helpers\Json;

class ActiveRecord extends db\ActiveRecord
{
    const MASTER_MODEL_RELATIONS_TYPE_MASTER_DETAIL = "master_detail";
    const MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY = "many_to_many";

    const SLAVE_MODEL_ADD_METHOD_BUTTON = "button";
    const SLAVE_MODEL_ADD_METHOD_CHECK = "check";

    /**
     * Список поведений подключенных к данной модели
     * @var array
     */
    protected static $behaviorsList = [];

    protected static $processedAdditionFieldsBehaviors = false;

    /**
     * Структура модели.
     * Массив в первом уровне в качестве ключей элементов используются имена полей в таблице
     * каждый такой элемент - вложенный ассоциативный массив:
     *      'title' - Название поля,
     *
     *      'calc' - Если true, то поле вычисляемое, для таих полей актуально значение formula,
     *
     *      'expression' - SQL выражение, для вычисляемого поля,
     *
     *      'tooltip' - подсказка к полю,
     *
     *      'type' - тип поля:
     *          'int' - целое число,
     *          'float' - число с точкой,
     *          'string' - строка, в mysql varchar(1024),
     *          'tinystring' - строка, в mysql varchar(256),
     *          'text' - многострочный текст, редактируется textarea, в mysql - longtext
     *          'html' - многострочный текст с форматированием, редактируется tinymce, в mysql - longtext
     *          'date' - дата
     *          'datetime' - дата и время
     *          'pointer' - ссылка на запись в другой модели, в mysql - int(11) и внешний ключ ссылающийся на другую модель, имя
     *              которой указано в relativeModel
     *          'linked' - ссылка на запись в связанной модели (ее имя указано в $linkModelName), в mysql - int(11) и внешний ключ ссылающийся на другую модель, имя
     *              которой указано в relativeModel
     *          'select' - одно из предустановленных значений, значения указываются в selectOptions
     *          'file' - файл, в mysql - int(11) с внешним ключем на модель s_files
     *          'bool' - флаг, в mysql tinyint(1)
     *          'color' - цвет, в mysql это varchar(11), цвет может быть в двух форматах (см. описание параметра colorFormat)
     *
     *      'settings' - Дополнительные настройки (ассоциативный массив):
     *          'maxLength' - максимальная длина (для строк)
     *          'minLength' - максимальная длина (для строк)
     *          'min' - минимальное значение,
     *          'max' - максимальное значение,
     *          'round' - количество цифр после точки (при сохранении применяется округление),
     *          'width' - ширина столбца,
     *          ...
     *
     *      'mask' - для полей типа string и tinystring можно указывать маску, в которой маркерами обозначаются вводимые символы:
     *          9 - любая цифра.
     *          a - любая буква русского или латинского алфавитов.
     *          * - любая буква русского или латинского алфавитов или любая цифра.
     *
     *      'includeMaskInValue' - если true, то символы маски будут добавлены в значение (по умолчанию), например, если маска
     *        "8(999) 999-99-99", то при вводе в значение попадут так же восмерка, пробелы и прочие символы, а если в
     *        includeMaskInValue будет false, то значение будет очищена от этих символов
     *
     *      'regexp' - регалярное выражение для проверки значения поля
     *
     *      'regexpError' - сообщение об ошибке, если значение не соответствует указанному регулярному выражению
     *
     *      'editInGrid' - если установлено true, то разрешено редактирование прямо в списке (кроме файлов и полей типа pointer)
     *
     *      'group' - Название группы полей,
     *
     *      'keepHistory' - Если true, то автоматически будет сохраняться история значений поля,
     *
     *      'colorFormat' - Формат цвета:
     *          'hex' - шестнадцатиричный формат ("#ffffff")
     *          'dec' - десятичный формат ("255,255,255")
     *
     *      'relativeModel' - имя связанной модели (полное имя класса, включая namespace) или ассоциативный массив:
     *          'classname' - полное имя класса,
     *          'moduleName' - имя модуля,
     *          'modalSelect' - выбирать запись в модальном окне,
     *          'name' - имя модели,
     *          'runAction' - массив: имя модуля, имя контроллера, имя действия; для получения пользовательского интерфейса,
     *
     *      'autocomplete' - true если нужно чтобы в pointer поле была авто подстановка,
     *
     *      'selectOptions' - Ассоциативный массив возможных значений,
     *
     *      'showInGrid' - true если поле отображается в таблице записей, false - поле скрывается, по умолчанию true,
     *
     *      'showCondition' - Условия отображения поля - ассоциативный массив, в котором ключи - это имена других полей,
     *        а значения это условие или массив условий, каждое из которых - ассоциативный массив (все условия в итоге
     *        объединяются оператором AND):
     *            'operation' - операция:
     *                  '==' - равно,
     *                  '!=' - не равно
     *                  '>' - больше
     *                  '<' - меньше
     *                  '>=' - больше или равно
     *                  '<=' - меньше или равно
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
     *                  [
     *                      'operation' => '>',
     *                      'value' => '5000'
     *                  ], [
     *                      'operation' => '<',
     *                      'value' => '8000'
     *                  ]
     *              ]
     *          ]
     *          При таких условиях данное поле будет отображаться если установлен флаг-поле payd и значение поля
     *          sum > 5000 AND sum < 8000
     *
     *      'filterCondition' - условие для фильтрации значений поля типа pointer - ассоциативный массив, котором
     *          ключи - это имена других полей, а значения это условие или массив условий, каждое из которых -
     *          ассоциативный массив (все условия в итоге объединяются оператором AND):
     *          'comparison' - условие сравнения:
     *                  '==' - равно,
     *                  '!=' - не равно
     *                  '>' - больше
     *                  '<' - меньше
     *                  '>=' - больше или равно
     *                  '<=' - меньше или равно
     *                  'like', - " like '%VALUE%'"
     *                  'start' - " like 'VALUE%' ",
     *                  'end' - " like '%VALUE' ",
     *          'field' - имя поля в таблице
     *
     *          Например есть поле "price" (тип int) и "manager" (тип pointer ссылается на таблицу managers), в таблице
     *          managers есть поле max_price значение которого показывает максимальный ценовой порог, при превышении которого
     *          товар не может быть продан данным менеджером, и прии редактировании товара необходимо выбрать менеджера из
     *          числа доступных. В таком случае условие для поля "manager" будет выглядеть так:
     *
     *          'filterCondition' => [
     *              'price' => [
     *                  'comparison' => '<',
     *                  'field' => 'max_price'
     *              ]
     *          ]
     *
     *          При описании модели-детализации, часто нужно фильтровать поля по значению какого-нибудь поля master модели,
     *          для этого просто в качестве ключа в массиве filterCondition указываем поле master модели с префиксом "_master_model.".
     *          Пример из жизни. Интернет магазин Коллибриум, продает краску для волос, и там у каждого производителя есть палитра цветов,
     *          в которой все цвета сгруппированы - есть группы, привязанные к производителям и редактирующиеся в отдельной вкладке, и
     *          есть цвета, которые так же привязаны к производителями и редактируются в отдельной вкладке, но еще они привязаны к группам
     *          так вот при создании (изменении) цвета, нужно в его свойствах указать группу из списка, а группы нужно фильтровать по
     *          производителю, вот такое можно описать так:
     *
     *          'filterCondition' => [
     *              '_master_table.id' => [
     *                  'comparison' => '==',
     *                  'field' => 'master_table_id',
     *              ],
     *          ],
     *
     *
     *      'default' - значение по умолчанию, для полей типа pointer это может быть только id в подключенном
     *              справочнике, для поля типа select это значение ключа.
     *              Значения здесь могут быть вычисляемымми на стороне SQL, выражение указывается так же как и
     *                  в параметре expression
     *              Кроме того значения могут быть вычисляемыми на стороне JavaScript, для этого можно указать
     *                  js выражение, зависимость от изменений в другом поле или указать необходимость загрузки
     *                  скрипта модификатора.
     *
     *              Пример простого значения:
     *                  'default' => 'Тест'
     *
     *
     *
     *      'identify' - если true, то поле однозначно идентифицирует запись, например поле 'title' - название
     *
     *      'required' - поле обязательно для заполнения
     *
     *      'readonly' - поле не доступно для редактирования
     *
     *      'allowGroupEdit' - если равно false, то разрешено групповое редактирование
     *
     * @var array
     */
    protected static $structure = [];

    protected $pointerAttributes = [];

    /**
     * Если true - записи удаляются перманентно, если false, метятся, как удаленные путем установки del=1
     * @var bool
     */
    protected static $permanentlyDelete = true;

    /**
     * Если true то в модель нельзя ничего писать
     * @var bool
     */
    protected static $readonly = false;

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
     * Если модель рекурсивная (древовидная), то это свойство = true
     * @var bool
     */
    protected static $recursive = false;

    /**
     * Если модель рекурсивная (древовидная), и возможен только один корень
     * @var bool
     */
    protected static $singleRoot = false;

    /**
     * Если true, то модель всегда содержит только одну запись
     * @var bool
     */
    protected static $singleRecord = false;

    /**
     * Имя класса "master" модели с пространством имен
     * @var string
     */
    protected static $masterModel = '';

    /**
     * Условия отображения detail модели в панели управления.
     * Ассоциативный массив, в котором ключи - это поля, а значения это условие или массив условий,
     * каждое из которых - ассоциативный массив (все условия в итоге
     *        объединяются оператором AND):
     *            'operation' - операция:
     *                  '==' - равно,
     *                  '!=' - не равно
     *                  '>' - больше
     *                  '<' - меньше
     *                  '>=' - больше или равно
     *                  '<=' - меньше или равно
     *                  'set' - установленно
     *                  'notset' - не установленно
     *             'value' - значение
     *
     *    +-----------------------------------------------------------+
     *    | Важно! Все условия описываются относительно master модели |
     *    +-----------------------------------------------------------+
     *
     *          пример условия:
     *          [
     *              'payd' => [ // поле payd
     *                  'operation' => 'set' // установленно
     *              ]
     *          ]
     *          При таком условии модель будет отображаться если установлен флаг-поле payd
     *
     *          [
     *              'payd' => [
     *                  'operation' => 'set'
     *              ],
     *              'sum' => [
     *                  [
     *                      'operation' => '>',
     *                      'value' => '5000'
     *                  ], [
     *                      'operation' => '<',
     *                      'value' => '8000'
     *                  ]
     *              ]
     *          ]
     *          При таких условиях модель будет отображаться если установлен флаг-поле payd и значение поля
     *          sum > 5000 AND sum < 8000
     *
     *  В качестве ключей в showCondition могут быть указаны поля из родительской модели, они должны указываться с
     * префиксом "_parent_table"
     * @var array
     */
    protected static $showCondition = [];

    /**
     * Имя поля в таблице для связи с родительской или master моделью
     * @var string
     */
    protected static $masterModelRelFieldName = 'master_table_id';

    /**
     * Имя класса родительской модели
     * @var string
     */
    protected static $parentModel = '';

    /**
     * Имя модели связанной с родительской данной, через нее
     * @var string
     */
    protected static $linkModelName = '';

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
     * Название записи в единственном числе в именительном падеже, например "характеристика"
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

    /**
     * Выбор записей для полей Pointer в модальном окне
     * @var bool
     */
    public static $modalSelect = false;


    public static $masterModelRelationsType = "master_detail";

    /**
     * Способ добавления записи в таблицу сзязи many_to_many:
     *      'button' (static::SLAVE_MODEL_ADD_METHOD_BUTTON) - будет создана кнопка "добавить",
     *          при нажатии на которую будет появляться диалог выбора добавляемой записи
     *      'check' (static::SLAVE_MODEL_ADD_METHOD_CHECK) - будет создан грид, в котором будут выводиться все записи из
     *          подключаемой таблицы, и будет столбец с чекбоксами которыми можно выбирать
     * @var string
     */
    public static $slaveModelAddMethod = 'button';

    /**
     * Доступна ли ручная сортировка
     *
     * @var bool
     */
    public static $sortable = false;

    /**
     * Настройки сортировки по умолчанию
     *
     * например для новостей:
     * ['date' => SORT_DESC]
     *
     * @var array
     */
    public static $defaultSort = [];

    public static $detailModels = [];
    public static $childModel = [];

    protected static $haveRightsRules = true;

    protected $oldDirtyAttributes = [];

    public function __get($name)
    {
        $getter = '';
        if (method_exists($this, 'get' . $name)) {
            $getter = 'get' . $name;
        } elseif (method_exists($this, 'get_' . $name)) {
            $getter = 'get_' . $name;
        }
        $name_ = str_replace('_', '', 'get' . $name);
        if (method_exists($this, $name_)) {
            $getter = $name_;
        }

        if ($getter) {
            $refl = new \ReflectionMethod(static::className(), $getter);
            if (!$refl->isStatic()) {
                return $this->{$getter}();
            }
        }

        $structure = static::getStructure();
        if (strncmp($name, 'valof_', 6) == 0 && array_key_exists($key = str_replace('valof_', '', $name), $structure)) {
            if ($structure[$key]['type'] == 'select') {
                $val = $this->{$key};
                if (array_key_exists($val, $structure[$key]['selectOptions'])) {
                    return $structure[$key]['selectOptions'][$val];
                }
                return null;
            }
            if ($structure[$key]['type'] == 'pointer') {
                if (array_key_exists($key, $this->pointerAttributes)) {
                    return $this->pointerAttributes[$key];
                }
                $val = $this->getAttribute($key);
                if (is_array($structure[$key]['relativeModel'])) {
                    $relatedModelClass = '\app\modules\\'. $structure[$key]['relativeModel']['moduleName'].'\models\\'. $structure[$key]['relativeModel']['name'];
                } else {
                    $relatedModelClass = $structure[$key]['relativeModel'];
                }

                $hiddable = call_user_func([$relatedModelClass, 'getHiddable']);

                $val = call_user_func([$relatedModelClass, 'find'])->andWhere(['id' => $val]);
                if ($hiddable) {
                    $val->andWhere(['hidden' => 0]);
                }

                $val = $val->one();
                $this->pointerAttributes[$key] = $val;
                return $val;
            }
            return null;
        }

        if (array_key_exists($name, $structure) && $structure[$name]['type'] == 'pointer') {
            $val = $this->getAttribute($name);
            if (is_array($structure[$name]['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'. $structure[$name]['relativeModel']['moduleName'].'\models\\'. $structure[$name]['relativeModel']['name'];
            } else {
                $relatedModelClass = $structure[$name]['relativeModel'];
            }

            $hiddable = call_user_func([$relatedModelClass, 'getHiddable']);

            $val = call_user_func([$relatedModelClass, 'find'])->andWhere(['id' => $val]);
            if ($hiddable) {
                $val->andWhere(['hidden' => 0]);
            }

            return $val->one();
        }

        $detailsModel = static::getDetailModels();
        foreach ($detailsModel as $model) {
            $modelClassNameParts = explode('\\', $model);
            $countOfModelClassNameParts = count($modelClassNameParts);
            if ($name == lcfirst($modelClassNameParts[$countOfModelClassNameParts - 1]) && !method_exists($this, 'get'.$name)) {
                $masterModelRelFieldName = call_user_func([$model, 'getMasterModelRelFieldName']);
                $permanentlyDelete = call_user_func([$model, 'getPermanentlyDelete']);
                $hiddable = call_user_func([$model, 'getHiddable']);
                $where = [];
                if (!$permanentlyDelete) {
                    $where["del"] = 0;
                }
                if ($hiddable) {
                    $where["hidden"] = 0;
                }
                return $this->hasMany($model, [$masterModelRelFieldName => 'id'])->where($where)->all();
            }
        }

        $childModel = static::getChildModel();
        if ($childModel) {
            $modelClassNameParts = explode('\\', $childModel);
            $countOfModelClassNameParts = count($modelClassNameParts);

            if ($name == lcfirst($modelClassNameParts[$countOfModelClassNameParts - 1]) && !method_exists($this, 'get'.$name)) {
                $masterModelRelFieldName = call_user_func([$childModel, 'getMasterModelRelFieldName']);
                $permanentlyDelete = call_user_func([$childModel, 'getPermanentlyDelete']);
                $hiddable = call_user_func([$childModel, 'getHiddable']);
                $where = [];
                if (!$permanentlyDelete) {
                    $where["del"] = 0;
                }
                if ($hiddable) {
                    $where["hidden"] = 0;
                }
                return $this->hasMany($childModel, [$masterModelRelFieldName => 'id'])->where($where)->all();
            }
        }

        if (isset($structure[$name]) && $structure[$name]['type'] == 'file') {
            return Files::find()->where(['id' => parent::__get($name)])->one();
        }

        if (isset($structure[$name]) && $structure[$name]['type'] == 'pointer') {
            if (is_array($structure[$name]['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$structure[$name]['relativeModel']['moduleName'].'\models\\'.$structure[$name]['relativeModel']['name'];
            } else {
                $relatedModelClass = $structure[$name]['relativeModel'];
            }

            return call_user_func([$relatedModelClass, "find"])->andWhere(['id' => parent::__get($name)])->one();
        }

        if (isset($structure[$name]) && $structure[$name]['type'] == 'linked' && !method_exists($this, 'get'.$name) && preg_match("/\\Files$/", static::$linkModelName)) {
            return Files::find()->where(['id' => parent::__get($name)])->one();
        }

        if (isset($structure[$name]) && $structure[$name]['type'] == 'linked') {
            $relatedModelClass = static::$linkModelName;

            return call_user_func([$relatedModelClass, "find"])->andWhere(['id' => parent::__get($name)])->one();
        }

        if ($name == 'parent' && static::$recursive) {
            return static::find()->andWhere(['id' => $this->parent_id])->one();
        }

        return parent::__get($name);
    }

    public function __set($name, $val)
    {
        $structure = static::getStructure();
        if (array_key_exists($name, $structure)) {
            if (
                ($structure[$name]['type'] == 'string' ||
                    $structure[$name]['type'] == 'tinystring' ||
                    $structure[$name]['type'] == 'color' ||
                    $structure[$name]['type'] == 'text' ||
                    $structure[$name]['type'] == 'html') && !$val
            ) {
                $val = '';
            } elseif (
                ($structure[$name]['type'] == 'int' ||
                    $structure[$name]['type'] == 'float') && !$val
            ) {
                $val = 0;
            } elseif (($structure[$name]['type'] == 'pointer' || $structure[$name]['type'] == 'select') && !$val) {
                $val = null;
            } elseif ($structure[$name]['type'] == 'bool' && !$val) {
                $val = 0;
            }
        }
        return parent::__set($name, $val);
    }

    protected static function createTableCol($fieldName, $field) {
        $tableName = static::tableName();
        if ($field['type'] == 'string') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(1024) NOT NULL DEFAULT ''
            ")->execute();
        } elseif ($field['type'] == 'tinystring') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(256) NOT NULL DEFAULT ''
            ")->execute();
        } elseif ($field['type'] == 'color') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(11) NOT NULL DEFAULT ''
            ")->execute();
        } elseif ($field['type'] == 'text' || $field['type'] == 'html') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` LONGTEXT
            ")->execute();
        } elseif ($field['type'] == 'int') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'float') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DOUBLE NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'bool') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` tinyint(1) NOT NULL DEFAULT 0
            ")->execute();
        } elseif ($field['type'] == 'select') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` VARCHAR(256) DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'date') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DATE DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'datetime') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` DATETIME DEFAULT NULL
            ")->execute();
        } elseif ($field['type'] == 'pointer') {
            if (is_array($field['relativeModel'])) {
                $relatedModelClass = '\app\modules\\'.$field['relativeModel']['moduleName'].'\models\\'.$field['relativeModel']['name'];
            } else {
                $relatedModelClass = $field['relativeModel'];
            }
            $tmp = call_user_func([$relatedModelClass, 'tableName']);
            $command = ["ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) DEFAULT NULL"];
            if (strpos($tmp, '.') === false) {
                $command[] = "ADD CONSTRAINT `". $tableName ."__".$fieldName."` FOREIGN KEY (`".$fieldName."`) REFERENCES `". $tmp ."`(id) ON DELETE SET NULL ON UPDATE CASCADE";
            }
            Yii::$app->db->createCommand(implode(", ", $command))->execute();
        } elseif ($field['type'] == 'file') {
            Yii::$app->db->createCommand("
                ALTER TABLE `". $tableName ."` ADD COLUMN `".$fieldName."` int(11) DEFAULT NULL,
                    ADD CONSTRAINT `". $tableName ."__".$fieldName."` FOREIGN KEY (`".$fieldName."`) REFERENCES `s_files`(id) ON DELETE SET NULL ON UPDATE CASCADE
            ")->execute();
        }
    }

    public static function checkStructure () {
        if (YII_DEBUG) {
            // Проверяем наличие таблицы
            $tableName = static::tableName();
            $table = Yii::$app->db->createCommand("Show tables like '". $tableName ."'")->queryAll();
            $cols = [];
            if (!$table) {
                // Создаем таблицу
                Yii::$app->db->createCommand("
                    CREATE TABLE `". $tableName ."` (
                        id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)
                    )
                    ENGINE = INNODB
                    CHARACTER SET utf8
                    COLLATE utf8_general_ci
                ")->execute();
            } else {
                $tmp = Yii::$app->db->createCommand("SHOW COLUMNS FROM `". $tableName ."`")->queryAll();
                foreach ($tmp as $col) {
                    $cols[$col['Field']] = $col;
                }

            }

            // Перманентное удаление
            if (!static::$permanentlyDelete && !array_key_exists('del', $cols)) {
                // Добавляем поле 'del'
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` ADD COLUMN `del` tinyint(1) NOT NULL DEFAULT 0
                ")->execute();
            } elseif (static::$permanentlyDelete && array_key_exists('del', $cols)) {
                // Удаляем записи помеченные на удаление и поле del
                Yii::$app->db->createCommand("
                    DELETE FROM `". $tableName ."` WHERE `del` = 1
                ")->execute();

                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` DROP COLUMN `del`
                ")->execute();
            }

            if (static::$recursive && !array_key_exists('parent_id', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        ADD COLUMN `parent_id` int(11) DEFAULT NULL,
                        ADD CONSTRAINT `". $tableName ."__parent_id` FOREIGN KEY (parent_id) REFERENCES `". $tableName ."`(id)  ON DELETE CASCADE ON UPDATE CASCADE
                ")->execute();
            } elseif (!static::$recursive && array_key_exists('parent_id', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        DROP FOREIGN KEY `". $tableName ."__parent_id`
                ")->execute();
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        DROP COLUMN `parent_id`,
                ")->execute();
            }

            if (static::$parentModel && !array_key_exists(static::$masterModelRelFieldName, $cols)) {
                $tmp = call_user_func([static::$parentModel, 'tableName']);
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        ADD COLUMN `".static::$masterModelRelFieldName."` int(11) DEFAULT NULL,
                        ADD CONSTRAINT `". $tableName ."__".static::$masterModelRelFieldName."` FOREIGN KEY (`".static::$masterModelRelFieldName."`) REFERENCES `". $tmp ."`(id) ON DELETE CASCADE ON UPDATE CASCADE
                ")->execute();
            }

            if (static::$masterModel && !array_key_exists(static::$masterModelRelFieldName, $cols)) {
                $tmp = call_user_func([static::$masterModel, 'tableName']);
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        ADD COLUMN `".static::$masterModelRelFieldName."` int(11) DEFAULT NULL,
                        ADD CONSTRAINT `". $tableName ."__".static::$masterModelRelFieldName."` FOREIGN KEY (`".static::$masterModelRelFieldName."`) REFERENCES `". $tmp ."`(id) ON DELETE CASCADE ON UPDATE CASCADE
                ")->execute();
            }

            if (static::$masterModel && static::$masterModelRelationsType == self::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY && static::$linkModelName && !array_key_exists(static::getLinkTableIdField(), $cols)) {
                $tmp = call_user_func([static::$linkModelName, 'tableName']);
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."`
                        ADD COLUMN `".static::getLinkTableIdField()."` int(11) DEFAULT NULL,
                        ADD CONSTRAINT `". $tableName ."__link_table_id` FOREIGN KEY (`".static::getLinkTableIdField()."`) REFERENCES `". $tmp ."`(id) ON DELETE CASCADE ON UPDATE CASCADE
                ")->execute();
            }

            if (static::$hiddable && !array_key_exists('hidden', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` ADD COLUMN `hidden` tinyint(1) NOT NULL DEFAULT 0
                ")->execute();
            } elseif (!static::$hiddable && array_key_exists('hidden', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` DROP COLUMN `hidden`
                ")->execute();
            }

            if (static::$sortable && !array_key_exists('sort_priority', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` ADD COLUMN `sort_priority` int(11) NOT NULL DEFAULT 0
                ")->execute();
            } elseif (!static::$sortable && array_key_exists('sort_priority', $cols)) {
                Yii::$app->db->createCommand("
                    ALTER TABLE `". $tableName ."` DROP COLUMN `sort_priority`
                ")->execute();
            }

            // Проверяем структуру

            $structure = static::getStructure();
            foreach ($structure as $name => $field) {
                if (!isset($field['addition']) && !isset($field['calc']) && !array_key_exists($name, $cols)) {
                    static::createTableCol($name, $field);
                }
            }
        }
    }

    public static function getLinkTableIdField ()
    {
        if (static::$masterModel && static::$masterModelRelationsType == self::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY) {
            $structure = static::getStructure();
            foreach ($structure as $name => $field) {
                if ($field['type'] == 'linked') {
                    return $name;
                }
                return 'link_table_id';
            }
        }
        return '';
    }

    public static function isSortable()
    {
        return static::$sortable;
    }

    public static function getHaveRightsRule ()
    {
        return static::$haveRightsRules;
    }

    public function behaviors()
    {
        return static::$behaviorsList;
    }

    public static function getRunAction ()
    {
        return [static::getModuleName(), 'main', 'get-interface'];
    }

    public static function find()
    {
        $query = parent::find();
        $cond = static::defaultWhere();
        if ($cond) {
            $query->andWhere(static::defaultWhere());
        }
        if (static::$sortable) {
            $query->orderBy(['sort_priority' => SORT_ASC]);
        }
        return $query;
    }

    protected  static function defaultWhere()
    {
        if (!static::$permanentlyDelete) {
            return "`" . static::tableName() . "`.del = 0";
        }
        return [];
    }

    public function rules()
    {
        $rules = [];
        $structure = static::getStructure();
        foreach ($structure as $name => $field) {
            if (isset($field['calc']) && ['calc']) {
                continue;
            }
            if (isset($field['required']) && $field['required']) {
                $rules[] = [
                    [$name], 'required',
                    'message' => 'Поле "' . $field['title'] . '" обязательно для заполнения.'
                ];
            }

            if ($field['type'] == 'string') {
                $rules[] = [
                    [$name], 'string', 'min' => (isset($field['minLength']) ? $field['minLength'] : null),
                    'max' => (isset($field['maxLength']) ? $field['maxLength'] : 1024),
                    'tooLong' => 'Поле "' . $field['title'] . '" не может быть длинее 1024 символа.'
                ];
                if (isset($field['regexp']) && $field['regexp']) {
                    $rules[] = [
                        [$name], 'match', 'pattern' => $field['regexp'],
                        'message' => (isset($field['regexpError']) ? $field['regexpError'] : 'Поле "' . $field['title'] . '" имеет не верный формат.1'),
                    ];
                }
            } elseif ($field['type'] == 'tinystring') {
                $rules[] = [
                    [$name], 'string', 'min' => (isset($field['minLength']) ? $field['minLength'] : null),
                    'max' => (isset($field['maxLength']) ? $field['maxLength'] : 256),
                    'tooLong' => 'Поле "' . $field['title'] . '" не может быть длинее 1024 символа.'
                ];
                if (isset($field['regexp']) && $field['regexp']) {
                    $rules[] = [
                        [$name], 'match', 'pattern' => $field['regexp'],
                        'message' => (isset($field['regexpError']) ? $field['regexpError'] : 'Поле "' . $field['title'] . '" имеет не верный формат.2'),
                    ];
                }
            } elseif ($field['type'] == 'int') {
                $rules[] = [
                    [$name], 'integer', 'integerOnly' => true, 'min' => (isset($field['min']) ? $field['min'] : null),
                    'max' => (isset($field['max']) ? $field['max'] : null),
                    'tooSmall' => 'Значение поля "' . $field['title'] . '" не может быть меньше '.(isset($field['min']) ? $field['min'] : '0').'.',
                    'tooBig' => 'Значение поля "' . $field['title'] . '" не может быть больше '.(isset($field['max']) ? $field['max'] : '0').'.',
                ];
            } elseif ($field['type'] == 'float') {
                $rules[] = [
                    [$name], 'double', 'integerOnly' => false, 'min' => (isset($field['min']) ? $field['min'] : null),
                    'max' => (isset($field['max']) ? $field['max'] : null),
                    'tooSmall' => 'Значение поля "' . $field['title'] . '" не может быть меньше '.(isset($field['min']) ? $field['min'] : '0').'.',
                    'tooBig' => 'Значение поля "' . $field['title'] . '" не может быть больше '.(isset($field['max']) ? $field['max'] : '0').'.',
                ];
            } elseif ($field['type'] == 'select') {
                $rules[] = [
                    $name, 'validateSelectValue',
                ];
            }
        }

        return $rules;
    }

    public function validateSelectValue($field, $params)
    {
        $structure = static::getStructure();
        if (!array_key_exists($this->{$field}, $structure[$field]['selectOptions'])) {
            $this->addError($field, 'Недопустимое значение поля "'. $structure[$field]['title'].'", необходимо выбрать одно из предложенных значений');
        }
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

    public static function getSingleRoot () {
        return static::$singleRoot;
    }

    public static function getMasterModel () {
        return static::$masterModel;
    }

    public static function getMasterModelRelFieldName ()
    {
        return static::$masterModelRelFieldName;
    }

    public static function getParentModel ()
    {
        return static::$parentModel;
    }

    public static function getModalSelect () {
        return static::$modalSelect;
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
        $className = '\\'.static::className();
        if (array_key_exists($className, static::$childModel)) {
            return static::$childModel[$className];
        }

        $classPath = "@app/modules/".static::getModuleName()."/models";
        $classNameSpace = '\app\modules\\'.static::getModuleName().'\\models';
        $classPath = Yii::getAlias($classPath);
        if (file_exists($classPath)) {
            $files = scandir($classPath);
            foreach ($files as $file) {
                if (preg_match("/^[a-zA-Z0-9]+\\.php$/", $file)) {
                    $modelName = $classNameSpace.'\\'.str_replace(".php", "", $file);
                    if ($modelName != $className) {
                        if (is_callable([$modelName, 'getParentModel'])) {
                            $parentModel = call_user_func([$modelName, 'getParentModel']);
                            if ($parentModel == $className) {
                                static::$childModel[$className] = $modelName;
                                return $modelName;
                            }
                        }
                    }
                }
            }
        }
        static::$childModel[$className] = null;
        return null;
    }

    /**
     * Возвращает модели-детализации, для которых в качестве мастер-модели указана текущая (если таковые есть).
     * Возвращаются имена классов, включая пространства имен, в массиве.
     * Если модели-деталицации не найдены, возвращается false
     * @return array|bool
     */
    public static function getDetailModels() {
        $className = trim(static::className(), '\\');

        if (array_key_exists($className, static::$detailModels)) {
            return static::$detailModels[$className];
        }

        $result = [];
        $classPath = "@app/modules/".static::getModuleName()."/models";
        $classNameSpace = '\app\modules\\'.static::getModuleName().'\\models';
        $classPath = Yii::getAlias($classPath);

        if (file_exists($classPath)) {
            $files = scandir($classPath);
            foreach ($files as $file) {
                if (preg_match("/^[a-zA-Z0-9]+\\.php$/", $file)) {
                    $modelName = str_replace(".php", "", $file);
                    if ($modelName != $className) {
                        $modelName = $classNameSpace.'\\'.str_replace(".php", "", $file);
                        if (is_callable([$modelName, 'getMasterModel'])) {
                            $masterModel = call_user_func([$modelName, 'getMasterModel']);
                            if (trim($masterModel, '\\') == $className) {
                                $result[] = $modelName;
                            }
                        }
                    }
                }
            }
        }

        static::$detailModels[$className] = $result;
        return $result;
    }

    public static function setDetailModels ($detailModels)
    {
        static::$detailModels = $detailModels;
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
            return (isset(static::$structure[$fieldName]) ? static::$structure[$fieldName] : null);
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
        $structure = static::getStructure();
        foreach ($structure as $fieldName => $fieldConf) {
            if (isset($fieldConf['identify']) && $fieldConf['identify']) {
                $fieldConf['name'] = $fieldName;
                return $fieldConf;
            }
        }
        return null;
    }

    protected static function beforeList($params)
    {
        return $params;
    }

    protected static function afterList($list)
    {
        return $list;
    }

    protected static function getSimpleFilterCondition($type, $field, $comparison, $value, $expression = '')
    {
        $tableName = static::tableName();
        $tableName = str_replace('.', '_', $tableName);
        $res = [];

        if ($field == static::$masterModelRelFieldName) {
            $type = 'numeric';
        }

        $fieldConf = static::getStructure($field);
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
                $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $relatedTableName = $relatedTableClearName."_".$field;
                $relatedFieldName = $relatedIdentifyFieldConf['name'];
                $res = ['like', ($relatedTableName.".".$relatedFieldName), $value];
            }
        }
        return $res;
    }

    protected static function getFilterCondition($filter, $expression = '')
    {
        $condition = [];
        if (!isset($filter['value'])) {
            return ['=', 'id',-8];
        }
        if (!is_array($filter['value'])) {
            $filter['value'] = [$filter['value']];
        }

        foreach ($filter['value'] as $value) {
            $condition[] = static::getSimpleFilterCondition(
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
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($params)
    {
        $tableName = static::tableName();
        $tableName = str_replace('.', '_', $tableName);
        static::checkStructure();
        static::addAdditionFields();

        if (isset($params['query']) && $params['query']) {
            // Быстрый фильтр по identyfy полю
            $identifyFieldConf = static::getIdentifyFieldConf();
            $params['filter'] = ($params['filter'] ? $params['filter'] : []);
            $params['filter'] = array_merge($params['filter'], [
                [
                    'type' => 'string',
                    'value' => $params['query'],
                    'field' => $identifyFieldConf['name'],
                ],
            ]);
        }

        $params = static::beforeList($params);
        $select = ["`".$tableName."`.`id`"];
        $selectParams = [];
        $join = [];
        $pointers = [];
        $selectFields = [];
        $calcFields = [];
        $additionTables = [];
        $colorFields = [];

        $structure = static::getStructure();

        foreach ($structure as $fieldName => $fieldConf) {
            if (isset($params['identifyOnly']) && $params['identifyOnly']) {
                if ((isset($fieldConf['identify']) && !$fieldConf['identify']) || (!isset($fieldConf['identify']))) {
                    continue;
                }
            }

            if (!isset($fieldConf['calc'])) {
                $fieldConf['calc'] = false;
            }

            if (!isset($fieldConf['addition'])) {
                $fieldConf['addition'] = false;
            }

            if ($fieldConf['type'] == 'linked' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                $relatedIdentifyFieldConf = call_user_func([static::$linkModelName, 'getIdentifyFieldConf']);
                if ($relatedIdentifyFieldConf) {
                    $select[] = "`__link_model_table`.`id` AS `".$fieldName."`";
                    $select[] = "`__link_model_table`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                    $pointers[$fieldName] = [
                        "table" => '__link_model_table',
                        "field" => $relatedIdentifyFieldConf['name']
                    ];

                    if (strtolower(call_user_func([static::$linkModelName, 'tableName'])) == 's_files') {
                        $select[] = "`__link_model_table`.`name` as `fileof_".$fieldName."`";
                        $pointers[$fieldName]['file_field'] = 'name';
                    }
                }
            } elseif ($fieldConf['type'] == 'pointer' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                if (is_array($fieldConf['relativeModel'])) {
                    $relatedModelClass = '\app\modules\\'.$fieldConf['relativeModel']['moduleName'].'\models\\'.$fieldConf['relativeModel']['name'];
                } else {
                    $relatedModelClass = $fieldConf['relativeModel'];
                }
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                if ($relatedIdentifyFieldConf) {
                    $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                    $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                    $select[] = "`".$relatedTableClearName."_".$fieldName."`.`id` AS `".$fieldName."`";
                    if ($relatedIdentifyFieldConf['type'] == 'select') {
                        $options = [];
                        $keyIndex = 1;
                        foreach ($relatedIdentifyFieldConf['selectOptions'] as $key => $value) {
                            $options[] = "WHEN :option".$keyIndex."_".$fieldName."_key THEN :option".$keyIndex."_".$fieldName."_value";
                            $selectParams[":option".$keyIndex."_".$fieldName."_key"] = $key;
                            $selectParams[":option".$keyIndex."_".$fieldName."_value"] = $value;
                            $keyIndex++;
                        }
                        $select[] = "(CASE `".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` ".implode(' ', $options)." END) AS `valof_".$fieldName."`";
                    } else {
                        $select[] = "`".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                    }
                    $pointers[$fieldName] = [
                        "table" => $relatedTableClearName."_".$fieldName,
                        "field" => $relatedIdentifyFieldConf['name']
                    ];
                    $join[] = [
                        'name' => $relatedTableName." as ".$relatedTableClearName."_".$fieldName,
                        'on' => "`".$tableName."`.`".$fieldName."` = `".$relatedTableClearName."_".$fieldName."`.id"
                    ];
                }
            } elseif ($fieldConf['type'] == 'select' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                $select[] = "`".$tableName."`.`".$fieldName."`";
                $options = [];
                $keyIndex = 1;
                foreach ($fieldConf['selectOptions'] as $key => $value) {
                    $options[] = "WHEN :option".$keyIndex."_".$fieldName."_key THEN :option".$keyIndex."_".$fieldName."_value";
                    $selectParams[":option".$keyIndex."_".$fieldName."_key"] = $key;
                    $selectParams[":option".$keyIndex."_".$fieldName."_value"] = $value;
                    $keyIndex++;
                }
                $select[] = "(CASE `".$tableName."`.`".$fieldName."` ".implode(' ', $options)." END) AS `valof_".$fieldName."`";
                $selectFields[$fieldName] = [
                    "valField" => "valof_".$fieldName
                ];
            } elseif ($fieldConf['type'] == 'file' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                $relatedModelClass = '\app\modules\files\models\Files';
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $select[] = "`".$tableName."`.`".$fieldName."`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`name` as `fileof_".$fieldName."`";
                $pointers[$fieldName] = [
                    "table" => $relatedTableClearName."_".$fieldName,
                    "field" => $relatedIdentifyFieldConf['name'],
                    "file_field" => 'name'
                ];
                $join[] = [
                    'name' => $relatedTableName." as ".$relatedTableClearName."_".$fieldName,
                    'on' => "`".$tableName."`.`".$fieldName."` = `".$relatedTableClearName."_".$fieldName."`.id"
                ];
            } elseif ($fieldConf['type'] == 'file' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                $relatedModelClass = '\app\modules\files\models\Files';
                $relatedIdentifyFieldConf = call_user_func([$relatedModelClass, 'getIdentifyFieldConf']);
                $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $select[] = "(" . $fieldConf['expression'] . ")" . " AS `" . $fieldName . "`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`name` as `fileof_".$fieldName."`";
                $pointers[$fieldName] = [
                    "table" => $relatedTableClearName."_".$fieldName,
                    "field" => $relatedIdentifyFieldConf['name'],
                    "file_field" => 'name'
                ];
                $join[] = [
                    'name' => $relatedTableName." as ".$relatedTableClearName."_".$fieldName,
                    'on' => "(" . $fieldConf['expression'] . ")"." = `".$relatedTableClearName."_".$fieldName."`.id"
                ];
            } elseif ($fieldConf['type'] == 'color' && !$fieldConf['calc'] && !$fieldConf['addition']) {
                if ($fieldConf['colorFormat'] == 'dec') {
                    $colorFields[] = $fieldName;
                }
                $select[] = "`".$tableName."`.`".$fieldName."`";
            } elseif ($fieldConf['type'] != 'file' && $fieldConf['type'] != 'pointer') {
                // Простые типы данных
                if ($fieldConf['calc'] && isset($fieldConf['expression']) && $fieldConf['expression']) {
                    $select[] = "(" . $fieldConf['expression'] . ")" . " AS `" . $fieldName . "`";
                    $calcFields[$fieldName] = "(" . $fieldConf['expression'] . ")";
                } elseif (array_key_exists('addition', $fieldConf) && $fieldConf['addition']) {
                    if (!in_array($fieldConf['additionTable'], $additionTables)) {
                        $additionTables[] = $fieldConf['additionTable'];
                        $join[] = [
                            'name' => $fieldConf['additionTable'],
                            'on' => "`".$fieldConf['additionTable']."`.`master_table_id` = `".$tableName."`.id AND `".$fieldConf['additionTable']."`.`master_table_name` = '".$tableName."'"
                        ];
                    }
                    $select[] = "`".$fieldConf['additionTable']."`.`".$fieldName."`";
                } else {
                    $select[] = "`".$tableName."`.`".$fieldName."`";
                }
            }
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && static::$recursive) {
            $fieldName = 'parent_id';
            $relatedModelClass = static::className();
            $relatedIdentifyFieldConf = static::getIdentifyFieldConf();
            if ($relatedIdentifyFieldConf) {
                $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $select[] = "`".$tableName."`.`".$fieldName."`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $pointers[$fieldName] = [
                    "table" => $relatedTableClearName."_".$fieldName,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
                $join[] = [
                    'name' => $relatedTableName." as ".$relatedTableClearName."_".$fieldName,
                    'on' => "`".$tableName."`.`".$fieldName."` = `".$relatedTableClearName."_".$fieldName."`.id"
                ];
            }
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && static::$hiddable) {
            $fieldName = 'hidden';
            $select[] = "`".$tableName."`.`".$fieldName."`";
        }

        if (!(isset($params['identifyOnly']) && $params['identifyOnly']) && (static::$parentModel || static::$masterModel)) {
            $parentModelName = (static::$parentModel ? static::$parentModel : static::$masterModel);

            $fieldName = static::$masterModelRelFieldName;
            $relatedModelClass = (static::$parentModel ? static::$parentModel : static::$masterModel);
            $relatedIdentifyFieldConf = call_user_func([$parentModelName, 'getIdentifyFieldConf']);
            if ($relatedIdentifyFieldConf) {
                $relatedTableName = call_user_func([$relatedModelClass, 'tableName']);
                $relatedTableClearName = str_replace('.', '_', $relatedTableName);
                $select[] = "`".$tableName."`.`".$fieldName."`";
                $select[] = "`".$relatedTableClearName."_".$fieldName."`.`".$relatedIdentifyFieldConf['name']."` as `valof_".$fieldName."`";
                $pointers[$fieldName] = [
                    "table" => $relatedTableClearName."_".$fieldName,
                    "field" => $relatedIdentifyFieldConf['name']
                ];
                $join[] = [
                    'name' => $relatedTableName." as ".$relatedTableClearName."_".$fieldName,
                    'on' => "`".$tableName."`.`".$fieldName."` = `".$relatedTableClearName."_".$fieldName."`.id"
                ];
            }
        }

        $query = static::find();

        $filteredFields = [];

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                $filteredFields[] = $filter['field'];
                if (isset($calcFields[$filter['field']])) {
                    $query->andWhere(static::getFilterCondition($filter, $calcFields[$filter['field']]));
                } else {
                    $query->andWhere(static::getFilterCondition($filter));
                }
            }
        }

        if(static::$masterModelRelationsType == static::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY && static::$slaveModelAddMethod == static::SLAVE_MODEL_ADD_METHOD_CHECK) {

            //Вызываем рак мозга у запроса - нам надо в секцию FROM запроса затолкать таблицу, которую мы подключаем, а основную сджоинить
            $relatedTableName = call_user_func([static::$linkModelName, 'tableName']);

            $query->from($relatedTableName." as __link_model_table");
            $select[] = "IF((`".$tableName."`.`id` IS NOT NULL AND `".
                $tableName."`.`".static::$masterModelRelFieldName."` = ".$params['masterId']."), 1, 0) AS `check`";

            $query->leftJoin("`".$tableName."`", "`__link_model_table`.`id` = `".$tableName."`.`".static::getLinkTableIdField()."` AND `".$tableName."`.`".static::$masterModelRelFieldName."` = ".$params['masterId']);
            if (!call_user_func([static::$linkModelName, 'getPermanentlyDelete'])) {
                $query->andWhere("`__link_model_table`.del = 0");
            }
        } elseif (static::$masterModelRelationsType == static::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY && static::$slaveModelAddMethod == static::SLAVE_MODEL_ADD_METHOD_BUTTON) {
            $relatedTableName = call_user_func([static::$linkModelName, 'tableName']);
            $query->leftJoin("`".$relatedTableName."` as __link_model_table", "`".$tableName."`.`".static::getLinkTableIdField()."` = `__link_model_table`.`id`");
            $query->andWhere("`".$tableName."`.`".static::$masterModelRelFieldName."` = ".$params['masterId']);
            if (!call_user_func([static::$linkModelName, 'getPermanentlyDelete'])) {
                $query->andWhere("`__link_model_table`.del = 0");
            }
        } else {

            if (isset($params['masterId']) && $params['masterId'] && (static::$masterModel || static::$parentModel)) {
                $query->andWhere('`'.$tableName.'`.'.static::$masterModelRelFieldName.' = ' . intval($params['masterId']));
            }

            $tbName = static::tableName();
            if (strpos($tbName, '.') !== false) {
                $tbName = explode('.', $tbName);
                $tbName = "`{$tbName[0]}`.`{$tbName[1]}`";
            } else {
                $tbName = "`{$tbName}`";
            }
            $query->from("{$tbName} as `{$tableName}`");
        }

        foreach ($join as $item) {
            $query->leftJoin($item['name'], $item['on']);
        }

        if (isset($params['where'])) {
            $query->andWhere($params['where']);
        }

        if (static::$recursive && array_key_exists('parentId', $params)) {
            $query->andWhere(['parent_id' => $params['parentId']]);
        }

        // Начало говнокода, который надо будет извести
        $totalCount = false;
        if (!static::$sortable) {
            $tmpQuery = clone $query;
            $totalCount = intval($tmpQuery->count());
        }
        // Конец говнокода, который надо будет извести

        $query->select($select);
        if ($selectParams) {
            $query->addParams($selectParams);
        }

        $orderBy = [];
        if (isset($params['sort']) && $params['sort']) {
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
                        $orderBy["`".$tableName."`.`".$sort['property']."`"] = $dir;
                    }
                }
            }
        }

        if(static::$masterModelRelationsType == static::MASTER_MODEL_RELATIONS_TYPE_MANY_TO_MANY && static::$slaveModelAddMethod == static::SLAVE_MODEL_ADD_METHOD_CHECK) {
            $orderBy = array_merge($orderBy, ["`__link_model_table`.id" => SORT_ASC]);
        }

        if (static::$sortable) {
            $orderBy = array_merge($orderBy, ["`".$tableName."`.`sort_priority`" => SORT_ASC]);
        }

        if (static::$defaultSort && !static::$sortable) {
            $orderBy = array_merge($orderBy, static::$defaultSort);
        }

        $query->orderBy(($orderBy ? $orderBy : null));

        if (!static::$sortable) {
            if (isset($params['limit']) && $params['limit']) {
                $query->limit($params['limit']);
            }
            if (isset($params['start']) && $params['limit']) {
                $query->offset($params['start']);
            }
        }

//        if (!static::$sortable) {
//            $query->selectOption = 'SQL_CALC_FOUND_ROWS';
//        }

        $list = $query->asArray()->all();


//        $totalCount = false;
//        if (!static::$sortable) {
//            $command = Yii::$app->db->createCommand('SELECT FOUND_ROWS() as rowCount');
//            $totalCount = intval($command->queryScalar());
//        }

        if ($pointers) {
            foreach ($list as $key => $item) {
                foreach ($pointers as $fieldName => $some) {
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
        if ($colorFields) {
            foreach ($list as $key => $item) {
                if ($list[$key][$fieldName]) {
                    foreach ($colorFields as $fieldName) {
                        $some = explode(',', $list[$key][$fieldName]);
                        $list[$key][$fieldName] = "#".str_pad(dechex(intval($some[0])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[1])), 2, STR_PAD_LEFT).str_pad(dechex(intval($some[2])), 2, STR_PAD_LEFT);
                    }
                }
            }
        }

        if ($selectFields) {
            foreach ($list as $key => $item) {
                foreach ($selectFields as $fieldName => $some) {
                    $list[$key][$fieldName] = Json::encode([
                        'id' => $item[$fieldName],
                        'value' => $item['valof_'.$fieldName]
                    ]);
                }
            }
        }

        $dataKey = (isset($params['dataKey']) ? $params['dataKey'] : 'data');
        $res = [$dataKey => static::afterList($list)];

        if (static::$recursive && isset($params['all']) && $params['all']) {
            // Получаем все дерево
            // todo me: Надо бы это как-то оптимизировать
            foreach ($res[$dataKey] as $i => $data) {
                $children = static::getList(array_merge($params, [
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

    /**
     * Приводит значение value к типу поля fieldName
     * @param $fieldName
     * @param $value
     * @return int|null|string
     */
    protected static function setType ($fieldName, $value, $type = false)
    {
        $structure = static::getStructure();
        $type = (!$type ? $structure[$fieldName]['type'] : $type);

        if ($type == 'int') {
            return intval($value);
        } elseif ($type == 'float') {
            return floatval($value);
        } elseif ($type == 'string' || $type == 'tinystring' || $type == 'text' || $type == 'html') {
            return strval($value);
        } elseif ($type == 'color') {
            if ($structure[$fieldName]['colorFormat'] == 'dec') {
                $val = str_replace("#", "", $value);
                $val = str_split($val, 2);
                foreach ($val as $i => $v) {
                    $val[$i] = hexdec($v);
                }
                $value = implode(',', $val);
            }
            return $value;
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
            $value = strval($value['id']);
            if (isset($structure[$fieldName]['selectOptions']) && isset($structure[$fieldName]['selectOptions'][$value])) {
                return $value;
            } else {
                return null;
            }
        } elseif ($type == 'bool') {
            return $value ? 1 : 0;
        } elseif ($type == 'pointer' || $type == 'linked' || $type == 'img' || $type == 'file') {
            if(isset($value['id'])) {
                return $value['id'];
            } else {
                $value = intval($value);
                return ($value ? $value : null);
            }
        }

        return null;
    }

    /**
     * "Накладывает" массив данных или JSON строку на модель
     * @param $data
     */
    public function mapJson(&$data)
    {
        if (is_string($data)) {
            $data = Json::decode($data);
        }

        // Ищем поля типа file и проверяем есть ли загрузка файла
        $structure = static::getStructure();
        foreach ($structure as $key => $field) {
            if (!array_key_exists($key, $data) && isset($_FILES[$key."_filebin"])) {
                $file = \app\modules\files\models\FilesUtils::uploadFile(0, '' , 1, $key."_filebin");
                if ($file['success']) {
                    $data[$key] = ['id' => $file['data']['id']];
                }
            }
        }

        if ($data) {
            foreach ($data as $key => $val) {
                if (isset($structure[$key]) && $structure[$key]['type'] != 'linked' &&
                    $structure[$key]['type'] != 'file' && $structure[$key]['type'] != 'bool' &&
                    (!isset($structure[$key]['calc']) || !$structure[$key]['calc']) &&
                    !$val && isset($structure[$key]['default'])
                ) {
                    if (is_array($structure[$key]['default'])) {
                        if (isset($structure[$key]['default']['expression'])) {
                            $this->$key = new db\Expression($structure[$key]['default']['expression']);
                            continue;
                        }
                    } else {
                        $this->$key = $structure[$key]['default'];
                        continue;
                    }
                }

                if (isset($structure[$key]) && $structure[$key]['type'] == 'linked') {
                    if (!isset($structure[$key]['calc']) || !$structure[$key]['calc']) {
                        $this->{static::getLinkTableIdField()} = static::setType($key, $val);
                    }
                } elseif (isset($structure[$key])) {
                    if (!isset($structure[$key]['calc']) || !$structure[$key]['calc']) {
                        $this->$key = static::setType($key, $val);
                    }
                } elseif ($key == 'hidden' && static::$hiddable) {
                    $this->$key = static::setType($key, $val, 'bool');
                } elseif ($key == 'parent_id' && static::$recursive) {
                    $this->$key = static::setType($key, $val, 'pointer');
                } elseif ($key == static::$masterModelRelFieldName && static::$parentModel) {
                    $this->$key = static::setType($key, $val, 'pointer');
                }
            }
        }
    }

    private function saveToHistory($event, $id)
    {
        $history = new SDataHistoryEvents();
        $history->time = date('Y-m-d H:i:s');
        $history->user_id = Yii::$app->user->id;
        $history->event = $event;
        $history->model = static::className();
        $history->record_id = $id;
        $history->save();
        $eventId = $history->id;

        if ($event != 'delete') {
            $structure = static::getStructure();
            foreach ($structure as $name => $fieldConf) {
                if (isset($fieldConf['keepHistory']) && $fieldConf['keepHistory'] && array_key_exists($name, $this->oldDirtyAttributes)) {
                    $dirtyFieldOldValue = $this->oldDirtyAttributes[$name];
                    $historyData = new SDataHistory();
                    $historyData->event_id = $eventId;
                    $historyData->field = $name;
                    $historyData->value = $dirtyFieldOldValue;
                    $historyData->save();
                }
            }
        }
    }

    /**
     * Сохраняет данные переданные в массиве $data
     * @param array $data
     * @param bool $add
     * @param int $masterId
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function saveData($data, $add = false, $masterId = 0)
    {
        static::addAdditionFields();
        if (static::$masterModel) {
            $this->{static::$masterModelRelFieldName} = $masterId;
        }

        $this->mapJson($data);

        if (static::$sortable && $add) {
            $maxSortPriority = call_user_func([static::className(), 'find'])->max('sort_priority');
            $this->sort_priority = $maxSortPriority + 1;
        }

        $this->oldDirtyAttributes = $this->dirtyAttributes;
        if ($this->save()) {

            $structure = static::getStructure();
            foreach ($structure as $fieldName => $fieldData) {
                if ($fieldData['type'] == 'img' || $fieldData['type'] == 'file') {
                    $file = Files::findOne(['id' => $data[$fieldName]]);
                    if ($file) {
                        $file->tmp = 0;
                        $file->save(false);
                    }
                }
            }

            if ($add) {
                $result = static::getList([
                    "limit" => 1,
                    "sort" => [
                        [
                            "property" => 'id',
                            "direction" => 'desc'
                        ]
                    ],
                    "masterId" => $masterId
                ]);

                if ($result && is_array($result)) {
                    $this->saveToHistory("create", $result['data'][0]['id']);
                    return $result['data'][0];
                } else {
                    return false;
                }
            } else {
                $result = static::getList([
                    "where" => "`".static::tableName()."`.id = '{$data['id']}'",
                    "masterId" => $masterId
                ]);

                if ($result && is_array($result)) {
                    $this->saveToHistory("update", $result['data'][0]['id']);
                    return $result['data'][0];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Обработка события - удаление группы записей, вызывается в методе deleteRecords непосредственно перед удалением
     *
     * @param string|array $condition
     * @param array $params
     * @return array
     */
    protected static function beforeDeleteRecords($condition = '', $params = [])
    {
        return [$condition, $params];
    }

    /**
     * Удаление записей удовлетворяющих условию $condition
     * По сути аналог deleteAll, но учитывается флаг permanentlyDelete в свойствах модели и в $condition можно передать
     * массив условий
     *
     * @param string|array $condition
     * @param array $params
     * @return bool
     */
    public static function deleteRecords($condition = '', $params = [])
    {
        if ($condition) {
            if (is_array($condition)) {
                $condition = implode(' OR ', $condition);
            }
            list($condition, $params) = static::beforeDeleteRecords($condition, $params);

            $records = static::find()->where($condition, $params)->all();
            foreach ($records as $record) {
                $record->saveToHistory("delete", $record->id);
            }


            if (static::$permanentlyDelete) {
                static::deleteAll($condition, $params);
            } else {
                static::updateAll(['del' => 1], $condition, $params);
            }
        }
        return true;
    }

    public static function beforeReturnUserInterface($config) {
        return $config;
    }

    protected static function addAdditionFields()
    {
        if (!static::$processedAdditionFieldsBehaviors) {
            if (static::$behaviorsList) {
                foreach (static::$behaviorsList as $item) {
                    $className = '';
                    if (is_string($item)) {
                        $className = $item;
                    } elseif (is_array($item) && isset($item['class'])) {
                        $className = $item['class'];
                    }

                    if ($className && is_callable([$className, 'getAdditionFields'])) {
                        $addFields = call_user_func([$className, 'getAdditionFields']);
                        foreach ($addFields as $key=>$field) {
                            $addFields[$key]['addition'] = true;
                        }
                        static::$structure = array_merge(static::getStructure(), $addFields);
                    }
                }
            }
        }
    }

    /**
     * Возвращает настройки пользовательского интерфейса.
     *
     * Если $configOnly == true, то возвращается только массив настроек,
     *      если false, то возвращается полностью javascript редактора.
     *
     * В $masterId указывается ID записи предка
     *
     * Если $modal == true, то возвращается конфиг модульного окна.
     *
     * В $params передаются произвольные параметры, которые могут прийти в POST параметрах запроса.
     *
     * @param bool $configOnly
     * @param int $masterId
     * @param bool $modal
     * @param array $params
     * @return string
     */
    public static function getUserInterface($configOnly = false, $masterId = 0, $modal = false, $params = [])
    {
        static::checkStructure();
        static::addAdditionFields();
        $modelStructure = static::getStructure();
        $fields = [];
        foreach ($modelStructure as $fieldName => $config) {
            $linkedSubType = '';
            $relativeModel = [];
            if ($config['type'] == 'pointer') {
                // Для полей типа pointer получаем конфигурацию связанной модели

                if (is_array($config['relativeModel'])) {
                    $relativeModel = $config['relativeModel'];
                    if (!isset($relativeModel['classname'])) {
                        if (!isset($relativeModel['moduleName']) || !isset($relativeModel['name'])) {
                            return false;
                        } else {
                            $relativeModel['classname'] = '\app\modules\\'.$relativeModel['moduleName'].'\models\\'.$relativeModel['name'];
                        }
                    }
                } else {
                    $relativeModel['classname'] = $config['relativeModel'];
                }

                if (!isset($relativeModel['moduleName']) || !isset($relativeModel['name'])) {
                    $relativeModelFullName = $relativeModel['classname'];
                    $relativeModelPath = str_replace('\app\modules\\', '', str_replace('\models', '', $relativeModelFullName));
                    $relativeModelPath = explode('\\', $relativeModelPath);
                }

                if (!isset($relativeModel['moduleName'])) {
                    $relativeModel['moduleName'] = $relativeModelPath[0];
                }

                if (!isset($relativeModel['name'])) {
                    $relativeModel['name'] = $relativeModelPath[1];
                }

                $relativeModelIdentifyFieldConf = call_user_func([$relativeModel['classname'], 'getIdentifyFieldConf']);

                $relativeModel['identifyFieldName'] = $relativeModelIdentifyFieldConf['name'];
                $relativeModel['identifyFieldType'] = $relativeModelIdentifyFieldConf['type'];

                if (!isset($relativeModel['runAction'])) {
                    $relativeModel['runAction'] = [
                        $relativeModel['moduleName'],
                        'main',
                        'get-interface'
                    ];
                }

                if (!isset($relativeModel['modalSelect'])) {
                    $relativeModel['modalSelect'] = call_user_func([$relativeModel['classname'], 'getModalSelect']);
                }
            } elseif ($config['type'] == 'linked') {
                // Для полей типа pointer получаем конфигурацию связанной модели

                $relativeModel['classname'] = static::$linkModelName;
                $relativeTableName = call_user_func([static::$linkModelName, 'tableName']);

                if ($relativeTableName == 's_files') {
                    $linkedSubType = 'file';
                } else {
                    $linkedSubType = 'pointer';
                }

                if (!isset($relativeModel['moduleName']) || !isset($relativeModel['name'])) {
                    $relativeModelFullName = $relativeModel['classname'];
                    $relativeModelPath = str_replace('\app\modules\\', '', str_replace('\models', '', $relativeModelFullName));
                    $relativeModelPath = explode('\\', $relativeModelPath);
                }

                if (!isset($relativeModel['moduleName'])) {
                    $relativeModel['moduleName'] = $relativeModelPath[0];
                }

                if (!isset($relativeModel['name'])) {
                    $relativeModel['name'] = $relativeModelPath[1];
                }

                $relativeModelIdentifyFieldConf = call_user_func([$relativeModel['classname'], 'getIdentifyFieldConf']);

                $relativeModel['identifyFieldName'] = $relativeModelIdentifyFieldConf['name'];
                $relativeModel['identifyFieldType'] = $relativeModelIdentifyFieldConf['type'];

                if (!isset($relativeModel['runAction'])) {
                    $relativeModel['runAction'] = [
                        $relativeModel['moduleName'],
                        'main',
                        'get-interface'
                    ];
                }

                if (!isset($relativeModel['modalSelect'])) {
                    $relativeModel['modalSelect'] = call_user_func([$relativeModel['classname'], 'getModalSelect']);
                }
            } elseif ($config['type'] == 'img' || $config['type'] == 'file') {
                $relativeModel['classname'] = '\app\modules\files\models\Files';
                $relativeModel['moduleName'] = 'files';
                $relativeModel['name'] = 'Files';
                $relativeModelIdentifyFieldConf = call_user_func([$relativeModel['classname'], 'getIdentifyFieldConf']);
                $relativeModel['identifyFieldName'] = $relativeModelIdentifyFieldConf['name'];
                $relativeModel['identifyFieldType'] = $relativeModelIdentifyFieldConf['type'];
                $relativeModel['runAction'] = [
                    $relativeModel['moduleName'],
                    'main',
                    'get-interface'
                ];
                $relativeModel['modalSelect'] = call_user_func([$relativeModel['classname'], 'getModalSelect']);
            }

            $config['linkedSubType'] = $linkedSubType;

            $i = count($fields);
            $fields[$i] = array_merge([
                'name' => $fieldName
            ], $config);

            if ($relativeModel) {
                // есть связанная модель, добавляем ее конфигурацию в конфигурацию поля
                $fields[$i]['relativeModel'] = [
                    'moduleName' => $relativeModel['moduleName'],
                    'name' => $relativeModel['name'],
                    'identifyFieldName' => $relativeModel['identifyFieldName'],
                    'identifyFieldType' => $relativeModel['identifyFieldType'],
                    'modalSelect' => $relativeModel['modalSelect'],
                    'runAction' => $relativeModel['runAction']
                ];
            }
        }

        $getDataAction = [static::getModuleName(), 'main', 'list'];
        $linkModelRunAction = null;
        if (static::$linkModelName) {
            $linkModelRunAction = call_user_func([static::$linkModelName, 'getRunAction']);
        }

        $userRights = 0;

        $modelName = static::getModelName();

        if (static::$readonly) {
            $userRights = 1;
        } elseif (Yii::$app->user->can('backend-delete-record', ['modelName' => static::className()])) {
            $userRights = 3;
        } elseif (Yii::$app->user->can('backend-save-record', ['modelName' => static::className()])) {
            $userRights = 2;
        } elseif (Yii::$app->user->can('backend-read', ['modelName' => static::className()])) {
            $userRights = 1;
        }

        $linkModelName = '';
        if (static::$linkModelName) {
            $linkModelName = call_user_func([static::$linkModelName, 'getModelName']);
        }

        $childModel = static::getChildModel();
        $childModelConfig = null;

        if ($childModel) {
            $childModelConfig = call_user_func([$childModel, 'getUserInterface'], true);
            $childModelConfig['modelName'] = str_replace('app\modules\\'.static::getModuleName().'\models\\', '', trim($childModel, '\\'));
        }

        $parentModelName = static::getParentModel();
        if ($parentModelName) {
            $parentModelName = str_replace('app\modules\\'.static::getModuleName().'\models\\', '', trim($parentModelName, '\\'));
        }

        if (static::$recursive) {
            $fields[] = [
                'name' => 'parent_id',
                'type' => 'pointer',
                'extra' => true
            ];
        } elseif ($parentModelName || static::$masterModel) {
            $fields[] = [
                'name' => static::$masterModelRelFieldName,
                'type' => 'pointer',
                'extra' => true
            ];
        }

        if (static::$hiddable) {
            $fields[] = [
                'name' => 'hidden',
                'type' => 'bool',
                'extra' => true
            ];
        }


        $conf = [
            'fields' => $fields,
            'getDataAction' => $getDataAction,
            'linkModelRunAction' => $linkModelRunAction,
            'linkModelName' => $linkModelName,
            'modelName' => $modelName,
            'userRights' => $userRights,
            'createInterfaceForExistingParentOnly' => static::$createInterfaceForExistingParentOnly,
            'title' => static::getModelTitle(),
            'recordTitle' => static::$recordTitle,
            'accusativeRecordTitle' => static::$accusativeRecordTitle,
            'params' => $params,
            'masterRecordId' => $masterId,
            'sortable' => static::$sortable && !static::$readonly,
            'recursive' => static::$recursive,
            'singleRoot' => static::$singleRoot,
            'hiddable' => static::$hiddable && !static::$readonly,
            'masterModelRelationsType' => static::$masterModelRelationsType,
            'slaveModelAddMethod' => static::$slaveModelAddMethod,
            'childModelConfig' => $childModelConfig,
            'parentModelName' => $parentModelName,
            'masterModelRelFieldName' => static::$masterModelRelFieldName,
            'historyAccess' => Yii::$app->user->getIdentity(true)->isSU,
        ];

        if ($configOnly) {
            $conf['showCondition'] = (static::$showCondition ? static::$showCondition : null);
        }

        if (!$modal) {
            $tabs = [];
            $detailModels = static::getDetailModels();
            if ($detailModels) {
                foreach ($detailModels as $item) {
                    // Проверяем, есть ли файл конфигурации модели-детализации

                    $tabConfig = call_user_func([$item, 'getUserInterface'], true);
                    $tabConfig['modelName'] = str_replace('app\modules\\'.static::getModuleName().'\models\\', '', trim($item, '\\'));

                    $fileName = '@app/modules/'.static::getModuleName().'/js/'.static::getModelName().'/tabs/'.$tabConfig['modelName'].'.js';
                    if (file_exists(Yii::getAlias($fileName))) {
                        $tabConfig['className'] = 'App.modules.'.static::getModuleName().'.'.static::getModelName().'.tabs.'.$tabConfig['modelName'];
                    }

                    // Получаем конфиг модели-детализации
                    $tabs[] = $tabConfig;
                }
            }

            if ($tabs) {
                $conf['tabs'] = $tabs;
            }

            if ($configOnly) {
                return static::beforeReturnUserInterface($conf);
            }
        }

        $conf = static::beforeReturnUserInterface($conf);

        if ($modal) {
            $fileName = '@app/modules/'.static::getModuleName().'/js/'.static::getModelName().'/ModalSelectWindow.js';
            if (file_exists(Yii::getAlias($fileName))) {
                return ("
                  var module = Ext.create('App.modules.".static::getModuleName().".".static::getModelName().".ModalSelectWindow', ". Json::encode($conf).");
                ");
            }
            return ("
                  var module = Ext.create('App.core.GridModalSelectWindow', ". Json::encode($conf).");
                ");
        }

        // Автоматически выбираем тип редактора
        $editor = "SingleModelEditor";
        $recursive = static::$recursive;

        if (static::$singleRecord) {
            $editor = 'SingleRecordEditor';
            $data = static::getList([
                'limit' => 1
            ])['data'];
            $data = ($data ? $data[0] : null);
            $conf['data'] = $data;
        } elseif ($recursive && !$childModel) {
            $editor = 'SimpleEditor';
            $data = null;
            if (array_key_exists('recordId', $params) && $params['recordId']) {
                $data = static::getList([
                    'where' => ["`".static::tableName()."`.id" => $params['recordId']]
                ])['data'];
                $data = ($data ? $data[0] : null);
            }
            $conf['data'] = $data;
        } elseif ($childModel) {
            $editor = 'RelatedModelsEditor';
            $data = null;
            if (array_key_exists('recordId', $params) && $params['recordId']) {
                $data = static::getList([
                    'where' => ["`".static::tableName()."`.id" => $params['recordId']]
                ])['data'];
                $data = ($data ? $data[0] : null);
            }
            $conf['data'] = $data;
        }

        $fileName = '@app/modules/'.static::getModuleName().'/js/'.static::getModelName().'/Editor.js';
        if (file_exists(Yii::getAlias($fileName))) {
            return ("
              var module = Ext.create('App.modules.".static::getModuleName().".".static::getModelName().".Editor', ". Json::encode($conf).");
            ");
        }

        return ("
          var module = Ext.create('App.core.".$editor."', ". Json::encode($conf).");
        ");
    }
}