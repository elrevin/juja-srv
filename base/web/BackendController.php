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

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (Yii::$app->user->identity->isSU) {
                return true;
            } else {
                $access = false; // По умолчанию все запрещено
                if ($action->id == 'list') {
                    $access = Yii::$app->user->can('backend-'.$action->id);
                } elseif ($action->id == 'create-record') {
                    $access = Yii::$app->user->can('backend-'.$action->id);
                } elseif ($action->id == 'update-record') {
                    $access = Yii::$app->user->can('backend-'.$action->id);
                } elseif ($action->id == 'delete-record') {
                    $access = Yii::$app->user->can('backend-'.$action->id);
                }

                if (!$access) {
                    if (Yii::$app->request->isAjax) {
                        $this->ajaxError('app\base\web\BackendController\beforeAction\cpAccessDeny');
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
    public function ajaxError($type)
    {
        $code = intval(file_get_contents(Yii::getAlias('@app/data/base/lastErrorCode.txt')));
        $code++;
        file_put_contents(Yii::getAlias('@app/data/base/lastErrorCode.txt'), $code);

        Yii::error('Error #'.$code.": ".$type);
        if (Yii::$app->response->format == \yii\web\Response::FORMAT_JSON || Yii::$app->response->format == \yii\web\Response::FORMAT_RAW) {
            echo \yii\helpers\Json::encode([
                'success' => false,
                'error' => $code,
                'type' => $type
            ]);
        } elseif (Yii::$app->response->format == \yii\web\Response::FORMAT_HTML) {
            echo "<script>app.getApplication().showErrorMessage(code, type)</script>";
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