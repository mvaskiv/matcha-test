<?php

namespace App\Controllers;

use App\Controllers\BasicToken;
use PDO;

class LoginController extends BasicToken {
  private $rt = array();
  private $parsedBody;
  protected $conn;

  protected function init(){
    $var = require_once 'sqlconf.php';
    $this->conn = new PDO($var['dsn'], $var['user'], $var['password']);
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function insert($request, $response){
    $this->parsedBody = $request->getParsedBody();
    $this->init();
    if (!isset($this->parsedBody['login']) || !isset($this->parsedBody['password'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no login or password';
      return json_encode($this->rt);
    }
    if ($this->exec())
      $this->rt['status'] = 'ok';
    return json_encode($this->rt);
  }

  private function exec(){
    $stmt = $this->conn->prepare("SELECT * FROM user WHERE `email` = ? OR `u_name` = ?");
    $login = $this->parsedBody['login'];
    if ($stmt->execute([$login, $login])){
      $row = $stmt->fetch();
      if (!isset($row['email'])){
        $this->rt['status'] = 'ko';
        $this->rt['error'] = 'no user';
        return false;
    }
    else if (hash('ripemd160', $this->parsedBody['password']) != $row['password']){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'password dose not match';
      return false;
    }
  }
  $this->rt['status'] = 'ok';
  $this->rt['id'] = $row['id'];
  $this->rt['token'] = $this->generate($row['id']);
  return true;
}

}
