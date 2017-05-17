<?php
 if(!isset($_SESSION['login'])){
?>				
	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Page introuvable</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Érreur 404 </h1> </header>
	

		
	
		
	<div class="content clearfix">

<div class="danger"> La page que vous souhaitez visitez n'existe pas ! </div>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php } else {
				?>
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Boutique</a>
												<strong>Achat en cours</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Achat en cours </h1> </header>
	

		
	
		
	<div class="content clearfix">

		<p>
<?php
$points = 100;
$ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas='';
$idp = 70188;
// $ids n'est plus utilisé, mais il faut conserver la variable pour une question de compatibilité
$idd = 146281;
$ident=$idp.";".$ids.";".$idd;
if(isset($_POST['code1'])) $code1 = $_POST['code1'];
if(isset($_POST['code2'])) $code2 = ";".$_POST['code2'];
if(isset($_POST['code3'])) $code3 = ";".$_POST['code3'];
if(isset($_POST['code4'])) $code4 = ";".$_POST['code4'];
if(isset($_POST['code5'])) $code5 = ";".$_POST['code5'];
$codes=$code1.$code2.$code3.$code4.$code5;
if(isset($_POST['DATAS'])) $datas = $_POST['DATAS'];
$ident=urlencode($ident);
$codes=urlencode($codes);
$datas=urlencode($datas);

$get_f=@file("http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas");
if(!$get_f)
{
	exit("Votre serveur n'a pas accès au serveur de StarPass, merci de contacter votre hébergeur.");
}
$tab = explode("|",$get_f[0]);
							
if(!$tab[1]) $url = "index.php?p=buytokensinvalid";
else $url = $tab[1];
							
$pays = $tab[2];
$palier = urldecode($tab[3]);
$id_palier = urldecode($tab[4]);

$type = urldecode($tab[5]);
if(substr($tab[0],0,3) != "OUI")
{
	echo '
	<script type="text/javascript">
	window.location.replace("index.php?p=buytokensinvalid");
	</script>';
}
else
{
	mysql_query("UPDATE lvrp_users SET Tokens = Tokens + $points WHERE Name = '".$_SESSION['login']."'");
	echo"
	<script type='text/javascript'>
	window.location.replace('index.php?p=buytokensvalid');
	</script>
	";

	$date = date("d-m-Y");
	$heure = date("H:i");
	$ip = $_SERVER["REMOTE_ADDR"];
	mysql_query("INSERT INTO `lvrp_site_tokens` SET Name='".$_SESSION['login']."', Date='".$date." a ".$heure."', Reson='+ 100', Ip='".$ip."'");
}

?>
		</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php 
				}?>