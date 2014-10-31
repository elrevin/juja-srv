<?php
namespace app\components;

class Mailer extends \yii\swiftmailer\Mailer
{
    public $themeName='';

    protected function createView(array $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = View::className();
        }

        $config['themeName'] = $this->themeName;
        return \Yii::createObject($config);
    }
}