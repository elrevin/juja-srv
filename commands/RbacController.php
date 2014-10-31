<?php
namespace app\commands;

use app\rbac\BackendDeleteRule;
use app\rbac\BackendReadRule;
use app\rbac\BackendWriteRule;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $authManager = \Yii::$app->authManager;

        // Создаем роли для пенели управления
        $guest  = $authManager->createRole('guest');
        $manager  = $authManager->createRole('manager');
        $admin = $authManager->createRole('admin');

        $rule = new \app\rbac\UserGroupRule;
        $authManager->add($rule);

        $guest->ruleName = $rule->name;
        $manager->ruleName = $rule->name;
        $admin->ruleName = $rule->name;

        $authManager->add($guest);
        $authManager->add($manager);
        $authManager->add($admin);

        // Создаем разрешения для действий контроллеров панели управления, к каждому разрешению добавляем backend, чтобы не
        // было конфликтов с frontend'ом
        $backendList  = $authManager->createPermission('backend-list');
        $backendCreateRecord = $authManager->createPermission('backend-create-record');
        $backendUpdateRecord  = $authManager->createPermission('backend-update-record');
        $backendDeleteRecord = $authManager->createPermission('backend-delete-record');

        // Добавляем разрешения в Yii::$app->authManager
        $authManager->add($backendList);
        $authManager->add($backendCreateRecord);
        $authManager->add($backendUpdateRecord);
        $authManager->add($backendDeleteRecord);

        // Сознаем правила и привязываем их к разрешениям
        // Чтение
        $backendReadRule = new BackendReadRule();
        $authManager->add($backendReadRule);
        $backendList->ruleName = $backendReadRule->name;
        // Запись
        $backendWriteRule = new BackendWriteRule();
        $authManager->add($backendWriteRule);
        $backendCreateRecord->ruleName = $backendWriteRule->name;
        $backendWriteRule = new BackendWriteRule();
        $authManager->add($backendWriteRule);
        $backendUpdateRecord->ruleName = $backendWriteRule->name;
        // Удаление
        $backendDeleteRule = new BackendDeleteRule();
        $authManager->add($backendDeleteRule);
        $backendDeleteRecord->ruleName = $backendDeleteRule->name;

        $authManager->addChild($manager, $backendList);
        $authManager->addChild($manager, $backendUpdateRecord);
        $authManager->addChild($manager, $backendCreateRecord);
        $authManager->addChild($manager, $backendDeleteRecord);

        $authManager->addChild($admin, $backendList);
        $authManager->addChild($admin, $backendUpdateRecord);
        $authManager->addChild($admin, $backendCreateRecord);
        $authManager->addChild($admin, $backendDeleteRecord);
    }
}