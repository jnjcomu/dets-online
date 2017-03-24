# DET'S Online Apply System

## Requirement

- settings.php

### settings.php - example
```php
  <?php
  return [
      'settings' => [
          'displayErrorDetails' => true, // set to false in production
          'addContentLengthHeader' => false, // Allow the web server to send the content-length header

          // Renderer settings
          'renderer' => [
              'template_path' => __DIR__ . '/../templates/',
          ],

          'medoo' => [
              'database_type' => 'mysql',
              'database_name' => 'your_database',
              'server' => 'localhost',
              'username' => 'your_database',
              'password' => 'your_password',
              'charset' => 'utf8',
          ],

          // Monolog settings
          'logger' => [
              'name' => 'slim-app',
              'path' => __DIR__ . '/../logs/app.log',
              'level' => \Monolog\Logger::DEBUG,
          ],

          // Dimigo API
          'dimiapi' => [
              // If you want publish this application, you can use https://api.dimigo.hs.kr that be used product service.
              'host' => 'https://api.dimigo.org', 
              'api_id' => 'dimigoid_password',
              'api_pw' => 'dimigoapi_password',
          ],
      ],
  ];
```
