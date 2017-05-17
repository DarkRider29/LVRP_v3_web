<?php
 if(!isset($_SESSION['login'])){
?>
<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Voter pour le serveur</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title">Protocole pour voter correctement</h1> </header>
	

		
	
		
	<div class="content clearfix">
<p>
<div class="danger"><b><center>Vous n'êtes pas connecter !<br /> Si vous voter aucun tokens ne vous serra créditez et votre vote ne serra pas pris en compte dans notre classement </center></b></div>
<center><a class="button-more" href="http://www.root-top.com/topsite/gta/in.php?ID=2382"> Voter quand même ! </a></center>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				
				<?php } else { ?>
				
	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Voter pour le serveur</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title">Protocole pour voter correctement</h1> </header>
	

		
	
		
	<div class="content clearfix">
<p>
<div class="attention">Avant toute manipulations, nous vous prions de vous déconnecter du jeu pour éviter tout bugs du aux points  !</div>
<center><h5>Voter pour le serveur à comme but de nous faire progresser et d'assurer sa continuité.<br />
Grâce à vos votes, vous verrez vos points accroître de 20 points par vote.<br />
Vous avez la possibilité de pouvoir voter une fois tous les 2 heures. </h5></center>
<br /><div class="danger">Si vous ne remplissez pas le captcha, vous serrez bannis 24H et vos points vous serrons retirer !</div>

<h4> Vos statistiques : </h4>
<li> Vous avez effectué : <b> <?php echo $row['Votes']; ?> votes </b></li><br />
<li>Vous avez : <b><?php echo $row['Tokens'];?> Tokens</b> <small><a href="?p=tokens"> En obtenir d'avantage</small> </a></li></b>


<br /><div class ="alert alert-info"><b>Comment voter ?</b>
 Il vous suffit de cliquer sur le bouton voter juste en dessous ! <br /></div>
 <br />
 <center> <a href="?p=voter"><a class="button-more" href="?p=voter"> Voter ! </a>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>