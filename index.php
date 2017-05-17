<?php
session_start();

define('PHP_FIREWALL_REQUEST_URI', strip_tags( $_SERVER['REQUEST_URI'] ) );
define('PHP_FIREWALL_ACTIVATION', true );
if ( is_file( @dirname(__FILE__).'/firewall/firewall.php' ) )
include_once( @dirname(__FILE__).'/firewall/firewall.php' );
	require_once('inc/config.php');
	 require_once('inc/functions.php');
	 require_once('inc/header.php');

	    	 
	 	 
	 
				
define('SECU', true);
if (empty($_GET['p'])) $_GET['p'] = 'home';

switch($_GET['p']){
case "404":
case "home":
case "functions":
case "menu_droite":
case "header":
case "footer":
case "config":
case "regles":
case "equipe":
case "tokens":
case "boutique":
case "definitions":
case "crime":
case "vote":
case "argent":
case "telechargement":
case "inscription":
case "login":
case "logout":
case "profil":
case "faction":
case "biens":
case "job":
case "inventaire":
case "vip":
case "casier":
case "apvote":
case "voter":
case "buytokensinvalid":
case "buytokensontrue":
case "buytokensvalid":
case "services":
case "valid":
case "buyfer":
case "buyargent":
case "buyor":
case "buydiamant":
case "connected":
case "level":
case "banjoueur":
case "news":
case "news_sup":
case "gestionfaction":
case "gestionfaction_edit":
case "gestionfaction_sup":

include("./pages/".$_GET['p'].".php");
break;

default:
include("./pages/404.php");
break;
}

     require_once('inc/menu_droite.php');
	 require_once('inc/footer.php');