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

    return $this->pug->render(__DIR__ . '/../templates/layouts/intro.pug', $options);
});

require __DIR__ . '/routes/lectures.php';
require __DIR__ . '/routes/user.php';