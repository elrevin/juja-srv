<?php
//echo __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules'; die;
$dir = scandir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules');
$modules = [];
$modulesNames = [];
foreach ($dir as $item) {
    if (is_dir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$item) && $item != '.' && $item != '..') {
        $modules[$item] = [
            'class' => 'app\modules\\'.$item.'\Module'
        ];

        $modulesNames[] = $item;
    }
}

return $modules;