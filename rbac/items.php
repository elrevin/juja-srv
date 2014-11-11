<?php
return [
    'guest' => [
        'type' => 1,
        'ruleName' => 'userGroupRule',
    ],
    'manager' => [
        'type' => 1,
        'ruleName' => 'userGroupRule',
        'children' => [
            'backend-list',
            'backend-save-record',
            'backend-delete-record',
            'backend-cp-menu',
            'backend-get-interface',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userGroupRule',
        'children' => [
            'backend-list',
            'backend-save-record',
            'backend-cp-menu',
            'backend-get-interface',
        ],
    ],
    'backend-list' => [
        'type' => 2,
        'ruleName' => 'backendReadRule',
    ],
    'backend-save-record' => [
        'type' => 2,
        'ruleName' => 'backendWriteRule',
    ],
    'backend-delete-record' => [
        'type' => 2,
        'ruleName' => 'backendDeleteRule',
    ],
    'backend-cp-menu' => [
        'type' => 2,
        'ruleName' => 'backendCpMenuRule',
    ],
    'backend-get-interface' => [
        'type' => 2,
        'ruleName' => 'backendGetInterfaceRule',
    ],
];
