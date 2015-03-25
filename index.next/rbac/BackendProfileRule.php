<?php

namespace app\rbac;

use app\models\SRightsRules;
use Yii;
use yii\rbac\Rule;

class BackendProfileRule extends Rule
{
    public $name = 'backendProfileRule';

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
                if (isset($params['userId'])) {
                    return Yii::$app->user->id == $params['userId'];
                }
                return true;
            } elseif (Yii::$app->user->can('admin')) {
                return true;
            }
        }

        // Неавторизованному пользователю ничего нельзя
        return false;
    }
}