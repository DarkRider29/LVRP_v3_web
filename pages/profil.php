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
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Profil</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
	<?php 
			if($row['Origin'] == '1') $row['Origin'] = 'Vice City';
			elseif($row['Origin'] == '2') $row['Origin'] = 'Liberty City';
			elseif($row['Origin'] == '3') $row['Origin'] = 'Chinatown';
			elseif($row['Origin'] == '4') $row['Origin'] = 'San Fierro';
			elseif($row['Origin'] == '5') $row['Origin'] = 'Las Venturas';
			
			if($row['City'] == '1') $row['City'] = 'Los Santos';
			elseif($row['City'] == '2') $row['City'] = 'San Fierro';
			elseif($row['City'] == '3') $row['City'] = 'Las Venturas';
			elseif($row['City'] == '4') $row['City'] = 'Fort Carson';
			
			if($row['CarLic'] == '0') $row['CarLic'] = 'Non acquis';
			elseif($row['CarLic'] == '1') $row['CarLic'] = 'Acquis';
			
			if($row['FlyLic'] == '0') $row['FlyLic'] = 'Non acquis';
			elseif($row['FlyLic'] == '1') $row['FlyLic'] = 'Acquis';
			
			if($row['BoatLic'] == '0') $row['BoatLic'] = 'Non acquis';
			elseif($row['BoatLic'] == '1') $row['BoatLic'] = 'Acquis';
			
			if($row['MotoLic'] == '0') $row['MotoLic'] = 'Non acquis';
			elseif($row['MotoLic'] == '1') $row['MotoLic'] = 'Acquis';
			
			if($row['LourdLic'] == '0') $row['LourdLic'] = 'Non acquis';
			elseif($row['LourdLic'] == '1') $row['LourdLic'] = 'Acquis';
			
			if($row['FishLic'] == '0') $row['FishLic'] = 'Non acquis';
			elseif($row['FishLic'] == '1') $row['FishLic'] = 'Acquis';
			
			if($row['TrainLic'] == '0') $row['TrainLic'] = 'Non acquis';
			elseif($row['TrainLic'] == '1') $row['TrainLic'] = 'Acquis';
			
			if($row['Sex'] == '1') $row['Sex'] = 'Homme';
			elseif($row['Sex'] == '2') $row['Sex'] = 'Femme';
			
			if($row['PhoneNr'] == '0') $row['PhoneNr'] = 'Aucun';
			
			if($row['Connected'] == '0') $row['Connected'] = 'Non';
			else $row['Connected'] = '<font color="red">Oui</font>';
			
			if($row['Locked'] == '0') $row['Locked'] = 'Non';
			else $row['Locked'] = '<font color="red">Oui</font>';
			
			if($row['CombatStyle'] == '0') $row['CombatStyle'] = 'Elbow';
			elseif($row['CombatStyle'] == '1') $row['CombatStyle'] = 'Boxing';
			elseif($row['CombatStyle'] == '2') $row['CombatStyle'] = 'Grabkick';
			elseif($row['CombatStyle'] == '3') $row['CombatStyle'] = 'Kneehead';
			elseif($row['CombatStyle'] == '4') $row['CombatStyle'] = 'Kungfu';
			elseif($row['CombatStyle'] == '5') $row['CombatStyle'] = 'Normal';
			
			$age = $row['Level']+16;
	?>	
	<header> <h1 class="title"> Mon compte </h1> </header>
<ul>
<li> <h5>Actuellement connecté IG : <a><?php if ($row['Connected'] == 1){ echo "Connecté(e)"; } else { echo "Déconnecté(e)";}?></h5></a></li>
<li> <h5>Temps de jeu : <a><?php echo $row['ConnectedTime']?> heure(s)</a></h5></li>
<li> <h5>Avertissement(s) : <a><?php echo $row['Warnings']?></a></h5></li>
<li> <h5>Email : <a><?php echo $row['Email']?></a></h5></li>
<li> <h5>Dernière connexion : <a><?php echo date("d-m-Y à H:i:s",$row['LastLog'])?></a></h5></li>
<li> <h5>Banni : <a><?php echo $row['Locked']?></a></h5></li>
</ul>	
<hr class="dotted" />	

		<header> <h1 class="title"> Le personnage </h1> </header>
		<dl class="separator">
		<dt><center><br /><br /><br /><img src="./images/lvrp/skins/<?php echo $row['Skin']?>.jpg"></center></dt>
		<dd>

<ul>
<li> <h5>Identité : <a><?php echo $row['Name'];?></h5></a></li>
<li> <h5>Âge : <a><?php echo $age?> ans</a></h5></li>
<li> <h5>Level : <a><?php echo $row['Level']?></a></h5></li>
<li> <h5>Origine : <a><?php echo $row['Origin']?></a></h5></li>
<li> <h5>Sexe : <a><?php echo $row['Sex']?></a></h5></li>
<li> <h5>Numéro de téléphone : <a><?php echo $row['PhoneNr']?></a></h5></li>
<li> <h5>Cash : <a><?php echo $row['Cash']?> $</a></h5></li>
<li> <h5>Compte en banque : <a><?php echo $row['Bank']?> $</a></h5></li>
<li> <h5>Seconde langue : <a><?php echo get_LangName($row['Lang1'])?> (<?php echo $row['LangState1']?>%)</a></h5></li>
<li> <h5>Troisième langue : <a><?php echo get_LangName($row['Lang2'])?> (<?php echo $row['LangState2']?>%)</a></h5></li>
<li> <h5>Style de combat : <a><?php echo $row['CombatStyle']?></a></h5></li>
</ul>


			</dd>
			
	</dl>
		
<hr class="dotted" />	
<header> <h1 class="title"> Les permis </h1> </header>
<ul>
<li> <h5>Permis de conduire : <a> <?php echo $row['CarLic']; ?></a></h5> </li>
<li> <h5>Permis de vol : <a> <?php echo $row['FlyLic']; ?></a></h5> </li>
<li> <h5>Permis de navigation : <a> <?php echo $row['BoatLic']; ?></a></h5> </li>
<li> <h5>Permis moto : <a> <?php echo $row['MotoLic']; ?></a></h5> </li>
<li> <h5>Permis poids lourd : <a> <?php echo $row['LourdLic']; ?></a></h5> </li>
<li> <h5>Permis de pêche : <a> <?php echo $row['FishLic']; ?></a></h5> </li>
<li> <h5>Permis de train : <a> <?php echo $row['TrainLic']; ?></a></h5> </li>
</ul>
				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>