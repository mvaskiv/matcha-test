<?php
try {
$dsn = 'mysql:host=localhost';
$user = 'root';
$password = 'qweqwe';
  $conn = new PDO($dsn, $user, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->exec("CREATE DATABASE IF NOT EXISTS matcha_db;");
  $conn->exec("Use matcha_db;");
  $conn->exec("CREATE TABLE `User` (`f_name` VARCHAR(15), `l_name` VARCHAR(15),
      `u_name` VARCHAR(15), `gender` ENUM('M', 'F'), `sex_preference` ENUM('M', 'F'),
       `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `biography` TEXT, `tags` TEXT,
       `email` VARCHAR(20), `password` VARCHAR(50), `date` DATE)");
  $conn->exec("CREATE TABLE `fotos` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `id_user` INT,
                `all_foto` TEXT, `avatar` TEXT)");
  $conn->exec("CREATE TABLE `notifications` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `id_user` INT,
      `text` TEXT, `new` int(1), `type` ENUM('like', 'like_back', 'dislike', 'new_msg', 'checked'),
      `from` INT,`date` DATETIME DEFAULT CURRENT_TIMESTAMP)");
   $conn->exec("CREATE TABLE `chats` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `user1` INT, `user2` INT)");
   $conn->exec("CREATE TABLE `messages` (`chat_id` INT,
   `sender` INT, `recipient` INT, `date` DATETIME DEFAULT CURRENT_TIMESTAMP, `msg` TEXT)");
  echo "Connection sucssec\n";
} catch (PDOException $e) {
  echo 'Conection is fail ' . $e->getMessage();
}
 ?>
