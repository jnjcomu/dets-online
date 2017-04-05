<?php

/**
 * 강사 신청 라우팅
 */
$app->get('/teachers/request', function ($request, $response, $args) {
    $userdata = $_SESSION['userdata'];
    $options = [
        'teachers_r' => true,
        'title' => '디미고 Dets 신청 시스템 :: 강사 신청',
    ];

    // 이름 할당
    $this->util->add_option($options);
    $this->util->check_manager($options);

    return $this->pug->render(__DIR__ . '/../../templates/layouts/teachers_request.pug', $options);
})->add($login_check);

/**
 * 강사 신청 DB 처리 라우팅
 */

$app->post('/teachers/request', function ($request, $response, $args) {
    return $response;
});
