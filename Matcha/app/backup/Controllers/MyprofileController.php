<?php

namespace App\Controllers;

use App\Controllers\BasicToken;
use PDO;

class MyprofileController extends BasicToken {
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
    if (!isset($this->parsedBody['id']) || !isset($this->parsedBody['token'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token';
      return json_encode($this->rt);
    }
    try{
        if  (!$this->check($this->parsedBody['token'], $this->parsedBody['id'])){
          $this->rt['status'] = 'ko';
          $this->rt['error'] = 'wrong token';
          return json_encode($this->rt);
        }
    if ($this->exec())
      $this->rt['status'] = 'ok';
    $this->rt['token'] = $this->update($this->parsedBody['token']);
  } catch (\Exception $e){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'token is broken';
      return json_encode($this->rt);
  }
    return json_encode($this->rt);
  }

  private function exec(){
    $this->init();
    $stmt = $this->conn->prepare("SELECT user.f_name, user.l_name, user.u_name, user.id, user.gender, user.biography, user.tags, user.date, user.sex_preference, fotos.all_foto, fotos.avatar FROM user LEFT JOIN fotos ON fotos.id_user=user.id WHERE user.id = ?");
    if (isset($this->parsedBody['viewId'])) {
      $id = $this->parsedBody['viewId'];
    } else {
      $id = $this->parsedBody['id'];
    }
    if ($stmt->execute([$id])){
      $row = $stmt->fetch();
      if (!isset($row['id'])){
        $this->rt['status'] = 'ko';
        $this->rt['error'] = 'no id';
        return false;
    }
    unset($row['password']);
    array_push($this->rt, $row);
    return true;
  }
}
}
