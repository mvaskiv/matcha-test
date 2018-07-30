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

}
