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
            'backend-update-record',
            'backend-create-record',
            'backend-delete-record',
        ],
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userGroupRule',
        'children' => [
            'backend-list',
            'backend-update-record',
            'backend-create-record',
            'backend-delete-record',
        ],
    ],
    'backend-list' => [
        'type' => 2,
        'ruleName' => 'backendReadRule',
    ],
    'backend-create-record' => [
        'type' => 2,
        'ruleName' => 'backendWriteRule',
    ],
    'backend-update-record' => [
        'type' => 2,
        'ruleName' => 'backendWriteRule',
    ],
    'backend-delete-record' => [
        'type' => 2,
        'ruleName' => 'backendDeleteRule',
    ],
];
