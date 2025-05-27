<?php
$host = '127.0.0.1';
$db   = 'restaurante_db';
$user = 'root';
$pass = '';
$opts = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION];
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try { $pdo = new PDO($dsn, $user, $pass, $opts); }
catch (PDOException $e) { die('DB Error: ' . $e->getMessage()); }
?>