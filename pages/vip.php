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
				<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>V.I.P</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Mon rang V.I.P </h1> </header>

		
	
		
	<div class="content clearfix">

<p>
<?php
			if($row['DonateRank']==1) $row['DonateRank']="VIP Fer";
			elseif($row['DonateRank']==2) $row['DonateRank']="VIP Argent";
			elseif($row['DonateRank']==3) $row['DonateRank']="VIP Or";
			elseif($row['DonateRank']==4) $row['DonateRank']="VIP Diamant";
			else $row['DonateRank']="Aucun";
?>
	<ul>
	<li> <h5>Rang V.I.P : <a> <?php echo $row['DonateRank']; ?></a></h5> </li>
	<li> <h5>Temps restant : <a> <?php echo $row['VipTime']; ?> mins</a></h5> </li>
	</ul>
</p>
</div>
	<div class="content clearfix">
	<header> <h1 class="title"> Divers </h1> </header>
<p>
<?php
			if($row['DonateRank']==1) $row['DonateRank']="VIP Fer";
			elseif($row['DonateRank']==2) $row['DonateRank']="VIP Argent";
			elseif($row['DonateRank']==3) $row['DonateRank']="VIP Or";
			elseif($row['DonateRank']==4) $row['DonateRank']="VIP Diamant";
			else $row['DonateRank']="Aucun";
?>
	<ul>
	<li> <h5>Renames : <a> <?php echo $row['PointsRename']; ?></a></h5> </li>
	<li> <h5>ChangeNum : <a> <?php echo $row['ChangeNum']; ?></a></h5> </li>
	<li> <h5>ChangePlaque : <a> <?php echo $row['ChangePlaque'];?></a></h5> </li>
	<li> <h5>ChangeAge : <a> <?php echo $row['ChangeAge']; ?></a></h5> </li>
	<li> <h5>ChangeSexe : <a> <?php echo $row['ChangeSex']; ?></a></h5> </li>
	<?php
				if($row['CarUnLock4'] == 1) echo ('<li><h5>Slot Véhicule 1 déverrouillé : <a>Oui</a></h5></li>');
				else echo ('<li><h5>Slot Véhicule 1 déverrouillé : <a>Non</a></h5></li>');
				if($row['CarUnLock5'] == 1) echo ('<li><h5>Slot Véhicule 2 déverrouillé : <a>Oui</a></h5></li>');
				else echo ('<li><h5>Slot Véhicule 2 déverrouillé : <a>Non</a></h5></li>');
				if($row['CarUnLock6'] == 1) echo ('<li><h5>Slot Véhicule 3 déverrouillé : <a>Oui</a></h5></li>');
				else echo ('<li><h5>Slot Véhicule 3 déverrouillé : <a>Non</a></h5></li>');
				if($row['InvDev5'] == 1) echo ('<li><h5>Slot Arme 1 déverrouillé : <a>Oui</a></h5></li>');
				else echo ('<li><h5>Slot Arme 1 déverrouillé : <a>Non</a></h5></li>');
				if($row['InvDev6'] == 1) echo ('<li><h5>Slot Arme 2 déverrouillé : <a>Oui</a></h5></li>');
				else echo ('<li><h5>Slot Arme 2 déverrouillé : <a>Non</a></h5></li>');
	?>
	</ul>
</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>