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
			if($dStats['Origin'] == '1') $dStats['Origin'] = 'Vice City';
			elseif($dStats['Origin'] == '2') $dStats['Origin'] = 'Liberty City';
			elseif($dStats['Origin'] == '3') $dStats['Origin'] = 'Chinatown';
			elseif($dStats['Origin'] == '4') $dStats['Origin'] = 'San Fierro';
			elseif($dStats['Origin'] == '5') $dStats['Origin'] = 'Las Venturas';
			
			if($dStats['City'] == '1') $dStats['City'] = 'Los Santos';
			elseif($dStats['City'] == '2') $dStats['City'] = 'San Fierro';
			elseif($dStats['City'] == '3') $dStats['City'] = 'Las Venturas';
			elseif($dStats['City'] == '4') $dStats['City'] = 'Fort Carson';
			
			if($dStats['CarLic'] == '0') $dStats['CarLic'] = 'Non acquis';
			elseif($dStats['CarLic'] == '1') $dStats['CarLic'] = 'Acquis';
			
			if($dStats['FlyLic'] == '0') $dStats['FlyLic'] = 'Non acquis';
			elseif($dStats['FlyLic'] == '1') $dStats['FlyLic'] = 'Acquis';
			
			if($dStats['BoatLic'] == '0') $dStats['BoatLic'] = 'Non acquis';
			elseif($dStats['BoatLic'] == '1') $dStats['BoatLic'] = 'Acquis';
			
			if($dStats['MotoLic'] == '0') $dStats['MotoLic'] = 'Non acquis';
			elseif($dStats['MotoLic'] == '1') $dStats['MotoLic'] = 'Acquis';
			
			if($dStats['LourdLic'] == '0') $dStats['LourdLic'] = 'Non acquis';
			elseif($dStats['LourdLic'] == '1') $dStats['LourdLic'] = 'Acquis';
			
			if($dStats['FishLic'] == '0') $dStats['FishLic'] = 'Non acquis';
			elseif($dStats['FishLic'] == '1') $dStats['FishLic'] = 'Acquis';
			
			if($dStats['TrainLic'] == '0') $dStats['TrainLic'] = 'Non acquis';
			elseif($dStats['TrainLic'] == '1') $dStats['TrainLic'] = 'Acquis';
			
			if($dStats['Sex'] == '1') $dStats['Sex'] = 'Homme';
			elseif($dStats['Sex'] == '2') $dStats['Sex'] = 'Femme';
			
			if($dStats['PhoneNr'] == '0') $dStats['PhoneNr'] = 'Aucun';
			
			if($dStats['Connected'] == '0') $dStats['Connected'] = 'Non';
			else $dStats['Connected'] = '<font color="red">Oui</font>';
?>
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Job</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Mon job </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>
<?php
if($dStats['Job'] > 0){
?>
<ul>
<li> <h5>Job : <a> <?php echo get_JobName($dStats['Job']); ?></a></h5> </li>
<li> <h5>Niveau : <a> <?php echo $dStats['JobLvl']; ?></a></h5> </li>
<li> <h5>Expérience : <a> <?php echo $dStats['JobExp']; ?></a></h5> </li>
<li> <h5>Bonus : <a> <?php echo $dStats['JobBonnus']; ?></a></h5> </li>
<li> <h5>Temps de travail : <a> <?php echo $dStats['JobTime']; ?></a></h5></li>
</ul>
<?php } else {
?>
<ul>
<h5><li>Vous ne faites <a>parti d'aucun jobs.</a></li></h5>
<?php } ?>

</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>