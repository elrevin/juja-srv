<?php

/*
 * Скрипт должен вернуть настроки компонента db, обычно просто меняются:
 * BASE-NAME на имя базы данных
 * USER-NAME на имя пользователя базы данных
 * password на пароль к базе данных
 *
 */

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=BASE-NAME',
    'username' => 'USER-NAME',
    'password' => 'PASSWORD',
    'charset' => 'utf8',
];
