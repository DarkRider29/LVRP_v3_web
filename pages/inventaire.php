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
												<strong>Inventaire</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Mon inventaire </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>
<?php
if($dStats['InvWeapon1'] == 0 && $dStats['InvWeapon2'] == 0 && $dStats['InvWeapon3'] == 0 && $dStats['InvWeapon4'] == 0 && ($dStats['InvWeapon5'] == 0 && $dStats['InvDev5'] == 1) && ($dStats['InvWeapon6'] == 0 && $dStats['InvDev6'] == 1))
				echo ('<ul><h5><li>Vous n\'avez pas d\'armes dans votre inventaire.</h5></li></ul>');
				if($dStats['InvWeapon1'] != 0 && $dStats['InvAmmo1'] != 0) echo ('<li><b>Arme slot 1 :</b> '.get_WepName($dStats['InvWeapon1']).' ('.$dStats['InvAmmo1'].' balle(s)) </li>');
				if($dStats['InvWeapon2'] != 0 && $dStats['InvAmmo2'] != 0) echo ('<li><b>Arme slot 2 :</b> '.get_WepName($dStats['InvWeapon2']).' ('.$dStats['InvAmmo2'].' balle(s)) </li>');
				if($dStats['InvWeapon3'] != 0 && $dStats['InvAmmo3'] != 0) echo ('<li><b>Arme slot 3 :</b> '.get_WepName($dStats['InvWeapon3']).' ('.$dStats['InvAmmo3'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo4'] != 0) echo ('<li><b>Arme slot 4 :</b> '.get_WepName($dStats['InvWeapon4']).' ('.$dStats['InvAmmo4'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo5'] != 0) echo ('<li><b>Arme slot 5 :</b> '.get_WepName($dStats['InvWeapon5']).' ('.$dStats['InvAmmo5'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo6'] != 0) echo ('<li><b>Arme slot 6 :</b> '.get_WepName($dStats['InvWeapon6']).' ('.$dStats['InvAmmo6'].' balle(s)) </li>');
?>
<br />
	<header> <h1 class="title"> Autres </h1> </header>
<?php
if($dStats['Weed'] > 0) echo ('<li><b>Weed :</b><a> '.$dStats['Weed'].' gramme(s)</a></li>');
				if($dStats['SeedWeed'] > 0) echo ('<li><b>Graine(s) de weed :</b><a> '.$dStats['SeedWeed'].' </a></li>');
				if($dStats['Heroine'] > 0) echo ('<li><b>Heroïne :</b> <a>'.$dStats['Heroine'].' gramme(s)</a></li>');
				if($dStats['Cocaine'] > 0) echo ('<li><b>Cocaïne :</b><a> '.$dStats['Cocaine'].' gramme(s)</a></li>');
				if($dStats['Ecstasie'] > 0) echo ('<li><b>Ecstasie :</b><a> '.$dStats['Ecstasie'].' gramme(s)</a></li>');
				if($dStats['Tabac'] > 0) echo ('<li><b>Tabac :</b><a> '.$dStats['Tabac'].' </a></li>');
				if($dStats['Leaf'] > 0) echo ('<li><b>Feuilles :</b><a> '.$dStats['Leaf'].' </a></li>');
				if($dStats['Materials'] > 0) echo ('<li><b>Matériaux :</b><a> '.$dStats['Materials'].' </a></li>');
?>
</p>
</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>