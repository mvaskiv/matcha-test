<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
  'settings' => [
      'displayErrorDetails' => true,
    ]
]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['upload_directory'] = __DIR__ . '/uploads';

$container['db'] = function ($container) {
    $db = $container['settings']['db'];
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['RegistrationController'] = function($container){
  return new \App\Controllers\RegistrationController;
};

$container['LoginController'] = function($container){
  return new \App\Controllers\LoginController;
};

$container['MyprofileController'] = function($container){
  return new \App\Controllers\MyprofileController;
};

$container['UsersController'] = function($container){
  return new \App\Controllers\UsersController;
};

$container['UploadController'] = function($container){
  return new \App\Controllers\UploadController;
};

require_once __DIR__.'/../app/routes.php';
