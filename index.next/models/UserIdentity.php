<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;

/**
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $authKey
 * @property string $restoreCode
 * @property string $restoreCodeExpires
 * @property string $isSU
 * @property string $cpAccess
 */
class UserIdentity extends \yii\base\Object implements \yii\web\IdentityInterface
{
    /**
     * id Пользователя
     * @var int
     */
    public $id;

    /**
     * логин пользователя
     * @var string
     */
    public $username;

    /**
     * email пользователя
     * @var string
     */
    public $email;

    /**
     * имя пользователя
     * @var string
     */
    public $name;

    /**
     * Пароль пользлователя (хэш)
     * @var string
     */
    public $password;

    /**
     * Код сессии
     * @var string
     */
    public $authKey;

    /**
     * Код воставновления пароля
     * @var string
     */
    public $restoreCode;

    /**
     * Время жизни кода востановления пароля в формате 'Y-m-d H:i:s'
     * @var string
     */
    public $restoreCodeExpires;

    /**
     * true если вошел суперпользователь
     * @var bool
     */
    public $isSU = false;

    /**
     * true если пользователь имеет доступ к панели управления
     * @var bool
     */
    public $cpAccess = false;

    /**
     * @var \app\models\SUsers
     */
    public $user = null;

    /**
     * Возвращает identity по записи модели SUsers
     * @param \app\models\SUsers $user
     * @return null|\app\models\UserIdentity
     */
    public static function getIdentity($user)
    {
        if ($user) {
            return \Yii::createObject([
                'class' => 'app\models\UserIdentity',
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'password' => $user->password,
                'authKey' => $user->hash,
                'isSU' => $user->su,
                'cpAccess' => $user->group->cp_access,
                'email' => $user->email,
                'user' => $user,
            ]);
        }
        return null;
    }

    /**
     * Возвращает identity по id пользователя
     * @param int|string $id
     * @return null|\app\models\UserIdentity
     */
    public static function findIdentity($id)
    {
        $identity = self::getIdentity(SUsers::findOne(['id' => $id]));
        return ($identity ? $identity : null);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Возвращает identity по логину пользователя
     * @param $username
     * @return null|\app\models\UserIdentity
     */
    public static function findByUsername($username)
    {
        $identity = self::getIdentity(SUsers::findOne(['username' => $username]));
        return ($identity ? $identity : null);
    }

    /**
     * Возвращает id пользователя
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Возвращает код сохраненной сессии
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Проверяет подлинность кода сохраненной сессии
     * @param string $authKey
     * @return bool
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Проверка пароля
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Генерация кода востановления пароля
     */
    public function generatePasswordRestoreCode()
    {
        $this->restoreCode = Yii::$app->security->generateRandomString() . '_' . time();
        $this->restoreCodeExpires = date('Y-m-d H:i:s', time()+3*24*60*60);

        $user = SUsers::findOne(['id' => $this->id]);
        $user->restore_code = $this->restoreCode;
        $user->restore_code_expires = $this->restoreCodeExpires;
        $user->save();
    }

    /**
     * Удаление кода востановления пароля
     */
    public function removePasswordRestoreCode()
    {
        $this->restoreCode = null;
        $this->restoreCodeExpires = null;

        $user = SUsers::findOne(['id' => $this->id]);
        $user->restore_code = $this->restoreCode;
        $user->restore_code_expires = $this->restoreCodeExpires;
        $user->save();
    }

    public function getUserData ()
    {
        if (!$this->user) {
            $this->user = SUsers::findOne(['id' => $this->id]);
        }
        return $this->user;
    }
}
