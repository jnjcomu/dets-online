<?php
/**
 * 강좌 리스트 라우팅
 */

$app->any('/lectures', function ($request, $response, $args) {

    // 라우팅 기본정보
    $options = [
        'lectures_l' => true,
        'title' => '디미고 Dets 신청 시스템 :: 강의 목록',
    ];

    // 이름 할당
    $this->util->add_option($options);

    $get_option = [
        'AND' => [
            'active' => 'ACTIVE'
        ]
    ];

    if(isset($_SESSION['userdata'])) {
        $get_option['AND']['teacher_grade'] = $_SESSION['userdata']['grade'];
    }

    // ACTIVE 처리된 강의만 가져오기
    $lectures = $this->medoo->select(
        'lectures',
        ['idx', 'name', 'teacher_name', 'topic', 'description'],
        $get_option
    );

    // 옵션해시에 강의 목록 넣기
    $options['lectures'] = $lectures;

    return $this->pug->render(__DIR__ . '/../../templates/layouts/lectures_list.pug', $options);
})->add($login_check);



$app->get('/lectures/[{lecture}]', function ($request, $response, $args) {
    $options = [
        'lectures_l' => true,
        'already' => false
    ];

    $lecture = $this->medoo->select('lectures', '*', ['idx' => $args['lecture']])[0];
    $student_num = count($this->medoo->select('students', '*', ['lecture_idx' => $lecture['idx']]));

    if(isset($_SESSION['userdata'])) {
        $is_num = count($this->medoo->select('students', '*', [
            'AND' => [
                'lecture_idx' => $lecture['idx'],
                'student_name' => $_SESSION['userdata']['realname'],
                'serial' => $_SESSION['userdata']['serial']
            ]
        ]));

        if($is_num > 0) $options['already'] = true;
    }

    $options['title'] = '디미고 Dets 신청 시스템 :: ' . $lecture['name'];
    $serial = $lecture['teacher_code'];
    $options['lecture'] = [
        'idx' => $lecture['idx'],
        'name' => $lecture['name'],
        'teacher_name' => $lecture['teacher_name'],
        'teacher_info' => $serial[1] . '반 ' . substr($serial, 2) . '번',
        'description' => $lecture['description'],
        'now' => $student_num,
        'class_time' => $lecture['class_time'],
        'need_thing' => $lecture['need_thing'],
        'max' => $lecture['maximum'],
        'teacher_image' => $lecture['teacher_picurl'],
    ];

    // 이름 할당
    $this->util->add_option($options);

    return $this->pug->render(__DIR__ . '/../../templates/layouts/lectures_detail.pug', $options);
})->add($login_check);



$app->post('/lecture', function ($request, $response, $args) {

    // 유효한 강좌인치 체크
    $lecture_id = $request->getParsedBody()['lecture_id'];
    $lecture = $this->medoo->select('lectures', '*', ['idx' => $lecture_id])[0];
    if (!is_numeric($lecture_id) || empty($lecture)) {
        $response_message = $this->location->back('정상적인 강좌 아이디가 아닙니다.');
        return $response_message;
    }

    $already_apply = count($this->medoo->select('students', 'idx', [
            'AND' => [
                'lecture_idx' => $lecture_id,
                'student_name' => $_SESSION['userdata']['realname'],
                'serial' => $_SESSION['userdata']['serial']
            ]
        ])) > 0;
    if ($already_apply) {
        $response_message = $this->location->back('이미 신청한 강좌입니다.');
        return $response_message;
    }

    // 신청 부분

    $this->medoo->insert('students', [
        'lecture_idx' => $lecture_id,
        'student_name' => $_SESSION['userdata']['realname'],
        'grade' => $_SESSION['userdata']['grade'],
        'clazz' => $_SESSION['userdata']['class'],
        'number' => $_SESSION['userdata']['number'],
        'serial' => $_SESSION['userdata']['serial'],
    ]);

    $response_message = $this->location->go('/lectures/' . $lecture_id, '신청이 완료되었습니다.');
    return $response_message;
})->add($login_check);


$app->post('/remove/lecture', function ($request, $response, $args) {
    $lecture_id = $request->getParsedBody()['lecture_id'];
    $lecture = $this->medoo->select('lectures', '*', ['idx' => $lecture_id])[0];
    if (!is_numeric($lecture_id) || empty($lecture)) {
        $response_message = $this->location->back('정상적인 강좌 아이디가 아닙니다.');
        return $response_message;
    }

    $this->medoo->delete('students', [
        'AND' => [
            'lecture_idx' => $lecture_id,
            'student_name' => $_SESSION['userdata']['realname'],
            'serial' => $_SESSION['userdata']['serial']
        ]
    ]);

    $response_message = $this->location->go('/lectures/' . $lecture_id, '신청취소가 완료되었습니다.');
    return $response_message;
});

/**
 * 내가 신청한 강좌 보기
 */

$app->get('/my/lectures', function ($request, $response, $args) {

    // 라우팅 기본정보
    $options = [
        'lectures_my' => true,
        'title' => '디미고 Dets 신청 시스템 :: 신청한 강의 목록'
    ];

    // 이름 할당
    $this->util->add_option($options);

    // 내 강의만 가져오기
    $lectures = $this->medoo->select(
        'lectures',
        ['[>]students' => ['idx' => 'lecture_idx']],
        [
            'students.idx', 'lectures.name',
            'lectures.teacher_name', 'lectures.description',
            'lectures.topic', 'students.lecture_idx'
        ],
        [
            'AND' => [
                'students.student_name' => $_SESSION['userdata']['realname'],
                'students.serial' => $_SESSION['userdata']['serial']
            ]
        ]
    );

    // 옵션해시에 강의 목록 넣기
    $options['lectures'] = $lectures;

    return $this->pug->render(__DIR__ . '/../../templates/layouts/my_lectures.pug', $options);
})->add($login_check);