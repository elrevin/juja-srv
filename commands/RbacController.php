<?php
namespace app\commands;

use app\rbac\BackendDeleteRule;
use app\rbac\BackendReadRule;
use app\rbac\BackendWriteRule;
use app\rbac\BackendCpMenuRule;
use app\rbac\BackendGetInterfaceRule;
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
        $backendSaveRecord  = $authManager->createPermission('backend-save-record');
        $backendDeleteRecord = $authManager->createPermission('backend-delete-record');
        $backendCpMenu = $authManager->createPermission('backend-cp-menu');
        $backendGetInterface = $authManager->createPermission('backend-get-interface');

        // Добавляем разрешения в Yii::$app->authManager
        $authManager->add($backendList);
        $authManager->add($backendSaveRecord);
        $authManager->add($backendDeleteRecord);
        $authManager->add($backendCpMenu);
        $authManager->add($backendGetInterface);

        // Сознаем правила и привязываем их к разрешениям
        // Чтение
        $backendReadRule = new BackendReadRule();
        $authManager->add($backendReadRule);
        $backendList->ruleName = $backendReadRule->name;
        // Запись
        $backendWriteRule = new BackendWriteRule();
        $authManager->add($backendWriteRule);
        $backendSaveRecord->ruleName = $backendWriteRule->name;
        // Удаление
        $backendDeleteRule = new BackendDeleteRule();
        $authManager->add($backendDeleteRule);
        $backendDeleteRecord->ruleName = $backendDeleteRule->name;
        // Главное меню
        $backendCpMenuRule = new BackendCpMenuRule();
        $authManager->add($backendCpMenuRule);
        $backendCpMenu->ruleName = $backendCpMenuRule->name;
        // Интерфейс модуля
        $backendGetInterfaceRule = new BackendGetInterfaceRule();
        $authManager->add($backendGetInterfaceRule);
        $backendGetInterface->ruleName = $backendGetInterfaceRule->name;

        $authManager->addChild($manager, $backendList);
        $authManager->addChild($manager, $backendSaveRecord);
        $authManager->addChild($manager, $backendDeleteRecord);
        $authManager->addChild($manager, $backendCpMenu);
        $authManager->addChild($manager, $backendGetInterface);

        $authManager->addChild($admin, $backendList);
        $authManager->addChild($admin, $backendSaveRecord);
        $authManager->addChild($admin, $backendDeleteRecord);
        $authManager->addChild($admin, $backendCpMenu);
        $authManager->addChild($admin, $backendGetInterface);
    }
}