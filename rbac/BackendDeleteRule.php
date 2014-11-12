<?php

namespace app\rbac;

use app\models\SRightsRules;
use Yii;
use yii\rbac\Rule;

class BackendDeleteRule extends Rule
{
    public $name = 'backendDeleteRule';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool|void
     */
    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {

            if (Yii::$app->user->can('manager')) {
                // Менеджеру можно только то что ему разрешено и только в интерфейсе менеджера
                if (Yii::$app->params['backendCurrentInterfaceType'] == 'manage') {
                    if (isset($params['modelName'])) {
                        $modelName = $params['modelName'];
                        if (isset($params['recordId'])) {
                            $recordId = $params['recordId'];
                        } else {
                            $recordId = 0;
                        }
                        $rights = SRightsRules::findRights($modelName, $recordId);
                        return $rights > SRightsRules::RIGHTS_ALL;
                    }
                }
            } elseif (Yii::$app->user->can('admin')) {
                return true;
            }
        }

        // Неавторизованному пользователю ничего нельзя
        return false;
    }
}