<?php

/**
 * Middlewares
 */

$login_check = function ($request, $response, $next) {
    if (isset($_SESSION['userdata'])) {
        return $next($request, $response);
    } else {
        return $response->withStatus(302)->withHeader('Location', '/login');
    }
};

$check_manager = function ($request, $response, $next) {
    $managers = $this->medoo->select('managers', '*', [
        'AND' => [
            'name' => $_SESSION['userdata']['realname'],
            'serial' => $_SESSION['userdata']['serial']
        ]
    ]);

    $is_manager = count($managers) > 0;
    if($is_manager) {
        return $next($request, $response);
    } else {
        return $response->withStatus(302)->withHeader('Location', '/');
    }
};

/**
 * Routes
 */

/**
 * 소개 라우팅
 */

$app->get('/', function ($request, $response, $args) {
    $options = [
        'intro' => true,
        'title' => '디미고 Dets 신청 시스템',
    ];

    // 이름 할당
    $this->util->add_option($options);
    $this->util->check_manager($options);

    return $this->pug->render(__DIR__ . '/../templates/layouts/intro.pug', $options);
});

require __DIR__ . '/routes/lectures.php';
require __DIR__ . '/routes/manager.php';
require __DIR__ . '/routes/user.php';