<?php

namespace app\rbac;

use Yii;
use yii\rbac\Rule;

class UserGroupRule extends Rule
{
    public $name = 'userGroupRule';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool|void
     */
    public function execute($user, $item, $params)
    {
        $go = 0;
        if (!Yii::$app->user->isGuest) {
            $group = Yii::$app->user->identity->getUserData()->group;
            if ($item->name === 'admin') {
                return Yii::$app->user->identity->isSU;
            } elseif ($item->name === 'manager') {
                return $group->cp_access == 1;
            }
        } elseif ($item->name === 'guest') {
            return true;
        }
        return false;
    }
}