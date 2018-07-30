<?php

namespace App\Controllers;

use App\Controllers\BasicToken;
use PDO;

class UsersController extends BasicToken {
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

    if (!isset($this->parsedBody['sort']) || !isset($this->parsedBody['start']) || !isset($this->parsedBody['number'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no sort or start or number';
      return json_encode($this->rt);
    }
    if ($this->parsedBody['sort'] == 'age' && (!isset($this->parsedBody['start_age']) || !isset($this->parsedBody['end_age']))){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no start_age or end_age';
      return json_encode($this->rt);
    }
    if ($this->parsedBody['sort'] == 'gender' && !isset($this->parsedBody['gender'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no gender';
      return json_encode($this->rt);
    }
    if ($this->parsedBody['sort'] == 'age_gender' && (!isset($this->parsedBody['gender']) || !isset($this->parsedBody['start_age']) || !isset($this->parsedBody['end_age']))){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no gender or start_age or end_age';
      return json_encode($this->rt);
    }
    $this->exec();
    return json_encode($this->rt);
  }

  private function exec(){
    $stmt = NULL;
    $usr = array();
    $start = intval($this->parsedBody['start']);
    $number = intval($this->parsedBody['number']);
    $stmtq = $this->conn->prepare("SELECT * FROM user");
    $stmtq->execute();
    $row_q = $stmtq->rowCount();
    if ($this->parsedBody['sort'] == 'unsort')
      $stmt = $this->conn->prepare("SELECT user.f_name, user.l_name, user.u_name, user.id, user.gender, fotos.all_foto, fotos.avatar FROM user LEFT JOIN fotos ON fotos.id_user=user.id LIMIT $start, $number");
    else if ($this->parsedBody['sort'] == 'age'){
      $start_age = $this->parsedBody['start_age'];
      $end_age = $this->parsedBody['end_age'];
      $stmt = $this->conn->prepare("SELECT
        user.f_name, user.l_name, user.u_name, user.id, user.gender, fotos.all_foto, fotos.avatar
         FROM user LEFT JOIN fotos ON fotos.id_user=user.id
        WHERE TIMESTAMPDIFF(YEAR, `date`, CURDATE()) > $start_age and TIMESTAMPDIFF(YEAR, `date`, CURDATE()) < $end_age  LIMIT $start, $number");
      }
      else if ($this->parsedBody['sort'] == 'gender'){
        $gender = $this->parsedBody['gender'];
        $stmt = $this->conn->prepare("SELECT
          user.f_name, user.l_name, user.u_name, user.id, user.gender, fotos.all_foto, fotos.avatar
           FROM user LEFT JOIN fotos ON fotos.id_user=user.id
          WHERE `gender` = '$gender' LIMIT $start, $number");
      }
      else if ($this->parsedBody['sort'] == 'age_gender'){
        $start_age = $this->parsedBody['start_age'];
        $end_age = $this->parsedBody['end_age'];
        $gender = $this->parsedBody['gender'];
        $stmt = $this->conn->prepare("SELECT
          user.f_name, user.l_name, user.u_name, user.id, user.gender, fotos.all_foto, fotos.avatar
           FROM user LEFT JOIN fotos ON fotos.id_user=user.id
          WHERE TIMESTAMPDIFF(YEAR, `date`, CURDATE()) > $start_age and TIMESTAMPDIFF(YEAR, `date`, CURDATE()) < $end_age
          and `gender` = '$gender'  LIMIT $start, $number");
        }
      else {
        $this->rt['status'] = 'ko';
        $this->rt['error'] = 'sort error';
        return ;
      }
    if ($stmt->execute()){
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // if (!empty($row['all_foto'] && !empty($row['avatar']))){
        //   $tmp = unserialize($row['all_foto']);
        //   if (isset($tmp[inval($row['avatar'])]))
        //     $row['avatar'] = $tmp[inval($row['avatar'])];
        //   }
        //   else
        //     $row['avatar'] = 'error';

          unset($row['all_foto']);
        array_push($usr, $row);
      }
    }
    $this->rt['data'] = $usr;
    $this->rt['status'] = 'ok';
    if (($this->parsedBody['number'] > $row_q) || ($this->parsedBody['number'] > count($usr))) {
        $this->rt['status'] = 'dbEnd';
    }
  return true;
}
}
