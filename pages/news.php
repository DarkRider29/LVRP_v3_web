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
			<?php } else { $admin = $row['AdminLevel'] > 4; if ($admin){ ?>	
			
		<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>News</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> News </h1> </header>
	

		
	
		
<div class="content clearfix">
<p>
<?php
 
if(isset($_POST['ok']))
{
$name = $row['Name'];
$title = mysql_real_escape_string(htmlspecialchars(trim($_POST['title'])));
$raison = mysql_real_escape_string($_POST['raison']);
mysql_query("INSERT INTO `lvrp_site_news` SET Title='".$title."', Contenu='".$raison."', Date=UNIX_TIMESTAMP(), Autor='".$name."' ");
}

?>
</p>
<form method="post" action="#" name="ok" class="box style">
<fieldset>
		<legend>Nouvelle news</legend>
		<div><label>Titre : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="title" type="text" placeholder="Titre" id="f1"/></div>
<div><label for="f5">Description :</label><br/><textarea name="raison" rows="15" cols="120" id="f5">Description</textarea></div>

	<center><button class="input" name="ok" value="argent" type="submit">Creer !</button></center>
</center>
	</fieldset></form>
	
	<br />
	<h1> Les news actuelles </h1>
	<table class="zebra">
			<thead>
		<tr>
			<th class="center">Titre</th>
			<th class="center">Description</th>
			<th class="center">Auteur</th>
			<th class="center">Date</th>
			<th class="center"></th>
			<th class="center"></th>
		</tr>
	</thead>

	<?php

$req = $bdd->query('select * FROM lvrp_site_news ORDER BY Date DESC LIMIT 0, 1000');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php echo '<center>'.$donnees['Title'].'</center></td></td><td><center>'.$donnees['Contenu'].'</center></td><td><center> '.$donnees['Autor'].'</center></td><td><center> '.date("d-m-Y",$donnees['Date']).'</center></td><td><center><a href="index.php?p=news_edit&amp;id='.$donnees['id'].'"><img src=images/lvrp/crayon.png></a></center></td><td><center><a href="index.php?p=news_sup&amp;id='.$donnees['id'].'"><img src=images/lvrp/croix.png></a></center></td>';

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
