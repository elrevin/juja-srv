<?php
namespace app\base\console;

class Controller extends \yii\console\Controller
{
    public function init()
    {
        \Yii::$app->view->setActiveTheme(\Yii::$app->params['themeName']);
        \Yii::$app->viewPath = '@themeroot/views/console';
        if ($this->module->id) {
            \Yii::$app->mailer->viewPath = '@themeroot/views/'.$this->module->id.'/console/'.$this->id.'/views';
        }


        \Yii::$app->mailer->view->setActiveTheme(\Yii::$app->params['themeName']);
        \Yii::$app->mailer->themeName = \Yii::$app->params['themeName'];

        \Yii::$app->mailer->viewPath = '@themeroot/views/console/'.$this->id.'/mail';
        if ($this->module->id) {
            \Yii::$app->mailer->viewPath = '@themeroot/views/'.$this->module->id.'/console/'.$this->id.'/mail';
        }
        \Yii::$app->mailer->htmlLayout = false;
        parent::init();
    }
}