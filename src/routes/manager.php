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
        $lecture['teacher_info'] = $lecture['teacher_name'] . ' (' . $lecture['teacher_code'] . ')';

        array_push($cleaning_lectures, $lecture);
    }

    $options['lectures'] = $cleaning_lectures;

    return $this->pug->render(__DIR__ . '/../../templates/manager_layouts/lectures_list.pug', $options);
})->add($login_check)->add($check_manager);



$app->get('/manager/lectures/[{lecture}]', function ($request, $response, $args) {
    $options = [
        'manager_p' => true,
        'title' => '디미고 Dets 관리 시스템 :: ',
    ];

    $this->util->add_option($options);
    $this->util->check_manager($options);

    $lecture = $this->medoo->select('lectures', '*', ['idx' => $args['lecture']])[0];
    $lecture['teacher_info'] = $lecture['teacher_name'] . ' (' . $lecture['teacher_code'] . ')';

    $options['lecture'] = $lecture;

    return $this->pug->render(__DIR__ . '/../../templates/manager_layouts/lecture_managed.pug', $options);
})->add($login_check)->add($check_manager);

$app->post('/manager/lecture', function ($request, $response, $args) {
    $data = $request->getParsedBody();

    $this->medoo->update('lectures', [
        'name' => $data['name'],
        'description' => $data['description'],
        'maximum' => $data['maximum'],
        'topic' => $data['topic'],
        'class_time' => $data['class_time'],
        'need_thing' => $data['need_thing'],
    ], ['idx' => $data['id']]);

    $response_message = $this->location->go('/manager/lectures/' . $data['id'], '수정이 완료되었습니다.');
    return $response_message;
})->add($login_check)->add($check_manager);

$app->get('/manager/lecture/delete', function ($request, $response, $args) {
    $data = $request->getParsedBody();

    $this->medoo->delete('lectures', ['idx' => $data['id']]);

    $response_message = $this->location->go('/manager/lectures/' . $data['id'], '삭제가 완료되었습니다.');
    return $response_message;
});