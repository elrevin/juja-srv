<?php
/**
 * Базовый класс контроллера панели управления.
 */

namespace app\base\web;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class BackendController extends Controller
{
    public $layout = false;
    protected $currentInterfaceType = 'manage';

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
        if ($action == 'list') {
            $modelName = Yii::$app->request->get('modelName', '');
            return Yii::$app->user->can('backend-'.$action, ['modelName' => $modelName]);
        } elseif ($action == 'save-record') {
            $modelName = Yii::$app->request->get('modelName', '');
            $data = Yii::$app->request->post('data', []);
            if (!isset($data['id'])) {
                $data = ['id' => 0];
            }
            return Yii::$app->user->can('backend-'.$action, ['modelName' => $modelName, 'recordId' => $data['id']]);
        } elseif ($action == 'delete-record') {
            $modelName = Yii::$app->request->get('modelName', '');
            $data = Yii::$app->request->post('data', []);
            if (!isset($data['id'])) {
                $data = ['id' => 0];
            }
            return Yii::$app->user->can('backend-'.$action, ['modelName' => $modelName, 'recordId' => $data['id']]);
        } elseif ($action == 'cp-menu') {
            return Yii::$app->user->can('backend-'.$action);
        } elseif ($action == 'get-interface') {
            $modelName = Yii::$app->request->get('modelName', '');
            $recordId = Yii::$app->request->get('recordId', 0);
            return Yii::$app->user->can('backend-'.$action, ['modelName' => $modelName, 'recordId' => $recordId]);
        }
        return false; // По умолчанию все запрещено
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
            echo \yii\helpers\Json::encode([
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
        return file_get_contents(Yii::getAlias("@app/data/{$moduleName}/{$fileName}"));
    }
}