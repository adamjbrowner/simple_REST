<?php

declare(strict_types=1);

$middleware = [
    'permission' => \Rest\Core\Routing\Middleware\ResourcePermissionMiddleware::class,
    'loggedIn' => \Rest\Core\Routing\Middleware\LoggedInMiddleware::class,
    'roleGuard' => \Rest\Core\Routing\Middleware\AuthRoleGuardMiddleware::class,
];

$routes = [
    ['method' => 'POST', 'url' => '/login', 'function' => 'Rest\Controllers\AuthController/login'],
    ['method' => 'POST', 'url' => '/send-reset-password-link', 'function' => 'Rest\Controllers\AuthController/sendResetPasswordLink'],
    ['method' => 'POST', 'url' => '/reset-password', 'function' => 'Rest\Controllers\AuthController/resetPasswordFromToken'],
    ['method' => 'GET', 'url' => '/reset-password-tokens', 'function' => 'Rest\Controllers\ResetPasswordTokenController/getToken'],
    ['method' => 'POST', 'url' => '/verify-otp', 'function' => 'Rest\Controllers\AuthController/verifyOneTimecode'],
    '/search' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'POST', 'url' => '/update', 'function' => "Rest\Controllers\SearchController/updateIndex"],
            ['method' => 'GET', 'url' => '/update-all', 'function' => "Rest\Controllers\SearchController/updateAll", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\SearchController/search"],
        ],
    ],

    '/notifications' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\NotificationController/index", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'GET', 'url' => '/{id}', 'function' => 'Rest\Controllers\NotificationController/readById'],
            ['method' => 'POST', 'url' => '', 'function' => 'Rest\Controllers\NotificationController/create'],
            ['method' => 'POST', 'url' => '/{id}', 'function' => 'Rest\Controllers\NotificationController/update'],
            ['method' => 'DELETE', 'url' => '/{id}', 'function' => 'Rest\Controllers\NotificationController/delete'],

        ]
    ],

    '/modules' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\ModuleController/index"],
            ['method' => 'GET', 'url' => '/{id}', 'function' => "Rest\Controllers\ModuleController/readById"],
            ['method' => 'POST', 'url' => '', 'function' => "Rest\Controllers\ModuleController/create", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'POST', 'url' => '/{id}', 'function' => "Rest\Controllers\ModuleController/update", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'DELETE', 'url' => '/{id}', 'function' => "Rest\Controllers\ModuleController/delete", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
        ],
    ],
    '/users' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\UserController/index"],
            ['method' => 'GET', 'url' => '/{id}', 'function' => "Rest\Controllers\UserController/readById"],
            ['method' => 'POST', 'url' => '/{id}/password', 'function' => "Rest\Controllers\UserController/updatePassword", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'POST', 'url' => '', 'function' => "Rest\Controllers\UserController/create", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'POST', 'url' => '/{id}', 'function' => "Rest\Controllers\UserController/update", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'DELETE', 'url' => '/{id}', 'function' => "Rest\Controllers\UserController/delete", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'GET', 'url' => '/current-user', 'function' => 'Rest\Controllers\AuthController/getCurrentUser'],
            ['method' => 'GET', 'url' => '/{id}/roles', 'function' => 'Rest\Controllers\RoleController/getRolesForUser'],
            ['method' => 'POST', 'url' => '/{id}/roles', 'function' => 'Rest\Controllers\RoleController/newRoleForUser', 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'DELETE', 'url' => '/{id}/roles/{roleId}', 'function' => 'Rest\Controllers\RoleController/deleteUserRole', 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],


            ['method' => 'GET', 'url' => '/{id}/notifications', 'function' => 'Rest\Controllers\NotificationController/getForUser'],
        ]
    ],
    '/resources' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\ResourceController/index"],
            ['method' => 'GET', 'url' => '/{id}', 'function' => "Rest\Controllers\ResourceController/readById"],
            ['method' => 'POST', 'url' => '', 'function' => "Rest\Controllers\ResourceController/create", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'POST', 'url' => '/{id}', 'function' => "Rest\Controllers\ResourceController/update", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],

            ['method' => 'DELETE', 'url' => '/{id}', 'function' => "Rest\Controllers\ResourceController/delete", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]]
        ]

    ],
    '/roles' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '', 'function' => "Rest\Controllers\RoleController/index", 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]],
            ['method' => 'GET', 'url' => '/{id}', 'function' => "Rest\Controllers\RoleController/readById"],
            ['method' => 'POST', 'url' => '', 'function' => "Rest\Controllers\RoleController/create"],
            ['method' => 'POST', 'url' => '/{id}', 'function' => "Rest\Controllers\RoleController/update"],
            ['method' => 'DELETE', 'url' => '/{id}', 'function' => "Rest\Controllers\RoleController/delete"],

            ['method' => 'GET', 'url' => '/{id}/modules', 'function' => 'Rest\Controllers\ModuleController/getModulesForRole'],
            ['method' => 'POST', 'url' => '/{id}/modules', 'function' => 'Rest\Controllers\ModuleController/newRoleModule'],
            ['method' => 'DELETE', 'url' => '/{id}/modules/{moduleId}', 'function' => 'Rest\Controllers\ModuleController/deleteRoleModule'],

        ]
    ],
    '/logs' => [
        'middleware' => ['class' => $middleware['loggedIn']],
        'routes' => [
            ['method' => 'GET', 'url' => '/error-logs', 'function' => 'Rest\Controllers\LogController/getErrorLogs', 'middleware' => [[
                'class' => $middleware['roleGuard'],
                'args' => ['admin']
            ]]]
        ]
    ]
];

return $routes;
