<?php
#$job=mysql_connect("mysql.fhero.net","u766388232_new","spiral");
#mysql_query("set names 'utf8'");
#mysql_select_db("u766388232_new");
#$db=new mysqli('localhost','root','','test'); 
$dsn = 'mysql:dbname=test;host=localhost';
$user = 'root';
$password = '';
$db = new PDO($dsn, $user, $password) or die("Error Mysql!");
?>