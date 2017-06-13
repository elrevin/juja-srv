<?php
namespace app\components;
use app\base\Module;
use yii\base\Component;

class Messages extends Component
{
    static function sendMessageToAll($name, $args, &$result)
    {

        /** @var Module $module */
        foreach (\Yii::$app->modules as $module) {
            if (method_exists($module, 'onMessage') && $module->onMessage($name, $result, $args)) {
                return true;
            }
        }

        return false;
    }
}