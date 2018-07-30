<?php
namespace App\Retchet;

use App\Controllers\BasicToken;
use App\Controllers;
use PDO;

class UserIndetify extends BasicToken {
  public $pool = array();
  // private $conn;

  // public function __construct(){
  //   $var = include "sqlconf.php";
  //   $this->conn = new PDO('mysql:host=localhost;dbname=matcha_db', $var['user'], $var['password']);
  //   $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // }

  public function addUser($id, $chat_id){
    $tmp = array(
      'id' => $id,
      'chat_id' => $chat_id
    );
    array_push($this->pool, $tmp);
    $sql = include "sqlconf.php";
    print_r($sql);
  }

  public function dropUser($chat_id){
    $del = array_search(array('chatt_id' => $chat_id), $this->pool);
    echo $del." find\n";
    for ($i = 0; $i < count($this->pool); $i++){
      if ($this->pool[$i]['chat_id'] == $chat_id){
        unset($this->pool[$i]);
        array_slice($this->pool, 0, count($this->pool));
        break;
      }
  }
}

public function checkInput(array $tmp){
  if (!isset($tmp['id']) || !isset($tmp['token']) || !isset($tmp['to'])
        || !isset($tmp['status']))
    return false;
  if (!$this->check($tmp['token'], $tmp['id']))
    return false;
  try{
    return $this->update($tmp['token']);
  } catch (\Exception $e){
    return false;
  }
}

public function receiver($to){
  foreach($this->pool as $usr){
    if ($usr['id'] === $to)
      return $usr['chat_id'];
  }
  return false;
}
}
