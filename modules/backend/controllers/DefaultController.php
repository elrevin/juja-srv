<?php
namespace app\modules\backend\controllers;

use app\models\SUsers;
use app\models\UserIdentity;
use Yii;

class DefaultController extends \app\base\web\BackendController
{
    public $layout = false;
    protected $useAccessControll = true;

    public function beforeAction($action)
    {
        return true;
    }

    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            // Пользователь не авторизован, рендерим страницу авторизации
            $state = Yii::$app->request->get('state', '');
            $state = $state == 'restore' ? 'restore' : '';
            return $this->render('auth', ['state' => $state]);
        } else {
            $interface = $this->getCurrentInterfaceType();

            if ($interface == 'settings' && Yii::$app->user->getIdentity()->isSU) {
                Yii::$app->response->redirect(["backend/default/index"]);
                Yii::$app->end();
            }

            //$user = SUsers::findOne(['id' => Yii::$app->user->getId()]);
            return $this->render('index', [
                'interface' => $interface,
                'userName' => Yii::$app->user->getIdentity()->name,
                'userId' => Yii::$app->user->getId()
            ]);
        }
    }

    public function actionAuth()
    {
        if (Yii::$app->user->isGuest) {
            // Пользователь не авторизован, пробуем авторизовать, вываливаем ошибку в случае неудачи

            $errors = [];

            $login = Yii::$app->request->post('login', '');
            $password = Yii::$app->request->post('password', '');

            if (!$login) {
                $errors['username'] = 'empty';
            }

            if (!$password) {
                $errors['password'] = 'empty';
            }

            if (!$errors) {
                $identity = UserIdentity::findByUsername($login);
                if ($identity && $identity->validatePassword($password)) {
                    if (Yii::$app->user->login($identity, 0)) {
                        Yii::$app->response->redirect(["backend/default/index"]);
                        Yii::$app->end();
                    } else {
                        $errors['login'] = 'some';
                    }
                } else {
                    $errors['login'] = 'incorrectPassword';
                }
            }

            if ($errors) {
                return $this->render('auth', ['authErrors' => $errors]);
            }
        } else {
            // Пользователь авторизован, просто редиректим на главную
            Yii::$app->response->redirect(["backend/default/index"]);
            Yii::$app->end();
        }
        return '';
    }

    public function actionRestore()
    {
        if (Yii::$app->user->isGuest) {
            $errors = [];

            $email = Yii::$app->request->post('email', '');

            if (!$email) {
                $errors['email'] = 'empty';
            }

            if (!$errors) {
                $user = SUsers::findOne(['email' => $email]);

                if (!$user) {
                    $errors['user'] = 'notFound';
                } else {
                    $restoreCode = Yii::$app->security->generatePasswordHash($user->email . "-" . $user->id . "-" . $user->username);
                    $restoreCodeExpires = time() + 3 * 24 * 60 * 60;
                    $user->restore_code = $restoreCode;
                    $user->restore_code_expires = date('Y-m-d H:i:s', $restoreCodeExpires);
                    $user->save();

                    // Отправляем письмо с кодом

                    \Yii::$app->mailer->compose('restore', ['code' => $restoreCode, 'codeExpires' => $restoreCodeExpires, 'email' => $email])
                        ->setFrom([\Yii::$app->params['cmsEmail'] => \Yii::$app->params['cmsEmailName']])
                        ->setTo($user->email)
                        ->setSubject(\Yii::$app->params['passwordRestoreLetterSubject'])
                        ->send();
                }
            }
            if ($errors) {
                return $this->render('auth', ['restoreErrors' => $errors, 'state' => 'restore']);
            } else {
                return $this->render('restore_success');
            }
        } else {
            // Пользователь авторизован, просто редиректим на главную
            Yii::$app->response->redirect(["backend/default/index"]);
            Yii::$app->end();
        }
        return '';
    }

    public function actionDoRestore()
    {
        $email = Yii::$app->request->get('email', '');
        $code = Yii::$app->request->get('code', '');

        $user = SUsers::findOne(['email' => $email, 'restore_code' => $code]);
        if ($user && $user->restore_code_expires > date('Y-m-d H:i:s')) {
            $do = intval(Yii::$app->request->post('do', 0));
            $errors = [];
            if ($do) {
                $password = Yii::$app->request->post('password', '');
                $passwordConfirm = Yii::$app->request->post('passwordConfirm', '');

                if ($password != $passwordConfirm) {
                    $errors['password'] = 'notMatch';
                }

                if (strlen($password) < 5) {
                    $errors['password'] = 'tooShort';
                }

                if (strlen($password) > 50) {
                    $errors['password'] = 'tooLong';
                }

                if (!$password) {
                    $errors['password'] = 'empty';
                }

                if (!$errors) {
                    $user->restore_code = null;
                    $user->restore_code_expires = null;
                    $user->password = Yii::$app->security->generatePasswordHash($password);
                    $user->save();
                    $identity = UserIdentity::getIdentity($user);
                    if ($identity) {
                        if (Yii::$app->user->login($identity)) {
                            Yii::$app->response->redirect(["backend/default/index"]);
                            Yii::$app->end();
                        }
                    }

                    $errors['login'] = 'some';
                }

            }
            return $this->render('change_password', ['errors' => $errors, 'email' => $email, 'code' => $code]);
        }
        return $this->render('do_restore_error');
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        Yii::$app->response->redirect(["backend/default/index"]);
        Yii::$app->end();
    }
}