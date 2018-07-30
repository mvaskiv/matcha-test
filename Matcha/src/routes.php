<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = 'localhost';
$config['db']['user']   = 'root';
$config['db']['pass']   = '459512144';
$config['db']['dbname'] = 'match';

$app = new \Slim\App(['settings' => $config]);

$app->post('/login', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $mysql_data = [];
    $mysql_data['login'] = filter_var($data['login'], FILTER_SANITIZE_STRING);
    $mysql_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);

    //$response->getBody()->write("Hello, ".$mysql_data['login'].", ".$mysql_data['password']);
    $rt = array('name' => 'Bob', 'age' => 40);
    //$newResponse = $oldResponse->withJson($data);
    return $response->withJson($rt);
});
$app->run();
