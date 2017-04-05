<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// view renderer
$container['server'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['util'] = function ($c) {
    return new class($c) {
        public function __construct($con) {
            $this->c = $con;
        }

        public function add_option(&$options) {
            if(!isset($_SESSION['userdata'])) return;

            $userdata = $_SESSION['userdata'];
            if(isset($userdata['username']) && !empty($userdata['username'])) {
                $options['username'] = $userdata['realname'];
            }
        }

        public function check_manager(&$options) {
            if(!isset($_SESSION['userdata'])) return;

            $userdata = $_SESSION['userdata'];
            if(isset($userdata['manager']) && !empty($userdata['manager'])) {
                $options['manager'] = true;
            }
        }
    };
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];

    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['medoo'] = function ($c) {
    $settings = $c->get('settings')['medoo'];

    $database = new \Medoo\Medoo($settings);
    return $database;
};

// pug
$container['pug'] = function ($c) {
    $pug = new Pug\Pug(array(
        'prettyprint' => true,
        'extension' => '.pug',
        'basedir' => __DIR__ . '/templates',
        'cache' => './../cache'
    ));

    return $pug;
};

$container['location'] = function ($c) {
    $location = new class {
        public function back($message = 'default message') {
            $go_back = '<script>
                alert("' . $message . '");
                window.onload = function() { history.back(); }
            </script>';

            return $go_back;
        }

        public function home() {
            $go_home = '<script>
                window.onload = function() { location.href="/" }
            </script>';

            return $go_home;
        }

        public function go($url = '/', $message = 'default message') {
            $go_home = "<script>
                alert(\"$message\");
                window.onload = function() { location.href='$url' }
            </script>";

            return $go_home;
        }
    };

    return $location;
};

$container['dimigo'] = function ($c) {
    $dimigo = new class($c) {
        function __construct($c) {
            $this->c = $c;
        }

        function curl_ready() {
            
            $settings = $this->c->get('settings')['dimiapi'];

            $id = $settings['api_id'];
            $pw = $settings['api_pw'];

            $curl = new \Curl\Curl();
            $curl->setBasicAuthentication($id, $pw);

            return $curl;
        }

        public function fetch_student_info($container, $username, $callback) {
            // Check callback has methods
            $has_methods = 
                method_exists($callback, 'onSuccess') && 
                method_exists($callback, 'onFailed');
            
            // methods check
            if(!$has_methods) {
                return 'callback doesn\'t has callback methods';
            } else {
                $callback->container = $container;
            }

            $check_username = isset($username) && empty($username);
            if($check_username) {
                $callback->onFailed('0', 'NO INFO', 'Does not have info that used login.');

                return 'user info is blink';
            }

            $curl = $this->curl_ready();

            $curl->get('http://api.dimigo.org/v1/user-students/' . $username);

            $res = $curl->response;
            if(property_exists($res, 'user_id'))
                $callback->onSuccess($res);
            else
                $callback->onFailed($res->status, $res->name, $res->message);

            return $res;
        }

        public function user_exist($container, $user_id, $user_password, $callback) {
            // Check callback has methods
            $has_methods = 
                method_exists($callback, 'onSuccess') && 
                method_exists($callback, 'onFailed');
            
            // methods check
            if(!$has_methods) {
                return 'callback doesn\'t has callback methods';
            } else {
                $callback->container = $container;
            }

            // null check
            $check_id = isset($user_id) && empty($user_id);
            $check_password = isset($user_password) && empty($user_password);
            if($check_id && $check_password) {
                $callback->onFailed('0', 'NO INFO', 'Does not have info that used login.');

                return 'user info is blink';
            }

            // Login
            $curl = $this->curl_ready();

            $curl->get(
                'http://api.dimigo.org/v1/users/identify?username=' . $user_id . '&password=' . $user_password
            );

            // Result
            $res = $curl->response;
            if(property_exists($res, 'id'))
                $callback->onSuccess($res);
            else
                $callback->onFailed($res->status, $res->name, $res->message);

            return $curl->response;
        }
    };

    return $dimigo;
};