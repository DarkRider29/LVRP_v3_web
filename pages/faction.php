<?php
 if(!isset($_SESSION['login'])){
?>	<div id="main" class="grid-block">
			
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
<?php
			$rStats = mysql_query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['login']."'");
			$dStats = mysql_fetch_array($rStats);
			
			if($dStats['Leader'] == '0') $dStats['Leader'] = 'Non';
			else $dStats['Leader'] = '<font color="red">Oui</font> (<a href="index.php?p=gestionfaction">Panel Gestion</a>)';
?>
				
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Faction</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Ma faction </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>

<?php if($dStats['Member'] > 0){
?>
<ul>
<li> <h5>Faction : <a> <?php echo get_FacName($dStats['Member']); ?></a></h5> </li>
<li> <h5>Leader : <a> <?php echo $dStats['Leader']; ?></a></h5> </li>
<li> <h5>Rang : <a> <?php echo get_FacRank($dStats['Member'],$dStats['Rank']); ?></a></h5> </li>
<li> <h5>Temps de travail : <a> <?php echo $dStats['DutyTime']; ?></a></h5> </li>
</ul>
<?php } else {
?>
<ul>
<li><h5> Vous ne faites parti <a>d'aucunes factions. </a> </h5></li>
</ul>
<?php 
}?>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>