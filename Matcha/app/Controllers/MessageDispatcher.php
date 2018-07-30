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
    if ($this->exec($this->parsedBody['to'], $this->parsedBody['id'], $this->parsedBody['msg']))
      $this->rt['status'] = 'ok';
    $this->rt['token'] = $this->update($this->parsedBody['token']);
  } catch (\Exception $e){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'token is broken';
      return json_encode($this->rt);
  }
    return json_encode($this->rt);
  }

//   public function possible_chat($user1, $user2){
//     $tmp = array(0, 0);
  
//     $stmt = $this->conn->prepare("SELECT FROM `chats` WHERE (`user1` = {$user1} AND `user2` = {$user2}) OR (`user1` = {$user2} AND `user2` = {$user1})");
//     if ($stmt->execute()){
//       $row = $stmt->fetch();
//       if (isset($row['id']))
//         return array('status' => 'ok', 'chat' => $row['id']);
//     }
//     $stmt = $this->conn->prepare("SELECT DISTINCT `id_user` FROM `notifications` WHERE (`type` = 'like' OR `type` = 'like_back') AND ((`id_user` = ? AND `from` = ?) OR (`id_user` = ? AND `from` = ?))");
//     if ($stmt->execute([$user1, $user2, $user2, $user1])){
//       while (($row = $stmt->fetch())){
//         if ($row['id_user'] == $user1)
//           $tmp[1] = 1;
//         else if ($row['id_user'] == $user2)
//           $tmp[2] = 1;
//       }
//     }
//     if ($tmp[0] === 1 && $tmp[1] === 1)
//       return array('status' => 'ok', 'chat' => 0);
//     return array('status' => 'ko');
//   }
  
  public function write_to_db($to, $from, $msg){
    
  }

  private function exec($to, $from, $msg) {
    $this->init();
    $messange = json_decode($msg, true);
    // $tmp = $this->possible_chat($to, $from);
    // if ($tmp['status'] === 'ko')
    //   return false;
    // if ($tmp['chat'] == 0){
      $stmt = $this->conn->prepare("INSERT INTO `chats` (`user1`, `user2`) VALUES({$to}, {$from})");
      $stmt->execute();
      $row = $stmt->fetch();
      $stmt = $this->conn->prepare("INSERT INTO `messages` (`chat_id`, `sender`, `recipient`, `msg`) VALUES(?, ?, ?, ?)");
      $stmt->execute([$row['id'], $from, $to, $messange['message']]);
    // }
    $stmt = $this->conn->prepare("INSERT INTO `messages` (`chat_id`, `sender`, `recipient`, `msg`) VALUES(?, ?, ?, ?)");
    $stmt->execute([$row['id'], $from, $to, $messange['message']]);
    return true;
}
}
