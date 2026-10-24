<?php
// Fill these in from cPanel > MySQL Databases
define('DB_HOST','localhost');
define('DB_NAME','cpaneluser_dbname');
define('DB_USER','cpaneluser_dbuser');
define('DB_PASS','your-db-password');

session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
session_start();
$db=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,
  [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
