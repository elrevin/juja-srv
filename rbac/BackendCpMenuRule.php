<?php

namespace app\rbac;

use app\models\SRightsRules;
use Yii;
use yii\rbac\Rule;

class BackendCpMenuRule extends Rule
{
    public $name = 'backendCpMenuRule';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool|void
     */
    public function execute($user, $item, $params)
    {
        if (!\Yii::$app->user->isGuest) {
            return true;
        }

        // Неавторизованному пользователю ничего нельзя
        return false;
    }
}