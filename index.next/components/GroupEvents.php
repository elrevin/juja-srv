<?php
namespace app\components;
use app\base\Module;
use yii\base\Component;
use yii\base\Event;

class GroupEvents extends Component
{
    /**
     * @param string $group
     * @param string $eventName
     * @param Event $event
     * @param Object $sender
     */
    function trigger($group, $eventName, Event $event, $sender = null) 
    {
        /** @var Module $item */
        foreach (\Yii::$app->modules as $item) {
            if ($event->sender === null) {
                $event->sender = $sender;
            }
            $item->onGroupEvent($group, $eventName, $event);
        }
    }
}