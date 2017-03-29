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
            'lectures.topic'
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

    return $this->pug->render(__DIR__ . '/../templates/layouts/my_lectures.pug', $options);
})->add($login_check);

/**
 * 강좌 리스트 라우팅
 */

$app->get('/lectures', function ($request, $response, $args) {

    // 라우팅 기본정보
    $options = [
        'lectures_l' => true,
        'title' => '디미고 Dets 신청 시스템 :: 강의 목록',
    ];

    // 이름 할당
    $this->util->add_option($options);

    // ACTIVE 처리된 강의만 가져오기
    $lectures = $this->medoo->select(
        'lectures',
        ['idx', 'name', 'teacher_name', 'topic', 'description'],
        ['active' => 'ACTIVE']
    );

    // 옵션해시에 강의 목록 넣기
    $options['lectures'] = $lectures;

    return $this->pug->render(__DIR__ . '/../templates/layouts/lectures_list.pug', $options);
});

$app->get('/lectures/[{lecture}]', function ($request, $response, $args) {
    $options = [
        'lectures_l' => true,
    ];

    $lecture = $this->medoo->select('lectures', '*', ['idx' => $args['lecture']])[0];
    $options['title'] = '디미고 Dets 신청 시스템 :: ' . $lecture['name'];
    $serial = $lecture['teacher_code'];
    $options['lecture'] = [
        'name' => $lecture['name'],
        'teacher_name' => $lecture['teacher_name'],
        'teacher_info' => $serial[1] . '반 ' . substr($serial, 2) . '번',
        'description' => $lecture['description'],
        'now' => '0',
        'max' => $lecture['maximum'],
        'teacher_image' => 'http://www.gravatar.com/avatar/6f790b180d17908ba75f675f2e9d1fd7?d=identicon&s=40'
    ];

    // 이름 할당
    $this->util->add_option($options);

    return $this->pug->render(__DIR__ . '/../templates/layouts/lectures_detail.pug', $options);
});

$app->post('/lectures/[{lecture}]', function ($request, $response, $args) {
    $lecture_id = $args['lecture'];
    if (is_numeric($lecture_id)) {
        $already_apply = count($this->medoo->select('students', 'idx', [
                'AND' => [
                    'lecture_idx' => $lecture_id,
                    'student_name' => $_SESSION['userdata']['realname'],
                    'serial' => $_SESSION['userdata']['serial']
                ]
            ])) > 0;

        if (!$already_apply) {
            $this->medoo->insert('students', [
                'lecture_idx' => $lecture_id,
                'student_name' => $_SESSION['userdata']['realname'],
                'grade' => $_SESSION['userdata']['grade'],
                'clazz' => $_SESSION['userdata']['clazz'],
                'number' => $_SESSION['userdata']['number'],
                'serial' => $_SESSION['userdata']['serial'],
            ]);

            $response_message = $this->container->location->back('신청이 완료되었습니다.');
        }
    }

    $response_message = $this->container->location->back('더이상 신청할 수 없습니다.');
    return $response->write($response_message);
});

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

    return $this->pug->render(__DIR__ . '/../templates/layouts/teachers_request.pug', $options);
})->add($login_check);

/**
 * 강사 신청 DB 처리 라우팅
 */

$app->post('/teachers/request', function ($request, $response, $args) {
    return $response;
});

/**
 * 로그인 라우팅
 */

$app->get('/login', function ($request, $response, $args) {
    return $this->pug->render(__DIR__ . '/../templates/login.pug');
});

$app->get('/debug', function ($request, $response, $args) {
    var_dump($_SESSION);

    return $response;
});

/**
 * 로그인 포스팅
 */
$app->post('/login', function ($request, $response, $args) {
    $arguments = $request->getParsedBody();

    $id = $arguments['id'];
    $password = $arguments['password'];

    $result = $this->dimigo->user_exist($this, $id, $password, new class
    {
        public function onSuccess($result)
        {
            if ($result->user_type != 'S') {
                $response_message = $this->container->location->back('학생 이외의 레벨은 로그인을 지원하지 않습니다.');
                $this->container->response->write($response_message);

                return;
            }


            $response_message = $this->container->location->home();
            $this->container->response->write($response_message);

            // userdata fill
            $_SESSION['userdata']['realname'] = $result->name;
            $_SESSION['userdata']['username'] = $result->username;

            $result_stduent = $this->container->dimigo->fetch_student_info($this, $result->username, new class
            {
                public function onSuccess($result)
                {
                    $_SESSION['userdata']['gender'] = $result->gender;
                    $_SESSION['userdata']['grade'] = $result->grade;
                    $_SESSION['userdata']['class'] = $result->class;
                    $_SESSION['userdata']['number'] = $result->number;
                    $_SESSION['userdata']['serial'] = $result->serial;

                }

                public function onFailed($status, $error_name, $message)
                {
                    $response_message = $this->container->location->back('예상치 못한 오류가 발생했습니다.');
                    $this->container->response->write($response_message);
                }
            });
        }

        public function onFailed($status, $error_name, $message)
        {
            $response_message = $this->container->location->back('회원정보가 일치하지 않습니다.');
            $this->container->response->write($response_message);
        }
    });

    return $response;
});

/**
 *  로그아웃
 *  세션 제거로 처리.
 *
 * @author CodeRi13 <ruto1924@gmail.com>
 * @since  2016.12.30
 */
$app->get('/logout', function ($request, $response, $args) {
    session_destroy();

    $response->getBody()->write('<script>location.href="/"</script>');
    return $response;
});