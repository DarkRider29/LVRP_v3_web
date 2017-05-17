<?php
 if(!isset($_SESSION['login'])){
?>	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Acceuil</a>
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
				<?php
			$rStats = mysql_query("SELECT * FROM `lvrp_users_casiers` WHERE `SQLid`='".$_SESSION['id']."'");
			$dStats = mysql_fetch_array($rStats);
			
?>
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Casier</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Crime actuelle </h1> </header>

	
		
	<div class="content clearfix">

<p>
<?php if($dStats){
?>
<ul>
<li> <h5>Nom du crime : <a> <?php echo $dStats['Crime1']; ?></a></h5> </li>
<li> <h5>Victime : <a> <?php echo $dStats['Victim']; ?></a></h5> </li>
<li> <h5>Témoin : <a> <?php echo $dStats['Witness']; ?></a></h5> </li>
</ul>
<?php } else {
?>
<ul>
<li><h5> Vous n'avez aucun <a>crime en ce moment </a> </h5></li>
</ul>
<?php 
}?>
<br /><hr class="dotted"><br />
<header> <h1 class="title"> Casier judiciaire </h1> </header>
<?php if($dStats){
?>
<ul>
<li> <h5>Crime(s) comis au total : <a> <?php echo $dStats['Crimes']; ?></a></h5> </li>
<li> <h5>Nombre de fois arrété : <a> <?php echo $dStats['Arrested']; ?></a></h5> </li>
<li> <h5>Ancien crime(s) : </li></h5>
<li><h5><a> <?php echo $dStats['Crime2']; ?></a></h5> </li>
<li><h5><a> <?php echo $dStats['Crime3']; ?></a></h5> </li>
<li><h5><a> <?php echo $dStats['Crime4']; ?></a></h5> </li>
<li><h5><a> <?php echo $dStats['Crime5']; ?></a></h5> </li>
</ul>
<?php } else {
?>
<ul>
<li><h5> Vous n'avez <a>pas de casier judiciaire</a> </h5></li>
</ul>
<?php
}
?>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>