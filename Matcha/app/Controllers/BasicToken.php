<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;

class BasicToken{
  protected $_key = "example_key";

  function generate($id_user){
    $token = array(
      "id" => $id_user,
      "time" => (time() + 30 * 24 * 60 * 60)
    );
    $jwt = JWT::encode($token, $this->_key);
    $decoded = JWT::decode($jwt, $this->_key, array('HS256'));
	return ($jwt);
  }

  function check($token, $id){
    if (!$token)
      return false;
    $decoded = JWT::decode($token, $this->_key, array('HS256'));
    $decoded_array = (array) $decoded;
    if (!isset($decoded_array['id']) || !isset($decoded_array['time']))
       return false;
    if ($decoded_array['id'] == $id && $decoded_array['time'] > time()){
       return true;
     }
    return false;
  }

  function update($token){
    $decoded = JWT::decode($token, $this->_key, array('HS256'));
    $decoded_array = (array) $decoded;
    $decoded_array['time'] = time() + 2 * 60 * 60;
    $jwt = JWT::encode($decoded_array, $this->_key);
    return ($jwt);
  }
}
