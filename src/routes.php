<?php

/**
 * 소개 라우팅
 */

$app->get('/', function ($request, $response, $args) {
    $options = [
        'intro' => true,
        'title' => '디미고 Dets 신청 시스템',
    ];
    
    if(isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $options['username'] = $_SESSION['username'];
    }

    return $this->pug->render(__DIR__ . '/../templates/layouts/intro.pug', $options);
});

/**
 * 강좌 리스트 라우팅
 */

$app->get('/lectures', function ($request, $response, $args) {
    $options = [
        'lectures_l' => true,
        'title' => '디미고 Dets 신청 시스템 :: 강의 목록',
        'lectures' => [
            
        ]
    ];

    if(isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $options['username'] = $_SESSION['username'];
    }

    return $this->pug->render(__DIR__ . '/../templates/layouts/lectures_list.pug', $options);
});

$app->get('/lectures/[{lecture}]', function ($request, $response, $args) {
    $options = [
        'lectures_l' => true,
        'title' => '디미고 Dets 신청 시스템 :: ' . $args['lecture'],
        'lecture' => [
            'name' => '노드강의',
            'teacher_name' => '박머리',
            'teacher_info' => '2학년 2반 2번',
            'description' => '이 편지는 영국에서 시작되어 가나다라마사바 에베베베베베',
            'now' => '1',
            'max' => '30',
            'teacher_image' => 'http://www.gravatar.com/avatar/6f790b180d17908ba75f675f2e9d1fd7?d=identicon&s=40'
        ]
    ];

    return $this->pug->render(__DIR__ . '/../templates/layouts/lectures_detail.pug', $options);
});

/**
 * 강사 신청 라우팅
 */

$app->get('/teachers/request', function ($request, $response, $args) {
    $options = [
        'teachers_r' => true,
        'title' => '디미고 Dets 신청 시스템 :: 강사 신청',
    ];

    if(isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $options['username'] = $_SESSION['username'];
    }

    return $this->pug->render(__DIR__ . '/../templates/layouts/teachers_request.pug', $options);
});

/**
 * 강사 신청 DB 처리 라우팅
 */

$app->post('/teachers/request', function ($request, $response, $args) {
    $user_name = $_SESSION['userdata'];

    // $this->medoo->select('lectures', ['author_name' => ])

    return $response;
});

/**
 * 로그인 라우팅
 */

$app->get('/login', function ($request, $response, $args) {
    return $this->pug->render(__DIR__ . '/../templates/login.pug');
});

$app->post('/login', function ($request, $response, $args) {
    $arguments = $request->getParsedBody();

    $id = $arguments['id'];
    $password = $arguments['password'];

    $result = $this->dimigo->user_exist($this, $id, $password, new class {
        // PHP의 AWESOME한 스코프 법칙 때문에 $this를 
        public function onSuccess($result) {
            $response_message = $this->container->location->home();
            $this->container->response->write($response_message);

            $user_data = $_SESSION['userdata'];

            $user_data['realname'] = $result->name;
            $user_data['username'] = $result->username;

        }

        public function onFailed($status, $error_name, $message) {
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
 *  @author CodeRi13 <ruto1924@gmail.com>
 *  @since  2016.12.30
 */
$app->get('/logout', function($request, $response, $args) {
    $response->getBody()->write('잠시만 기다려주세요...');

    session_destroy();

    $response->getBody()->write('<script>location.href="/"</script>');
    return $response;
});