<?php

/**
 * 로그인 라우팅
 */

$app->get('/login', function ($request, $response, $args) {
    return $this->pug->render(__DIR__ . '/../../templates/login.pug');
});

$app->get('/debug', function ($request, $response, $args) {
    var_dump($_SESSION);
    var_dump($request->getUri());
    var_dump($_SERVER);
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
            if($result->photofile2 == '')
                $_SESSION['profile_pic'] = $result->photofile1;
            else
                $_SESSION['profile_pic'] = $result->photofile2;


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