<?php
/**
 * Базовый класс контроллера панели управления.
 */

namespace app\base\web;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;


class BackendController extends Controller
{
    public $layout = false;
    protected $currentInterfaceType = 'manage';
    protected $defaultAccessList = [
        'list' => 'backend-read',
        'save-record' => 'backend-save-record',
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
        $this->enableCsrfValidation = false;
        Yii::$app->view->setActiveTheme('backend');
        Yii::$app->mailer->themeName = "backend";
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
        $this->accessList = array_merge($this->accessList, $this->defaultAccessList);

        if (!isset($this->accessList[$action])) {
            return false;
        }

        $accessRule = $this->accessList[$action];

        if (is_array($accessRule)) {
            if (isset($accessRule['function']) && is_callable([$this, $accessRule['function']])) {
                return call_user_func([$this, $accessRule['function']], $action);
            } elseif (isset($accessRule['rule'])) {
                $accessRule = $accessRule['rule'];
            }
        }

        $modelName = Yii::$app->request->get('modelName', '');
        $modelName = ($modelName ? '\app\modules\\'.$this->module->id.'\models\\'.$modelName : '');

        $parentId = intval(Yii::$app->request->get('parentId', 0));

        $recordId = 0;
        $data = Yii::$app->request->post('data', []);
        if (!isset($data['id'])) {
            $recordId = ['id' => 0];
        }

        return Yii::$app->user->can($accessRule, ['modelName' => $modelName, 'recordId' => $recordId, 'parentId' => $parentId, 'strict' => true]);
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
                        throw new ForbiddenHttpException('Access denied');
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
        } elseif (Yii::$app->response->cookies->has('interface')) {
            $interface = Yii::$app->response->cookies->getValue('interface', 'manage');
            $interface = ($interface == 'settings' ? 'settings' : 'manage');
        } else {
            $interface = 'manage';
        }

        return $interface;
    }

    function actionCPMenu()
    {

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
        $code = intval(file_get_contents(Yii::getAlias('@app/data/base/lastErrorCode.txt')));
        $code++;
        file_put_contents(Yii::getAlias('@app/data/base/lastErrorCode.txt'), $code);

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
        $moduleName = $this->module->id;
        return \app\helpers\Utils::getDataFile($moduleName, $fileName);
    }

    /**
     * Действие, возвращает интерфейс редактора.
     * в аргументе $parentId передается id родительской записи для детализаций, этот id так же может быть пеередан в
     * get запросе.
     * @param int $parentId
     * @return string
     */
    public function actionGetInterface($parentId = 0) {
        $modelName = Yii::$app->request->get('modelName', '');
        if (!$modelName) {
            $this->ajaxError('\app\base\web\BackendController\actionGetInterface?modelName='.$modelName, 'Справочник не найден.');
        }

        $moduleName = $this->module->id;

        $parentId = ($parentId ? $parentId : intval(Yii::$app->request->get('parentRecordId', 0)));
        $modal = intval(Yii::$app->request->get('modal', 0));

        $params = Json::decode(Yii::$app->request->post("params"), '[]');

        return call_user_func(['\app\modules\\'.$moduleName.'\models\\'.$modelName, 'getUserInterface'], false, $parentId, $modal, $params);

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
            $path = '@app/modules/'.$this->module->id.'/js/'.$modelName.'/'.$file;
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
        $modelName = Yii::$app->request->get('modelName', '');
        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$modelName;

            $params = [
                "identifyOnly" => (Yii::$app->request->get('identifyOnly', 0) ? true : false),
                'parentId' => intval(Yii::$app->request->get('parentId', 0)),
                "sort" => Json::decode(Yii::$app->request->post('sort', '[]')),
                "start" => intval(Yii::$app->request->post('start', 0)),
                "limit" => intval(Yii::$app->request->post('limit', 0)),
                "filter" => Json::decode(Yii::$app->request->post('colFilter', '[]')),
            ];

            $list = call_user_func([$modelName, 'getList'], $params);

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
        $add = intval(Yii::$app->request->get('add', 0));
        $data = Json::decode(Yii::$app->request->post('data', '[]'));
        $parentId = intval(Yii::$app->request->post('parentId', 0));

        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$modelName;
            if ($add) {
                /**
                 * @var \yii\db\ActiveRecord
                 */
                $model = new $modelName();
                if ($result = $model->saveData($data, true, $parentId)) {
                    $data = [
                        'data' => $result,
                        'success' => true
                    ];
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName.'&add=1', 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            } elseif (isset($data['id']) && $data['id']) {
                $model = call_user_func([$modelName, 'findOne'], $data['id']);
                if ($result = $model->saveData($data, false, $parentId)) {
                    $data = [
                        'data' => $result,
                        'success' => true
                    ];
                    return $data;
                } else {
                    $errors = "";
                    foreach ($model->errors as $error) {
                        $errors .= implode("<br/>", $error);
                    }
                    $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName.'&id='.$data['id'], 'Ошибка сохранения данных:<br/>'.$errors);
                    return null;
                }
            }
        }
        $this->ajaxError('\app\base\web\BackendController\actionSave?modelName='.$modelName, 'Справочник не найден.');
        return null;
    }

    /**
     * Удаление записи
     *
     * @return array|null
     */
    public function actionDeleteRecord () {
        $modelName = Yii::$app->request->get('modelName', '');
        $data = Json::decode(Yii::$app->request->post('data', '[]'));
        $parentId = intval(Yii::$app->request->post('parentId', 0));

        if (preg_match('/^[a-z_0-9]+$/i', $modelName)) {
            $modelName = '\app\modules\\'.$this->module->id.'\models\\'.$modelName;

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
                    if (call_user_func([$modelName, 'deleteRecords'], $conditions, $params)) {
                        return [
                            'success' => true
                        ];
                    }
                }
            } elseif (isset($data['id']) && $data['id']) {
                if (call_user_func([$modelName, 'deleteRecords'], 'id = :id', [':id' => $data['id']])) {
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
}