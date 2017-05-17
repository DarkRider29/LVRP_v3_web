<!DOCTYPE HTML>
<html lang="fr-gb" dir="ltr">


<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
<meta charset="utf-8" />
  <meta name="robots" content="index, follow" />
  <title>La Vie Roleplay</title>
  <link href="images/lvrp/icon.ico" rel="shortcut icon" type="image/x-icon" />
  <link rel="stylesheet" href="cache/template/widgetkit-d9390a5b-e9ac0d94.css" type="text/css" />
  <script type="text/javascript" src="cache/template/mootools-48cd395a.js"></script>
  <script type="text/javascript" src="cache/template/caption-8389dc0d.js"></script>
  <script type="text/javascript" src="cache/template/jquery-20cd9eb5.js"></script>
  <script type="text/javascript" src="cache/template/widgetkit-a1fb05c9-8e33faa0.js"></script>
  <script type="text/javascript" src="cache/template/js-image-slider.js"></script>
<link rel="apple-touch-icon-precomposed" href="templates/yoo_catalyst/ico.png" />
<link rel="stylesheet" href="cache/template/template-4444800e.css" />
<link rel="stylesheet" href="cache/template/js-image-slider.css" />
<link rel="stylesheet" href="cache/template/generic.css" />
<link rel="stylesheet" href="cache/template/lvrp.css" />
<script src="cache/template/template-fd6961b0.js"></script>
</head>

<body id="page" class="page sidebar-a-right sidebar-b-right isblog content- " data-config='{"twitter":0,"plusone":0}'>
 <script src="./cache/template/neige.js" type="text/javascript"></script> 

	<div id="block-toolbar">
		<div class="wrapper">
	
			<div id="toolbar" class="grid-block">
		
								<div class="float-left">
				
<time>
<?php
$date = date("d-m-Y");
$heure = date("H:i");
echo("Nous sommes le <b>$date</b> et il est <b>$heure</b>");
?>
</time>
									
					
				</div>
									
								
			</div>
			
		</div>
	</div>
			
	<div id="block-header"><div>
		<div class="wrapper">
		
			<header id="header">
		
				<div id="headerbar" class="grid-block">
				
						
					<a id="logo" href="?p=home"><img src="images/lvrp/logo.png" width="300" height="90" alt="logo" /></a>
<?php
 if(!isset($_SESSION['login'])){
?>
<?php } else {
?>
					<div class="left"><div class="module   deepest">
<?php $tok = $row['Tokens']; ?>
			Bienvenue <b> <?php echo $row['Name'];?></b>,<br /> Vous disposez acutellement de <b><?php echo number_format($tok,1); ?></b> tokens.		
</div></div>
	
	<?php } ?>
	<div class="left"><div class="module   deepest">

<!--			<ul class="social-icons-special">
	<li class="googleplus"><a href="#" title="Google +">google +</a></li>
	<li class="twitter"><a href="#" title="twitter">twitter</a></li>
	<li class="facebook"><a href="#" title="facebook">facebook</a></li>
</ul>	!-->	
</div></div>
										
				</div>
		
				<div id="menubar" class="grid-block">
					
										<nav id="menu"><ul class="menu menu-dropdown">
										<li class="level1 item2 parent"><a href="?p=home" class="level1 parent"><span>Serveur</span></a>
										<div class="dropdown columns1"><div class="dropdown-bg">
										<div>
										<div class="width100 column">
										<ul class="level2">
										<li class="level2 item1"><a href="?p=home" class="level2"><span>Acceuil</span></a></li>
										<li class="level2 item2"><a href="?p=equipe" class="level2"><span>L'&Eacute;quipe</span></a></li>
										<li class="level2 item3"><a href="?p=regles" class="level2"><span>R&egrave;glement</span></a></li>
										<li class="level2 item3"><a href="/ancien/index.php" class="level2"><span>Ancien Site</span></a></li>											
										</ul></div></div></div></div></li>
										
										<li class="level1 item2 parent"><a href="" class="level1 parent"><span>Classement</span></a>
										<div class="dropdown columns1"><div class="dropdown-bg">
										<div>
										<div class="width100 column">
										<ul class="level2">
										<li class="level2 item1"><a href="?p=vote" class="level2"><span>Vote</span></a></li>
										<li class="level2 item2"><a href="?p=argent" class="level2"><span>Argent</span></a></li>
										<li class="level2 item3"><a href="?p=crime" class="level2"><span>Crime</span></a></li>
										<li class="level2 item4"><a href="?p=level" class="level2"><span>Niveau</span></a></li>
										<li class="level2 item5"><a href="?p=connected" class="level2"><span>Temps de jeu</span></a></li>
										
										</ul></div></div></div></div></li>
										
										<li class="level1 item6"><a href="?p=definitions" class="level1"><span>Définition</span></a></li>
										
										<li class="level1 item2 parent"><a href="" class="level1 parent"><span>Boutique</span></a>
										<div class="dropdown columns1"><div class="dropdown-bg">
										<div>
										<div class="width100 column">
										<ul class="level2">
										<li class="level2 item1"><a href="?p=tokens" class="level2"><span>Tokens</span></a></li>
										<li class="level2 item2"><a href="?p=boutique" class="level2"><span>Achats en jeu</span></a></li>
										
										</ul></div></div></div></div></li>
										
										<li class="level1 item2 parent"><a href="" class="level1 parent"><span>Nous rejoindre</span></a>
										<div class="dropdown columns1"><div class="dropdown-bg">
										<div>
										<div class="width100 column">
										<ul class="level2">
										<li class="level2 item1"><a href="?p=inscription" class="level2"><span>Inscription</span></a></li>
										<li class="level2 item2"><a href="?p=telechargement" class="level2"><span>Téléchargement</span></a></li>
										
										</ul></div></div></div></div></li>
										
										<li class="level1 item6"><a href="/forum/index.php" class="level1"><span>Forum</span></a></li>


</ul>
<?php
 if(!isset($_SESSION['login'])){
?>
<ul class="menu menu-dropdown">
	<li class="level1 parent">
		<span class="level1 parent">
			<span><span class="title">Connexion </span><span class="subtitle"> Inscription au serveur</span></span>
		</span>
		<div class="dropdown columns1" >
			<div class="dropdown-bg">
				<div>
					<div class="module">

		
	<form class="short style" action="?p=login" method="post" name="login">
	
				
		<div class="username">
			<input type="text" name="login" size="18" placeholder="Nom de compte" />
		</div>
		
		<div class="password">
			<input type="password" name="passlog" size="18" placeholder="Mot de passe" />
		</div>
		
				<div class="remember">
						<label for="modlgn_remember-169440798">Se souvenir de moi ?</label>
			<input id="modlgn_remember-169440798" type="checkbox" name="remember" value="yes" checked />
		</div>
				
		<div class="button">
			<button type="submit" name="logon" value="" class="loginBtn">Connexion</button>
		</div>
		

		<ul class="blank">
			<li>
				<a href="?p=inscription">S'inscrire ?</a>
				<a href="?p=mdpoublie">Mot de passe oubli&eacute; ?</a>
			</li>

					</ul>
		
			
		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="L2RlbW8vdGhlbWVzL2pvb21sYS8yMDExL2NhdGFseXN0L2luZGV4LnBocD9vcHRpb249Y29tX2NvbnRlbnQmdmlldz1hcnRpY2xlJmlkPTkmSXRlbWlkPTQ=" />
		<input type="hidden" name="ea9c324d38c93f4d704108ec3b313011" value="1" />	</form>
	
	<script>
		jQuery(function($){
			$('form.short input[placeholder]').placeholder();
		});
	</script>
	
</div>
				</div>
			</div>
		</div>
	</li>
</ul>
<?php
} else {
?>
	<ul class="menu menu-dropdown">
	<?php
		if($row['AdminLevel'] >=6)
		{
			echo'
										<li class="level1 item2 parent"><a href="" class="level1 parent"><span>Panel Admin</span></a>
										<div class="dropdown columns1"><div class="dropdown-bg">
										<div>
										<div class="width100 column">
										<ul class="level2">
										<li class="level2 item1"><a href="?p=banjoueur" class="level2"><span>Ban ou deban un Joueur</span></a></li>
										<li class="level2 item3"><a href="?p=logs" class="level2"><span>Logs</span></a></li>
										<li class="level2 item3"><a href="?p=news" class="level2"><span>News</span></a></li>
										
										</ul></div></div></div></div></li>
			';
		}
	?>
  <li class="level1 item6"><a href="?p=logout" class="level1"><span>Déconnexion</span></a></li>
  </ul>
<?php
}
?>
</nav>
							

										
				</div>
			
							
			</header>
		
		</div>
	</div></div>
	
	<div id="block-main"><div><div>
		<div class="wrapper">
							<section id="top-a" class="grid-block"><div class="grid-box width25 grid-h"><div class="module mod-box  deepest">
			
		
		 <div id="sliderFrame">
        <div id="slider">
            <img src="./templates/lvrp/slider/image_slider1.jpg" />
            <img src="./templates/lvrp/slider/image_slider2.jpg" />
            <img src="./templates/lvrp/slider/image_slider4.jpg" />
            <img src="./templates/lvrp/slider/image_slider5.jpg" />
        </div>
    </div>


			
</div></div>