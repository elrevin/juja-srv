<?php
namespace app\commands;

use app\rbac\BackendDeleteRule;
use app\rbac\BackendReadRule;
use app\rbac\BackendWriteRule;
use app\rbac\BackendCpMenuRule;
use app\rbac\BackendProfileRule;
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
        $backendRead  = $authManager->createPermission('backend-read');
        $backendSaveRecord  = $authManager->createPermission('backend-save-record');
        $backendDeleteRecord = $authManager->createPermission('backend-delete-record');
        $backendCpMenu = $authManager->createPermission('backend-cp-menu');
        $backendProfile = $authManager->createPermission('backend-profile');

        // Добавляем разрешения в Yii::$app->authManager
        $authManager->add($backendRead);
        $authManager->add($backendSaveRecord);
        $authManager->add($backendDeleteRecord);
        $authManager->add($backendCpMenu);
        $authManager->add($backendProfile);

        // Сознаем правила и привязываем их к разрешениям
        // Чтение
        $backendReadRule = new BackendReadRule();
        $authManager->add($backendReadRule);
        $backendRead->ruleName = $backendReadRule->name;
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
        // Профиль
        $backendProfileRule = new BackendProfileRule();
        $authManager->add($backendProfileRule);
        $backendProfile->ruleName = $backendProfileRule->name;


        $authManager->addChild($manager, $backendRead);
        $authManager->addChild($manager, $backendSaveRecord);
        $authManager->addChild($manager, $backendDeleteRecord);
        $authManager->addChild($manager, $backendCpMenu);
        $authManager->addChild($manager, $backendProfile);

        $authManager->addChild($admin, $backendRead);
        $authManager->addChild($admin, $backendSaveRecord);
        $authManager->addChild($admin, $backendDeleteRecord);
        $authManager->addChild($admin, $backendCpMenu);
        $authManager->addChild($admin, $backendProfile);
    }
}