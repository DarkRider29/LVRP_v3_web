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
?>
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Biens</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Mes biens </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>
<?php
// Voitures
if($dStats['Car1'] == -1 && $dStats['Car2'] == -1 & $dStats['Car3'] == -1 & $dStats['Car4'] == -1 & $dStats['Car5'] == -1 & $dStats['Car6'] == -1)
					{echo '<ul><h5><li>Vous n\'avez <a>pas de voiture</a></li></h5></ul>';}
					
				if($dStats['Car1'] != -1) 
				{
					$rCar1 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car1']."");
					$dCar1 = mysql_fetch_array($rCar1);
					echo '<h5><li><b>Véhicule slot 1 :</b><a> ID :'.$dStats['Car1'].' - Model : '.get_CarName($dCar1['Model']).'</a></li></h5>';
				}
				if($dStats['Car2'] != -1) 
				{
					$rCar2 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car2']."");
					$dCar2 = mysql_fetch_array($rCar2);
					echo '<h5><li><b>Véhicule slot 2 :</b><a> ID :'.$dStats['Car2'].' - Model : '.get_CarName($dCar2['Model']).'</a></li></h5>';
				}
				if($dStats['Car3'] != -1) 
				{
					$rCar3 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car3']."");
					$dCar3 = mysql_fetch_array($rCar3);
					echo '<h5><li><b>Véhicule slot 3 :</b><a> ID :'.$dStats['Car3'].' - Model : '.get_CarName($dCar3['Model']).'</a></li></h5>';
				}
				if($dStats['Car4'] != -1 && $dStats['CarUnLock4']) 
				{
					$rCar4 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car4']."");
					$dCar4 = mysql_fetch_array($rCar4);
					echo '<h5><li><b>Véhicule slot 4 :</b><a> ID :'.$dStats['Car4'].' - Model : '.get_CarName($dCar4['Model']).'</a></li></h5>';
				}
				if($dStats['Car5'] != -1 && $dStats['CarUnLock5']) 
				{
					$rCar5 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car5']."");
					$dCar5 = mysql_fetch_array($rCar5);
					echo '<h5><li><b>Véhicule slot 5 :</b> ID :'.$dStats['Car5'].' - Model : '.get_CarName($dCar5['Model']).'</a></li></h5>';
				}
				if($dStats['Car6'] != -1 && $dStats['CarUnLock6']) 
				{
					$rCar6 = mysql_query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car6']."");
					$dCar6 = mysql_fetch_array($rCar6);
					echo '<h5><li><b>Véhicule slot 6 :</b> ID :'.$dStats['Car6'].' - Model : '.get_CarName($dCar6['Model']).'</a></li></h5>';
				}
// Bizz
				if($dStats['Bizz1'] == -1 && $dStats['Bizz2'] == -1 & $dStats['Bizz3'] == -1)
					echo ('<ul><h5><li>Vous n\'avez <a>pas de biz</a></li></ul>');
				if($dStats['Bizz1'] != -1) 
				{
					if($dStats['Bizz1'] >= 1000)
					{
						$biz1=$dStats['Bizz1']-999;
						$rbizz1 = mysql_query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz1."");
						$dbizz1 = mysql_fetch_array($rbizz1);
						echo ('<h5><li>Bizz slot 1 :</b> <a>ID : '.$dStats['Bizz1'].' - Nom : '.$dbizz1['Message'].'</a></li></h5>');
					}
					else
					{
						$biz1=$dStats['Bizz1']+1;
						$rbizz1 = mysql_query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz1."");
						$dbizz1 = mysql_fetch_array($rbizz1);
						echo ('<h5><li><b>Bizz slot 1 :</b><a> ID : '.$dStats['Bizz1'].' - Nom : '.$dbizz1['Message'].'</a></li></h5>');
					}
				}
				if($dStats['Bizz2'] != -1) 
				{
					if($dStats['Bizz2'] >= 1000)
					{
						$biz2=$dStats['Bizz2']-999;
						$rbizz2 = mysql_query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz2."");
						$dbizz2 = mysql_fetch_array($rbizz2);
						echo ('<h5><li><b>Bizz slot 2 :</b> <a>ID : '.$dStats['Bizz2'].' - Nom : '.$dbizz2['Message'].'</a></li></h5>');
					}
					else
					{
						$biz2=$dStats['Bizz2']+1;
						$rbizz1 = mysql_query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz2."");
						$dbizz2 = mysql_fetch_array($rbizz2);
						echo ('<h5><li><b>Bizz slot 2 :</b> <a>ID : '.$dStats['Bizz2'].' - Nom : '.$dbizz2['Message'].'</a></li></h5>');
					}
				}
				if($dStats['Bizz3'] != -1) 
				{
					if($dStats['Bizz3'] >= 1000)
					{
						$biz3=$dStats['Bizz3']-999;
						$rbizz3 = mysql_query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz3."");
						$dbizz3 = mysql_fetch_array($rbizz3);
						echo ('<h5><li><b>Bizz slot 3 :</b> <a>ID : '.$dStats['Bizz3'].' - Nom : '.$dbizz3['Message'].'</a></h5></li>');
					}
					else
					{
						$biz3=$dStats['Bizz3']+1;
						$rbizz3 = mysql_query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz3."");
						$dbizz3 = mysql_fetch_array($rbizz3);
						echo ('<h5><li><b>Bizz slot 3 :</b> <a>ID : '.$dStats['Bizz3'].' - Nom : '.$dbizz3['Message'].'</a></h5></li>²²');
					}
				}
// Maisons
				if($dStats['House1'] == -1 && $dStats['House2'] == -1 & $dStats['House3'] == -1)
					echo ('<h5><ul><li>Vous n\'avez <a>pas de maison</a></h5></li></ul>');
				if($dStats['House1'] != -1)
				{
					$house1=$dStats['House1']+1;
					$rhouse1 = mysql_query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house1."");
					$dhouse1 = mysql_fetch_array($rhouse1);
					echo ('<h5><li><b>Maison slot 1 :</b> <a>ID : '.$dStats['House1'].' - Info : '.$dhouse1['Message'].'</a></h5></li>');
				}
				if($dStats['House2'] != -1)
				{
					$house2=$dStats['House2']+1;
					$rhouse2 = mysql_query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house2."");
					$dhouse2 = mysql_fetch_array($rhouse2);
					echo ('<h5><li><b>Maison slot 2 :</b> <a>ID : '.$dStats['House2'].' - Info : '.$dhouse2['Message'].'</a></h5></li>');
				}
				if($dStats['House3'] != -1)
				{
					$house3=$dStats['House3']+1;
					$rhouse3 = mysql_query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house3."");
					$dhouse3 = mysql_fetch_array($rhouse3);
					echo ('<h5><li><b>Maison slot 3 :</b> ID : '.$dStats['House3'].' - Info : '.$dhouse3['Message'].'</a></h5></li>');
				}
// Garage
				if($dStats['Garage1'] == -1 && $dStats['Garage2'] == -1 & $dStats['Garage3'] == -1)
					echo ('<ul><h5><li>Vous n\'avez <a>pas de garage</a></li></ul>');
				if($dStats['Garage1'] != -1) echo ('<h5><li>Garage slot 1 : <a>ID : '.$dStats['Garage1'].'</a></li></h5>');
				if($dStats['Garage2'] != -1) echo ('<h5><li>Garage slot 2 : ID : '.$dStats['Garage2'].'</a></li></h5>');
				if($dStats['Garage3'] != -1) echo ('<h5><li>Garage slot 3 : ID : '.$dStats['Garage3'].'</a></li></h5>');
?>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>