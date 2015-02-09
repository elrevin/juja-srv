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

            if (Yii::$app->user->can('manager')) {
                // Менеджеру можно только то что ему разрешено и только в интерфейсе менеджера
                if (Yii::$app->params['backendCurrentInterfaceType'] == 'manage') {
                    // Проверяем настройки прав в БД
                    if (isset($params['modelName'])) {
                        $recordId = 0;
                        $modelName = $params['modelName'];
                        if ($modelName[0] != '\\') $modelName = '\\'.$modelName;
                        $masterModel = call_user_func([$modelName, 'getMasterModel']);
                        if ($masterModel) {
                            $modelName = $masterModel;
                            if ($modelName[0] != '\\') $modelName = '\\'.$modelName;
                            if (isset($params['masterId']) && intval($params['masterId'])) {
                                $recordId = $params['masterId'];
                            }
                        } elseif (isset($params['recordId'])) {
                            $recordId = $params['recordId'];
                        }

                        $strict = isset($params['strict']) && $params['strict'];

                        $rights = SRightsRules::findRights(trim($modelName, '\\'), $recordId, $strict);
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