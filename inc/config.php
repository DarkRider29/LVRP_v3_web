<?php

$host = '127.0.0.1';
$port = '3306';
$bdd1 = 'lvrp';
$root = 'root';
$pass = '';


try
{
$bdd = new PDO("mysql:host=$host;dbname=$bdd1", "$root", "$pass");
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}
	 $db = mysql_connect($host, $root, $pass);
		mysql_select_db($bdd1,$db);  
		mysql_query("SET NAMES UTF8");
	

 if(!isset($_SESSION['login'])){  } else { 

$login = $_SESSION['login'] ;
$result = mysql_query("SELECT * FROM lvrp_users WHERE Name = '$login'");
			   $row = mysql_fetch_array($result) or die(mysql_error());
}
?>