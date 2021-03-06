<?php
/**
 * Базовый класс контроллера панели управления.
 */

namespace app\base\web;
use app\base\components\PrintForm;
use app\base\db\ActiveRecord;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;


class BackendController extends Controller
{
    public $layout = false;
    protected $currentInterfaceType = 'manage';
    protected $defaultAccessList = [
        'list' => 'backend-read',
        'print-item' => 'backend-read',
        'print-list' => 'backend-read',
        'save-record' => 'backend-save-record',
        'sort-records' => 'backend-save-record',
        'delete-record' => 'backend-delete-record',
        'cp-menu' => 'backend-cp-menu',
        'get-interface' => 'backend-read',
        'get-js-file' => 'backend-read',
        'get-custom-interface-file' => 'backend-read'
    ];

    protected $accessList = [
    ];

    public function init()
    {
        Yii::$app->setComponents([
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'app\models\UserIdentity',
                'identityCookie' => [
                    'name' => 'backendIdentity',
                    'path'=>'/admin'
                ],
                'enableAutoLogin' => true,
            ],
            'session' => [
                'class' => 'yii\web\Session',
                'name' => '_backendSessionId',
                // 'savePath' => __DIR__ . '/../runtime', // a temporary folder on backend
            ],
        ]);
        $this->enableCsrfValidation = false;
//        Yii::$app->view->setActiveTheme('backend');
//        Yii::$app->mailer->themeName = Yii::$app->view->themeName;

        Yii::$app->mailer->viewPath = '@app/modules/backend/views/mail/views';
        Yii::$app->mailer->htmlLayout = '@app/modules/backend/views/mail/layouts/html';

        // Устанавливаем текущий интерфейс в куки
        $this->currentInterfaceType = $this->getCurrentInterfaceType();
        Yii::$app->params['backendCurrentInterfaceType'] = $this->currentInterfaceType;
        parent::init();
    }

    /**
     * Проверка прав доступа пользователя
     * @param string $action ID действия
     * @return bool
     */
    public function checkAccess($action) {
        $this->accessList = array_merge($this->defaultAccessList, $this->accessList);

        if (!isset($this->accessList[$action])) {
            return false;
        }

        if (($this->accessList[$action] == 'GRAND') && (Yii::$app->user->can('admin') || Yii::$app->user->can('manager'))) {
            return true;
        }

        $accessRule = $this->accessList[$action];
        $params = [];

        if (is_array($accessRule)) {
            if (isset($accessRule['params'])) {
                $params = $accessRule['params'];
            }
            if (isset($accessRule['function']) && is_callable([$this, $accessRule['function']])) {
                return call_user_func([$this, $accessRule['function']], $action);
            } elseif (isset($accessRule['rule'])) {
                $accessRule = $accessRule['rule'];
            }
        }

        $modelName = Yii::$app->request->get('modelName', '');
        if ($modelName && !preg_match("/^[a-zA-Z0-9_]+$/", $modelName)) {
            static::ajaxError('app\base\web\BackendController\checkAccess',
                "Неверно указана модель! Передайте это в программистам, поддерживающим сайт, они знают что с этим делать.");
        }

        if ($modelName && !isset($params['modelName'])) {
            $params['modelName'] = $modelName;
        }

        $params['modelName'] = (isset($params['modelName']) ? '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$params['modelName'] : '');

        $masterId = intval(Yii::$app->request->get('masterId', 0));

        if ($masterId && !isset($params['masterId'])) {
            $params['masterId'] = $masterId;
        }

        $recordId = 0;
        $data = Yii::$app->request->post('data', []);
        if (!isset($data['id'])) {
            $recordId = ['id' => 0];
        }

        if ($recordId && !isset($params['recordId'])) {
            $params['recordId'] = $recordId;
        }

        if (!array_key_exists('strict', $params)) {
            $params['strict'] = true;
        }

        return Yii::$app->user->can($accessRule, $params);
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (Yii::$app->user->identity->isSU) {
                return true;
            } else {
                if (!$this->checkAccess($action->id)) {
                    if (Yii::$app->request->isAjax) {
                        $this->ajaxError('app\base\web\BackendController\beforeAction\cpAccessDeny?action='.$action->id, 'У Вас не хватает прав для выполнения операции');
                    } else {
                        throw new \yii\web\ForbiddenHttpException('Access denied');
                    }
                }
                return true;
            }
        } else {
            return false;
        }
    }

    protected function getCurrentInterfaceType()
    {
        $interface = Yii::$app->request->get('interface', '');

        if ($interface) {
            $interface = ($interface == 'settings' ? 'settings' : 'manage');
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'interface',
                'value' => $interface,
            ]));
        } elseif (Yii::$app->request->cookies->has('interface')) {
            $interface = Yii::$app->request->cookies->getValue('interface', 'manage');
            $interface = ($interface == 'settings' ? 'settings' : 'manage');
        } else {
            $interface = 'manage';
        }

        return $interface;
    }

    protected function getCPMenuData($recursive, $shortModelName, $modelName, $parentId, $identifyFieldName)
    {
        $list = call_user_func([$modelName, 'getList'], [
            'identifyOnly' => true,
            'parentId' => $parentId,
            "dataKey" => 'list'
        ])['list'];

        $res = [];

        foreach ($list as $item) {
            $node = [
                "modelName" => $shortModelName,
                "moduleName" => (is_string($this->module) ? $this->module : $this->module->id),
                "leaf" => true,
                "title" => $item[$identifyFieldName],
                "recordId" => $item['id'],
                "runAction" => [(is_string($this->module) ? $this->module : $this->module->id), "main", "get-interface"],
                "sortAction" => [(is_string($this->module) ? $this->module : $this->module->id), "main", "sort-records"],
                "sortable" => call_user_func([$modelName, 'isSortable'])
            ];

            if ($recursive) {
                // Есть ли дочерние элементы
                $query = call_user_func([$modelName, 'find']);
                $haveChildren = $query->where(['parent_id' => $item['id']])->select(['id'])->limit(1)->exists();
                $node['leaf'] = false;//!$haveChildren;
                $node['getSubTreeAction'] = [(is_string($this->module) ? $this->module : $this->module->id), "main", "cp-menu"];
                $node['list'] = $this->getCPMenuData($recursive, $shortModelName, $modelName, $item['id'], $identifyFieldName);
            }
            $res[] = $node;
        }

        return $res;
    }

    function actionCpMenu($modelName = null, $recordId = null)
    {
        if ($modelName === null) {
            $modelName = Yii::$app->request->get('modelName', '');
        }
        if ($recordId === null) {
            $recordId = intval(Yii::$app->request->get('id', 0));
        }
        if (!$recordId) {
            $recordId = null;
        }

        if (!preg_match("/^[a-zA-Z0-9_]+$/", $modelName)) {
            static::ajaxError('app\base\web\BackendController\actionCPMenu',
                "Неверно указана модель! Передайте это в программистам, поддерживающим сайт, они знают что с этим делать.");
        }

        // Получаем данные о модули и выбираем как выводить ее в меню:
        //  * Если модель рекурсивная, то выводяться ее записи и для каждой проверяется если у нее подчиненные, если есть,
        //      то узлу добаляется атрибут "getSubTreeAction"
        //  * Если модель просто имеет подчиненную модель, то выводим записи текущей модели, и для каждой записи указывам
        //      толдько атрибут "runAction", а "getSubTreeAction" не требуется.

        $shortModelName = $modelName;
        $modelName = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$modelName;

        $identifyFieldName = call_user_func([$modelName, 'getIdentifyFieldConf'])['name'];
        $recursive = call_user_func([$modelName, 'getRecursive']);
        $res = $this->getCPMenuData($recursive, $shortModelName, $modelName, $recordId, $identifyFieldName);
        return ['list' => $res];
    }

    /**
     * Завершение приложения с ошибкой.
     * В аргумет type передается тип ошибки, который состоит из полного имени класса (включая пространство имен), в котором она произошла,
     * имя метода, название ошибки на усмотрение разработчика. Например:
     *
     * тип - "app\base\web\BackendController\beforeAction\settingsAccessDeny"
     * Говорит о том что произошла ошибка, которую разрботчик назвал settingsAccessDeny, в методе beforeAction, класса
     * app\base\web\BackendController
     *
     * @param $type
     * @param string $message
     * @throws \yii\base\ExitException
     */
    public function ajaxError($type, $message = "")
    {
        $code = dechex(time()*intval(rand(1, 30)));

        Yii::error('Error #'.$code.": ".$type);
        if (Yii::$app->response->format == \yii\web\Response::FORMAT_JSON || Yii::$app->response->format == \yii\web\Response::FORMAT_RAW) {
            header('content-type: application/json; charset=UTF-8');
            echo Json::encode([
                'success' => false,
                'error' => $code,
                'message' => $message
            ]);
        } elseif (Yii::$app->response->format == \yii\web\Response::FORMAT_HTML) {
            echo "<script>IndexNextApp.getApplication().showErrorMessage('$code', '$message')</script>";
        } elseif (Yii::$app->response->format == 'js') {
            echo "IndexNextApp.getApplication().showErrorMessage('$code', '$message');";
        } elseif (Yii::$app->response->format == \yii\web\Response::FORMAT_HTML) {
            Yii::$app->response->statusCode = 500;
        }
        Yii::$app->end();
    }

    protected function getDataFile($fileName)
    {
        $moduleName = (is_string($this->module) ? $this->module : $this->module->id);
        return \app\helpers\Utils::getDataFile($moduleName, $fileName);
    }

    /**
     * Действие, возвращает интерфейс редактора.
     * в аргументе $masterId передается id родительской записи для детализаций, этот id так же может быть пеередан в
     * get запросе.
     * @param int $masterId
     * @return string
     */
    public function actionGetInterface($masterId = 0) {
        $modelName = Yii::$app->request->get('modelName', '');
        if (!$modelName) {
            $this->ajaxError('\app\base\web\BackendController\actionGetInterface?modelName='.$modelName, 'Справочник не найден.');
        }

        $moduleName = (is_string($this->module) ? $this->module : $this->module->id);

        $masterId = ($masterId ? $masterId : intval(Yii::$app->request->get('masterRecordId', 0)));
        $recordId = intval(Yii::$app->request->get('id', 0));
        $modal = intval(Yii::$app->request->get('modal', 0));
        $configOnly = intval(Yii::$app->request->get('configOnly', 0));

        $params = Json::decode(Yii::$app->request->post("params", '[]'));
        $params['recordId'] = $recordId;

        return call_user_func(['\app\modules\\'.$moduleName.'\models\\'.$modelName, 'getUserInterface'], $configOnly, $masterId, $modal, $params);

    }

    /**
     * Действие, возвращает JS файл.
     *
     * @return string
     */
    public function actionGetJsFile ()
    {
        $content = '';
        $modelName = Yii::$app->request->get('modelName');
        $file = Yii::$app->request->get('file');
        if (preg_match("/^[a-zA-Z0-9_]+$/", $modelName) && preg_match("/^[a-zA-Z0-9_\\/]+\\.js$/", $file)) {
            $path = '@app/modules/'.(is_string($this->module) ? $this->module : $this->module->id).'/js/'.$modelName.'/'.$file;
            $path = Yii::getAlias($path);
            if (file_exists($path)) {
                $content = file_get_contents($path);
            }
        }

        return $content;
    }

    /**
     * Возвращает список записей для отображения в панели управления
     * @return mixed|null
     */
    public function actionList()
    {
        /**
         * @var $modelName ActiveRecord
         */
        $modelName = Yii::$app->request->get('modelName', '');
        $modelName = explode('\\', $modelName);
        $modelName = array_pop($modelName);
        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$modelName;

            $filterParams = Yii::$app->request->post('colFilter', null);
            $defaultFilterCondition = Yii::$app->request->post('defaultFilterCondition', null);

            $filterParams = Json::decode($filterParams ? $filterParams : Yii::$app->request->get('colFilter', '[]'));
            $defaultFilterCondition = Json::decode($defaultFilterCondition ? $defaultFilterCondition : Yii::$app->request->get('defaultFilterCondition', '[]'));

            $params = [
                "identifyOnly" => (Yii::$app->request->get('identifyOnly', 0) ? true : false),
                'masterId' => intval(Yii::$app->request->post('masterId', 0)),
                "sort" => Json::decode(Yii::$app->request->post('sort', '[]')),
                "group" => Json::decode(Yii::$app->request->post('group', '[]')),
                "start" => intval(Yii::$app->request->post('start', 0)),
                "limit" => intval(Yii::$app->request->post('limit', 0)),
                "filter" => $filterParams,
                "all" => (Yii::$app->request->get('all', 0) ? true : false),
                "parentId" => Yii::$app->request->get('parentId', null),
                'query' => Yii::$app->request->post('query', ''),
                'defaultId' => intval(Yii::$app->request->post('defaultId', 0)),
                'defaultFilterCondition' => $defaultFilterCondition,
            ];

            $list = call_user_func([$modelName, 'getList'], $params);

            $defaultId = intval(Yii::$app->request->post('defaultId', 0));
            if ($defaultId) {
                foreach ($list['data'] as $item) {
                    if ($item['id'] == $defaultId) {
                        $defaultId = 0;
                        break;
                    }
                }
//                if ($defaultId) {
//                    $listAdd = call_user_func([$modelName, 'getList'], array_merge($params, [
//                        'where' => ["`".$modelName::tableName()."`.id" => $defaultId],
//                    ]));
//                    if ($listAdd['data']) {
//                        array_pop($list['data']);
//                        array_unshift($list['data'], $listAdd['data'][0]);
//                    }
//                }
            }

            return $list;
        }
        $this->ajaxError('\app\base\web\BackendController\actionList?modelName='.$modelName, 'Справочник не найден.');
        return null;
    }

    /**
     * Сохранение записи
     * @return array|mixed|null
     */
    public function actionSaveRecord()
    {
        $modelName = Yii::$app->request->get('modelName', '');
        $modelName = explode('\\', $modelName);
        $modelName = array_pop($modelName);
        $add = intval(Yii::$app->request->get('add', 0));
        $data = Json::decode(Yii::$app->request->post('data', '[]'));
        $masterId = intval(Yii::$app->request->post('masterId', 0));

        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$modelName;
            if ($add || !(isset($data['id']) && $data['id'])) {
                /**
                 * @var \yii\db\ActiveRecord
                 */
                if ($data && !ArrayHelper::isAssociative($data)) {
                    $results = [];
                    $code = [];
                    foreach ($data as $item) {
                        $model = new $modelName();
                        if ($result = $model->saveData($item, true, $masterId)) {
                            $results[] = $result;
                        } else {
                            $errors = [];
                            foreach ($model->errors as $error) {
                                $errors[] = implode("<br/>", $error);
                            }
                            $errors = implode('<br/>', $errors);
                            $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName.'&add=1', 'Ошибка сохранения данных:<br/>'.$errors);
                            return null;
                        }
                        $code[] = $model->afterSaveDataCode;
                    }
                    $data = [
                        'data' => $results,
                        'success' => true,
                        'exec' => implode('; ', $code),
                    ];
                    return $data;
                } else {
                    $model = new $modelName();
                    if ($result = $model->saveData($data, true, $masterId)) {
                        $data = [
                            'data' => $result,
                            'success' => true,
                            'exec' => $model->afterSaveDataCode,
                        ];
                        return $data;
                    } else {
                        $errors = [];
                        foreach ($model->errors as $error) {
                            $errors[] = implode("<br/>", $error);
                        }
                        $errors = implode('<br/>', $errors);
                        $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName.'&add=1', 'Ошибка сохранения данных:<br/>'.$errors);
                        return null;
                    }
                }
            } elseif (isset($data['id']) && $data['id']) {
                $model = call_user_func([$modelName, 'findOne'], $data['id']);
                if ($result = $model->saveData($data, false, $masterId)) {
                    $data = [
                        'data' => $result,
                        'success' => true,
                        'exec' => $model->afterSaveDataCode,
                    ];
                    return $data;
                } else {
                    $errors = [];
                    foreach ($model->errors as $error) {
                        $errors[] = implode("<br/>", $error);
                    }
                    $errors = implode('<br/>', $errors);
                    $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName.'&id='.$data['id'], 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            }
        }
        $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName, 'Справочник не найден.');
        return null;
    }

    public function actionSortRecords()
    {
        $records = str_replace('"', '', Yii::$app->request->post('records', '[]'));
        $modelName = Yii::$app->request->get('modelName', '');
        $modelName = explode('\\', $modelName);
        $modelName = array_pop($modelName);
        $position = Yii::$app->request->post('position', '');
        $over = intval(Yii::$app->request->post('over', ''));

        if ($over && preg_match("/^\\[[0-9\\,]+\\]$/", $records) && preg_match("/^[a-zA-Z0-9_]+$/", $modelName) && ($position == 'before' || $position == 'after')) {
            $records = Json::decode($records);

            /**
             * @var $modelName ActiveRecord
             */
            $modelName = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$modelName;

            // Модель рекурсивная
            $recursive = call_user_func([$modelName, 'getRecursive']);

            // У модели есть родительская или мастер модель
            $haveMaster = call_user_func([$modelName, 'getMasterModel']);
            $haveMaster = ($haveMaster ? $haveMaster : call_user_func([$modelName, 'getParentModel']));

            $parentId = 0;
            $masterId = 0;
            if ($recursive) {
                $parentId = $modelName::findOne(["id" => $records[0]])->parent_id->id;
            }

            if ($haveMaster) {
                $masterId = $modelName::findOne(["id" => $records[0]])->{$modelName::getMasterModelRelFieldName()}->id;
            }

            array_walk($records, function($rec) use ($modelName, $over, $parentId, $masterId, $position) {
                $overPriority = call_user_func([$modelName, 'findOne'], ["id" => $over])->sort_priority;
                $oldPriority = call_user_func([$modelName, 'findOne'], ["id" => $rec])->sort_priority;

                $cond = [];
                $params = [];
                if ($parentId) {
                    $cond[] = "parent_id = :parent_id";
                    $params[":parent_id"] = $parentId;
                }
                if ($masterId) {
                    $cond[] = "master_table_id = :master_table_id";
                    $params[":master_table_id"] = $masterId;
                }

                $cond = implode(" and ", $cond);

                $query = call_user_func([$modelName, 'find']);
                if ($cond) {
                    $query = $query->where($cond, $params);
                }
                $recs = $query->orderBy(["sort_priority" => SORT_ASC])->all();

                if ($oldPriority > $overPriority) {
                    $currentPriority = 1;
                    $newOverPriority = 0;
                    foreach ($recs as $item) {
                        if ($item->id == $over) {
                            $newOverPriority = $currentPriority + ($position == 'after' ? 1 : 0);
                            if ($position == 'before') {
                                $currentPriority++;
                            }
                            $item->sort_priority = $currentPriority;
                            $item->save(false);
                            $currentPriority++;
                            if ($position == 'after') {
                                $currentPriority++;
                            }
                            continue;
                        }
                        if ($item->id == $rec) {
                            $item->sort_priority = $newOverPriority;
                            $item->save(false);
                            continue;
                        }
                        $item->sort_priority = $currentPriority;
                        $item->save(false);
                        $currentPriority++;
                    }
                } elseif ($oldPriority < $overPriority) {
                    $currentPriority = 1;
                    $sortedRecord = null;
                    foreach ($recs as $item) {
                        if ($item->id == $rec) {
                            $sortedRecord = $item;
                            continue;
                        }
                        if ($item->id == $over) {
                            $sortedRecord->sort_priority = $currentPriority + ($position == 'after' ? 1 : 0);
                            $sortedRecord->save(false);
                            if ($position == 'before') {
                                $currentPriority++;
                            }
                            if ($position == 'after') {
                                $item->sort_priority = $currentPriority;
                                $item->save(false);
                                $currentPriority += 2;
                                continue;
                            }
                        }
                        $item->sort_priority = $currentPriority;
                        $item->save(false);
                        $currentPriority++;
                    }
                }
            });

            return ['success' => true];
        }
        static::ajaxError('app\base\web\BackendController\actionSortRecords',
            "Сортировка не возможна.");

    }

    /**
     * Удаление записи
     *
     * @return array|null
     */
    public function actionDeleteRecord () {
        /**
         * @var $modelName ActiveRecord
         */
        $modelName = Yii::$app->request->get('modelName', '');
        $modelName = explode('\\', $modelName);
        $modelName = array_pop($modelName);
        $data = Json::decode(Yii::$app->request->post('data', '[]'));
        $masterId = intval(Yii::$app->request->post('masterId', 0));

        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\models\\'.$modelName;

            if (is_array($data) && isset($data[0])) {
                $conditions = [];
                $params = [];

                foreach ($data as $key => $item) {
                    if (isset($item['id']) && $item['id']) {
                        $conditions[] = 'id = :id'.($key+1);
                        $params[':id'.($key+1)] = $item['id'];
                    }
                }
                if ($conditions) {
                    if ($modelName::deleteRecords($conditions, $params)) {
                        return [
                            'success' => true
                        ];
                    }
                }
            } elseif (isset($data['id']) && $data['id']) {
                if ($modelName::deleteRecords('id = :id', ['id' => $data['id']])) {
                    return [
                        'success' => true
                    ];
                }
            }
            $this->ajaxError('\app\base\web\BackendController\actionDeleteRecord?modelName='.$modelName, 'Запись не найдена.');
        }
        $this->ajaxError('\app\base\web\BackendController\actionDeleteRecord?modelName='.$modelName, 'Справочник не найден.');
        return null;
    }

    public function actionGetPrintFormInterface()
    {
        $params = Json::decode(Yii::$app->request->post("params", '[]'));

        $form = (isset($params['form']) ? $params['form'] : '');
        $recordID = (isset($params['id']) ? intval($params['id']) : 0);

        if (!preg_match("/^[a-z0-9_]+$/i", $form)) {
            throw new Exception("Print form not found");
        }

        $formFile = Yii::getAlias("@app/modules/".(is_string($this->module) ? $this->module : $this->module->id)."/printforms/{$form}.php");

        /**
         * @var $formClass PrintForm
         */
        $formClass = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\printforms\\'.$form;

        if (!file_exists($formFile) || !method_exists($formClass, 'printItem')) {
            throw new Exception("Print form not found");
        }

        /**
         * @var $form PrintForm
         */
        $form = new $formClass($this->module, $recordID, []);

        return $form->getUserInterface();
    }

    public function actionPrintItem()
    {
        $form = Yii::$app->request->get('form', '');
        $recordID = intval(Yii::$app->request->get('id'));
        $options = Json::decode(Yii::$app->request->get('options', '[]'));

        if (!preg_match("/^[a-z0-9_]+$/i", $form)) {
            throw new Exception("Print form not found");
        }

        $formFile = Yii::getAlias("@app/modules/".(is_string($this->module) ? $this->module : $this->module->id)."/printforms/{$form}.php");

        /**
         * @var $formClass PrintForm
         */
        $formClass = '\app\modules\\'.(is_string($this->module) ? $this->module : $this->module->id).'\printforms\\'.$form;

        if (!file_exists($formFile) || !method_exists($formClass, 'printItem')) {
            throw new Exception("Print form not found");
        }

        /**
         * @var $form PrintForm
         */
        $form = new $formClass($this->module, $recordID, []);
        return $form->doPrint($options);
    }

    public function actionPrintList()
    {
        $columns = Json::decode(Yii::$app->request->get('columns'));
        $options = Json::decode(Yii::$app->request->get('options', '[]'));
        $form = Yii::$app->request->get('form', '');

        if (preg_match('/^[a-z_0-9]+$/i', $form)) {
            /** @var PrintForm $form */
            $form = '\app\modules\\'.$this->module->id.'\printforms\\'.$form;

            /**
             * @var $modelName ActiveRecord
             */
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$form::getModel();

            $filterParams = Yii::$app->request->get('colFilter', null);
            $defaultFilterCondition = Yii::$app->request->get('defaultFilterCondition', null);

            $filterParams = Json::decode($filterParams ? $filterParams : Yii::$app->request->get('colFilter', '[]'));
            $defaultFilterCondition = Json::decode($defaultFilterCondition ? $defaultFilterCondition : Yii::$app->request->get('defaultFilterCondition', '[]'));

            $params = [
                "identifyOnly" => false,
                'masterId' => 0,
                "sort" => Json::decode(Yii::$app->request->get('sort', '[]')),
                "group" => Json::decode(Yii::$app->request->get('group', '[]')),
                "start" => intval(Yii::$app->request->get('start', 0)),
                "limit" => intval(Yii::$app->request->get('limit', 0)),
                "filter" => $filterParams,
                "all" => false,
                "parentId" => Yii::$app->request->get('parentId', null),
                'query' => Yii::$app->request->get('query', ''),
                'defaultId' => intval(Yii::$app->request->post('defaultId', 0)),
                'defaultFilterCondition' => $defaultFilterCondition,
            ];

            $list = $modelName::getList($params);

            if ($columns) {
                foreach ($list['data'] as $key => $item) {
                    foreach ($list['data'][$key] as $col => $val) {
                        if ($col != 'id' && !in_array($col, $columns)) {
                            unset($list['data'][$key][$col]);
                        }
                    }
                }
            }

            $form = new $form($this->module, 0, []);
            return $form->doPrint($options, $list['data']);
        }
        $this->ajaxError('\app\base\web\BackendController\actionExportList?form='.$form, 'Справочник не найден.');
        return null;

    }
}