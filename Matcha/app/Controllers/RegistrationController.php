<?php

namespace App\Controllers;

use PDO;
use App\sqlconf;


class RegistrationController{
    private $parsedBody;
    private $f_name;
    private $l_name;
    private $u_name;
    private $gender;
    private $sex_preference;
    private $biography;
    private $tags;
    private $email;
    private $psw;
    private $date;
    private $rt = array();
    protected $conn;

    protected function init(){
      $var = require_once 'sqlconf.php';
      $this->conn = new PDO($var['dsn'], $var['user'], $var['password']);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insert($request, $response){
      $this->init();
      $this->parsedBody = $request->getParsedBody();
      if ($this->ifis()){
        $this->parse();
        $this->exec();
      }
      return json_encode($this->rt);
    }

    private function parse(){
       if (isset($this->parsedBody['f_name']))
          $this->f_name = $this->parsedBody['f_name'];
       if (isset($this->parsedBody['l_name']))
          $this->l_name = $this->parsedBody['l_name'];
       if (isset($this->parsedBody['u_name']))
          $this->u_name = $this->parsedBody['u_name'];
       if (isset($this->parsedBody['gender']))
          $this->gender = $this->parsedBody['gender'];
       if (isset($this->parsedBody['sex_preference']))
          $this->sex_preference = $this->parsedBody['sex_preference'];
       if (isset($this->parsedBody['biography']))
          $this->biography = $this->parsedBody['biography'];
       if (isset($this->parsedBody['tags']))
          $this->tags = $this->parsedBody['tags'];
       if (isset($this->parsedBody['email']))
          $this->email = $this->parsedBody['email'];
       if (isset($this->parsedBody['password']))
          $this->psw = hash('ripemd160', $this->parsedBody['password']);
       if (isset($this->parsedBody['date']))
          $this->date = $this->parsedBody['date'];
    }

    private function exec(){
      $stmt = $this->conn->prepare("INSERT INTO `user` (`f_name`, `l_name`, `u_name`,
                  `gender`, `sex_preference`, `biography`, `tags`, `email`,
                   `password`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$this->f_name, $this->l_name, $this->u_name,
                  $this->gender, $this->sex_preference, $this->biography,
                  $this->tags, $this->email, $this->psw, $this->date]);
      $this->rt['status'] = 'ok';
    }

    private function ifis(){
      if (isset($this->parsedBody['email'])  && isset($this->parsedBody['password'])){
        if ($this->notexist()){
          return true;
        }
        else{
          $this->rt['status'] = 'ko';
          $this->rt['error'] = 'email exist';
          return false;
        }
      }
      else {
        $this->rt['status'] = 'ko';
        $this->rt['error'] = 'no email or password';
      }
      return false;
    }

    private function notexist(){

      $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ?");

      $email = $this->parsedBody['email'];
      if ($stmt->execute([$email])){
        $row = $stmt->fetch();
        if (isset($row['email'])){
          return false;
        }
      }
      return true;
    }
}
