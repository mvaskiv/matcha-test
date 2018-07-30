<?php
try {
$dsn = 'mysql:host=localhost';
$user = 'root';
$password = '459512144';
// echo $dsn;
// echo $user;
// echo $password;

  $conn = new PDO($dsn, $user, $password);
  
  // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // $conn->exec("CREATE DATABASE IF NOT EXISTS matcha_db;");
  // $conn->exec("Use testdb;");
} catch (PDOException $e) {
  echo 'Conection is fail ' . $e->getMessage();
}
 ?>
