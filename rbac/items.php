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
            'backend-read',
            'backend-save-record',
            'backend-delete-record',
            'backend-cp-menu',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userGroupRule',
        'children' => [
            'backend-read',
            'backend-save-record',
            'backend-delete-record',
            'backend-cp-menu',
        ],
    ],
    'backend-read' => [
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
];
