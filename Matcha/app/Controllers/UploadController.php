<?php
namespace App\Controllers;

use App\Controllers\BasicToken;
use Slim\Http\UploadedFile;
use PDO;



class UploadController extends BasicToken {
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
    if (!isset($this->parsedBody['id']) || !isset($this->parsedBody['token'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token';
      return json_encode($this->rt);
    }
    if (!isset($this->parsedBody['img'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no img';
      return json_encode($this->rt);
    }
    if (!$this->token())
      return json_encode($this->rt);
    $imageData = $this->parsedBody['img'];
    $arr = preg_split('/base64,/', $imageData);
    $filteredData=substr($imageData, strpos($imageData, ",") + 1);
    $unencodedData=base64_decode($filteredData);
    $name = $this->getImgName($arr[0]);
    file_put_contents(__DIR__.'/../../uploads/'.$name, $unencodedData);
    $this->writeToDB($name, $this->parsedBody['id']);
    $this->rt['status'] = 'ok';
    $this->rt['index'] = $name;
    return json_encode($this->rt);
  }

  public function writeToDB($name, $user_id){
    $stmt = $this->conn->prepare("SELECT * FROM `fotos` WHERE `id_user` = ?");
    if ($stmt->execute([$user_id])){
      $row = $stmt->fetch();
      if (empty($row['all_foto'])){
        $ser_str = array();
                array_push($ser_str, $name);
        $stmt = $this->conn->prepare("INSERT INTO `fotos` (`id_user`, `all_foto`) VALUES(?, ?)");
        $stmt->execute([$user_id, json_encode($ser_str)]);
      }
      else {
        $ser_str = json_decode($row['all_foto']);
        $tmp = 0;
        foreach ($ser_str as $key => $value) {
          $tmp++;
        }
        array_push($ser_str, $name);
        $stmt = $this->conn->prepare("UPDATE `fotos` SET `all_foto` = ? WHERE `id_user` = ?");
        $stmt->execute([json_encode($ser_str), $user_id]);
      }
    }
  }

  public function getImgName($str){
    $arr = preg_split('/\//', $str);
    $arr = preg_split('/;/', $arr[1]);
    $dir = __DIR__.'/../../uploads/';
    $max = 0;
    if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
              if ($max < intval($file))
                $max = intval($file);
        }
        closedir($dh);
      }
    }
    $max++;
    return strval($max).".".$arr[0];
  }
  private function token(){
    try{
        if  (!$this->check($this->parsedBody['token'], $this->parsedBody['id'])){
          $this->rt['status'] = 'ko';
          $this->rt['error'] = 'wrong token';
          return false;
        }
    $this->rt['token'] = $this->update($this->parsedBody['token']);
  } catch (\Exception $e){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'token is broken';
      return false;
  }
  return true;
  }

  public function avatar($request, $response){
    $this->parsedBody = $request->getParsedBody();
    $this->init();
    if (!isset($this->parsedBody['photo']) || !isset($this->parsedBody['token']) || !isset($this->parsedBody['id'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token or photo';
      return json_encode($this->rt);
    }
    if (!$this->token())
      return json_encode($this->rt);
    $stmt = $this->conn->prepare("UPDATE `fotos` SET `avatar` = ? WHERE `id_user` = ?");
    $stmt->execute([$this->parsedBody['photo'], $this->parsedBody['id']]);
    $this->rt['status'] = 'ok';
    return json_encode($this->rt);
  }

  public function delete($request, $response){
    $this->parsedBody = $request->getParsedBody();
    $this->init();
    if ((intval($this->parsedBody['delphoto']) < 0) || !isset($this->parsedBody['token']) || !isset($this->parsedBody['id'])) {
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token or photo';
      return json_encode($this->rt);
    }
    $stmt = $this->conn->prepare("SELECT * FROM `fotos` WHERE `id_user` = ?");
    if ($stmt->execute([$this->parsedBody['id']])){
      $row = $stmt->fetch();
      
        $ser_str = json_decode($row['all_foto']);
        array_splice($ser_str, $this->parsedBody['delphoto'], 1);
        $stmt = $this->conn->prepare("UPDATE `fotos` SET `all_foto` = ? WHERE `id_user` = ?");
        $stmt->execute([json_encode($ser_str), $this->parsedBody['id']]);
        $this->rt['status'] = $this->parsedBody['delphoto'];
    }
    return json_encode($this->rt);
  }


  // MESSAGE PRE-DISPATCH



  public function send($request, $response){
    $this->parsedBody = $request->getParsedBody();
    if (!isset($this->parsedBody['id']) || !isset($this->parsedBody['token'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token';
      return json_encode($this->rt);
    }
    // try{
    //     if  (!$this->check($this->parsedBody['token'], $this->parsedBody['id'])){
    //       $this->rt['status'] = 'ko';
    //       $this->rt['error'] = 'wrong token: ' . $this->parsedBody['token'];
    //       return json_encode($this->rt);
    //     }
    if ($this->exec($this->parsedBody['to'], $this->parsedBody['id'], $this->parsedBody['msg']))
      $this->rt['status'] = 'ok';
    $this->rt['token'] = $this->update($this->parsedBody['token']);
  // } catch (\Exception $e){
  //     $this->rt['status'] = 'ko';
  //     $this->rt['error'] = 'token is broken';
  //     return json_encode($this->rt);
  // }
    return json_encode($this->rt);
  }

  private function exec($to, $from, $msg) {
    $this->init();
    $messange = json_decode($msg, true);
    // $tmp = $this->possible_chat($to, $from);
    // if ($tmp['status'] === 'ko')
    //   return false;
    $stmt = $this->conn->query("SELECT id from `chats` WHERE user1 = '$from' AND user2 = '$to' OR user2 = '$from' AND user1 = '$to'");
    $row = $stmt->fetch();
    $id = $row['id'];
    if (!$id){
      $stmt = $this->conn->prepare("INSERT INTO `chats` (`user1`, `user2`) VALUES({$to}, {$from})");
      $stmt->execute();
      $id = $this->conn->lastInsertId();
      $stmt = $this->conn->prepare("INSERT INTO `messages` (`chat_id`, `sender`, `recipient`, `msg`) VALUES(?, ?, ?, ?)");
      $stmt->execute([$id, $from, $to, $messange['message']]);
    } else {
      $stmt = $this->conn->prepare("INSERT INTO `messages` (`chat_id`, `sender`, `recipient`, `msg`) VALUES(?, ?, ?, ?)");
      $stmt->execute([$id, $from, $to, $messange['message']]);
    }
    $this->rt['id'] = $id;
    return true;
  }



  // GET CHATS

  public function getchats($request, $response) {
    $this->parsedBody = $request->getParsedBody();
    if (!isset($this->parsedBody['id']) || !isset($this->parsedBody['token'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token';
      return json_encode($this->rt);
    }
    if ($this->exec_getChats($this->parsedBody['id'])) {
      $this->rt['status'] = 'oke';
    }
    $this->rt['token'] = $this->update($this->parsedBody['token']);
    return json_encode($this->rt);
  }

  private function exec_getChats($id) {
    $this->init();
    $stmt = NULL;
    $chats = array();
    $stmtq = $this->conn->prepare("SELECT * FROM chats WHERE user1 = '$id' OR user2 = '$id'");
    $stmtq->execute();
    $row_q = $stmtq->rowCount();
    $stmt = $this->conn->prepare("SELECT * FROM chats WHERE user1 = '$id' OR user2 = '$id'");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    // if ($stmt->execute()) {
      foreach($rows as $row) {
        $uid = $row['user1'] === $id ? $row['user2'] : $row['user1'];
        $stmtu = $this->conn->query("SELECT user.f_name, fotos.avatar from `user` LEFT JOIN `fotos` ON fotos.id_user = user.id WHERE user.id = '$uid'");
        $uinfo = $stmtu->fetch();
        $row['data'] = $uinfo;
  
        array_push($chats, $row);
      }
    // }
    $this->rt['data'] = $chats;
    $this->rt['status'] = 'oke';
  return true;
}

// SET AND GET FIREBASE TOKEN

  public function fb_token($request, $response) {
    $this->parsedBody = $request->getParsedBody();
    if ($this->exec_firebaseToken($this->parsedBody['action'], $this->parsedBody['fbtoken'], $this->parsedBody['id'])) {
      $this->rt['status'] = 'ok';
    }
    return json_encode($this->rt);
  }

  private function exec_firebaseToken($action, $token, $id) {
    $this->init();
    if ($action === 'set') {
      // $stmt = $this->conn->query("SELECT * FROM `user` WHERE id = $id");
      // $row = $stmt->fetch();
      // if (empty($row['token'])){
      //   $stmt = $this->conn->prepare("INSERT INTO `user` (`token`) VALUE '$token' WHERE id = $id");
      //   $stmt->execute();
      //   return true;
      // }
      // else {
        $stmt = $this->conn->prepare("UPDATE `user` SET fbtoken = '$token' WHERE id = $id");
        $stmt->execute();
        return true;
      
    } else if ($action === 'get') {
      $stmt = $this->conn->query("SELECT * FROM `user` WHERE id = $id");
      $row = $stmt->fetch();
      $this->rt['data'] = $row['fbtoken'];
      return true;
    }
    return false;
  }

// GET MESSAGES 

  public function messagehistory($request, $response) {
    $this->parsedBody = $request->getParsedBody();
    if (!isset($this->parsedBody['id']) || !isset($this->parsedBody['token'])){
      $this->rt['status'] = 'ko';
      $this->rt['error'] = 'no id or token';
      return json_encode($this->rt);
    }
    if ($this->exec_messageHistory($this->parsedBody['viewId'], $this->parsedBody['number'])) {
      $this->rt['status'] = 'oke' . $this->parsedBody['viewId'];
    }
    $this->rt['token'] = $this->update($this->parsedBody['token']);
    return json_encode($this->rt);
  }

  private function exec_messageHistory($chatid, $number) {
    $this->init();
    $stmt = NULL;
    $msg = array();
    $stmt = $this->conn->prepare("SELECT * FROM messages WHERE chat_id = '$chatid' ORDER BY date ASC LIMIT 0, $number");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    // if ($stmt->execute()) {
      foreach($rows as $row) {
        array_push($msg, $row);
      }
    // }
    $this->rt['data'] = $msg;
    $this->rt['status'] = 'ok';
    if (($this->parsedBody['number'] > $row_q) || ($this->parsedBody['number'] > count($usr))) {
        $this->rt['status'] = 'dbEnd';
    }
  return true;
  }
}
