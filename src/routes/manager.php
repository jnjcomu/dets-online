<?php


/**
 * 관리자 모드
 */

$app->get('/manager', function ($request, $response, $args) {
    $options = [
        'manager_p' => true,
        'title' => '디미고 Dets 신청 시스템 :: 관리자',
    ];

    $this->util->add_option($options);
    $this->util->check_manager($options);

    $lectures = $this->medoo->select('lectures', '*');

    $cleaning_lectures = [];
    foreach ($lectures as $lecture) {
        $students = $this->medoo->select('students', 'idx', ['lecture_idx' => $lecture['idx']]);
        $lecture['number'] = count($students);

        array_push($cleaning_lectures, $lecture);
    }

    $options['lectures'] = $cleaning_lectures;

    return $this->pug->render(__DIR__ . '/../../templates/manager_layouts/lectures_list.pug', $options);
})->add($login_check)->add($check_manager);