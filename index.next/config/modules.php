<?php
//echo __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules'; die;
$dir = scandir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules');
$modules = [];
$modulesNames = [];
foreach ($dir as $item) {
    if (is_dir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$item) && $item != '.' && $item != '..') {
        /**
         * @var $moduleClass \app\base\Module
         */
        $moduleClass = '\app\modules\\'.$item.'\Module';
        $modules[$item] = [
            'class' => $moduleClass,
        ];

        $modulesNames[] = $item;
    }
}

return $modules;