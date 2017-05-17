<?php if(!isset($_SESSION['login'])){?>

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
Redirection vers <a href="?p=home"> l'acceuil dans 3 secondes </a>
<meta http-equiv="refresh" content="3; URL=?p=home">

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
			<?php } else { $leader = $row['Rank'] >= 6; if ($leader){ ?>	
			
		<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo get_FacName($row['Member']);?></a>
												<strong>Gestion Faction</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Gestion Faction </h1> </header>
	

		
	
		
<div class="content clearfix">
<p>
<?php
 
if(isset($_POST['ok']))
{
$name = $row['Name'];
$rank1 = mysql_real_escape_string($_POST['rank1']);
$rank2 = mysql_real_escape_string($_POST['rank2']);
$rank3 = mysql_real_escape_string($_POST['rank3']);
$rank4 = mysql_real_escape_string($_POST['rank4']);
$rank5 = mysql_real_escape_string($_POST['rank5']);
$rank6 = mysql_real_escape_string($_POST['rank6']);
if($row['Leader']==1 || $row['Leader']==2)
	{$rank7 = mysql_real_escape_string($_POST['rank7']);}
if($row['Leader']==1)
	{mysql_query("UPDATE `lvrp_factions_police` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Rank7='".$rank7."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', Skin7='".$_POST['skin7']."', EditedBySite=1 WHERE id=1");}
elseif($row['Leader']==2)
	{mysql_query("UPDATE `lvrp_factions_fbi` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Rank7='".$rank7."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', Skin7='".$_POST['skin7']."', EditedBySite=1 WHERE id=1");}
elseif($row['Leader']==3)
	{mysql_query("UPDATE `lvrp_factions_medic` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', EditedBySite=1 WHERE id=1");}
elseif($row['Leader']==4)
	{mysql_query("UPDATE `lvrp_factions_gouvernement` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', EditedBySite=1 WHERE id=1");}
elseif($row['Leader']==5)
	{mysql_query("UPDATE `lvrp_factions_pompier` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', EditedBySite=1 WHERE id=1");}
elseif($row['Leader']==9)
	{mysql_query("UPDATE `lvrp_factions_mecano` SET Rank1='".$rank1."', Rank2='".$rank2."', Rank3='".$rank3."', Rank4='".$rank4."', Rank5='".$rank5."', Rank6='".$rank6."', Skin1='".$_POST['skin1']."', Skin2='".$_POST['skin2']."', Skin3='".$_POST['skin3']."', Skin4='".$_POST['skin4']."',Skin5='".$_POST['skin5']."', Skin6='".$_POST['skin6']."', EditedBySite=1 WHERE id=1");}

}

?>
</p>
<form method="post" action="index.php?p=gestionfaction" name="ok" class="box style">
<fieldset>
		<legend>Rangs</legend>
		<div><label>Rang 1 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank1" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],1); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin1" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],1); ?>" id="f1"/></div>
		<div><label>Rang 2 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank2" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],2); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin2" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],2); ?>" id="f1"/></div>
		<div><label>Rang 3 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank3" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],3); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin3" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],3); ?>" id="f1"/></div>
		<div><label>Rang 4 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank4" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],4); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin4" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],4); ?>" id="f1"/></div>
		<div><label>Rang 5 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank5" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],5); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin5" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],5); ?>" id="f1"/></div>
		<div><label>Rang 6 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank6" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],6); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin6" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],6); ?>" id="f1"/></div>
		<?php
		if($row['Leader'] == 1 || $row['Leader']== 2)
		{?>
		<div><label>Rang 7 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank7" maxlength="32" type="text" value="<?php echo get_FacRank($row['Member'],7); ?>" id="f1"/> <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbspSkin :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label> <input name="skin7" type="text" maxlength="3" value="<?php echo get_FacSkin($row['Member'],7); ?>" id="f1"/></div>
		<?php } ?>
	<center><button class="input" name="ok" type="submit">Enregistrer</button></center>
</center>
	</fieldset></form>
	
	<br />
	<h1> Membres actuelles </h1>
	<table class="zebra">
			<thead>
		<tr>
			<th class="center">PRénom_Nom</th>
			<th class="center">Rang</th>
			<th class="center">Dernière connexion</th>
			<th class="center">Changer le rang</th>
			<th class="center">Virer</th>
		</tr>
	</thead>

	<?php

$req = $bdd->query('SELECT * FROM lvrp_users WHERE Member="'.$row['Member'].'" ORDER BY Rank DESC');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php 
					if($donnees['Connected'] ==1)
					{
						echo '<center>'.$donnees['Name'].'</center></td></td><td><center>'.get_FacRank($donnees['Member'],$donnees['Rank']).'</center></td><td><center> '.date("d-m-Y",$donnees['LastLog']).'</center></td><td><center><a>Joueur IG</a></center></td><td><a><center>Joueur IG</a></center></td>';
					}
					else
					{
						echo '<center>'.$donnees['Name'].'</center></td></td><td><center>'.get_FacRank($donnees['Member'],$donnees['Rank']).'</center></td><td><center> '.date("d-m-Y",$donnees['LastLog']).'</center></td><td><center><a href="index.php?p=faction&amp;sup_id='.$donnees['id'].'"><img src=images/lvrp/crayon.png></a></center></td><td><center><a href="index.php?p=gestionfaction_sup&amp;id='.$donnees['id'].'"><img src=images/lvrp/croix.png></a></center></td>';
					}
				}
			
			?>	
							</table>

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
												<strong>Page introuvable</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Érreur 404 </h1> </header>
	

		
	
		
	<div class="content clearfix">

<div class="danger"> La page que vous souhaitez visitez n'existe pas ! </div>
Redirection vers <a href="?p=home"> l'acceuil dans 3 secondes </a>
<meta http-equiv="refresh" content="3; URL=?p=home">

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
<?php } }
?>	
