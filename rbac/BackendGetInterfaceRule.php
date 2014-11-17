<?php

namespace app\rbac;

use app\models\SRightsRules;
use Yii;
use yii\rbac\Rule;

class BackendGetInterfaceRule extends Rule
{
    public $name = 'backendGetInterfaceRule';

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
                    // Проверяем настройки прав в БД
                    if (isset($params['modelName'])) {
                        $modelName = $params['modelName'];
                        if ($modelName[0] != '\\') $modelName = '\\'.$modelName;
                        if (isset($params['parentId']) && intval($params['parentId'])) {
                            $recordId = $params['parentId'];
                        } elseif (isset($params['recordId'])) {
                            $recordId = $params['recordId'];
                        } else {
                            $recordId = 0;
                        }

                        $masterModel = call_user_func([$modelName, 'getMasterModel']);
                        if ($masterModel) {
                            $modelName = $masterModel;
                        }

                        $rights = SRightsRules::findRights(trim($modelName, '\\'), $recordId);
                        return $rights > SRightsRules::RIGHTS_NONE;
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