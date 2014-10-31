<?php

namespace app\rbac;

use app\models\SRightsRules;
use Yii;
use yii\rbac\Rule;

class BackendReadRule extends Rule
{
    public $name = 'backendReadRule';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool|void
     */
    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {

            if ($item->name == 'manager') {
                // Менеджеру можно только то что ему разрешено и только в интерфейсе менеджера
                if (Yii::$app->params['backendCurrentInterfaceType'] == 'manage') {
                    // Проверяем настройки прав в БД
                    $modelName = $params['modelName'];
                    $recordId = $params['recordId'];

                    $rights = SRightsRules::findRights($modelName);
                    return $rights > SRightsRules::RIGHTS_NONE;
                }
            } elseif ($item->name == 'admin') {
                return true;
            }
        }

        // Неавторизованному пользователю ничего нельзя
        return false;
    }
}