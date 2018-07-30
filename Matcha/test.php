<?php
use \Firebase\JWT\JWT;

require_once __DIR__ . '/vendor/autoload.php';

$key = "example_key";
$token = array(
  $login = "vasi"
);

/**
 * IMPORTANT:
 * You must specify supported algorithms for your application. See
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * for a list of spec-compliant algorithms.
 */
$jwt = JWT::encode($token, $key);
echo $jwt."\n";
$decoded = JWT::decode($jwt, $key, array('HS256'));

print_r($decoded);
