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
												<strong>Rang</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<?php
$id = $_GET['id'];
$rStats = mysql_query("SELECT * FROM `lvrp_users` WHERE `id`='".$id."'");
$dStats = mysql_fetch_array($rStats);

if($dStats['Member'] != $row['Leader'])
{
	echo"
	<script type='text/javascript'>
	window.location.replace('index.php?p=gestionfaction');
	</script>
	";
}

echo '<header> <h1 class="title"> '.$dStats['Name'].' </h1> </header>';
?>
	

		
	
		
<div class="content clearfix">
<p>
<?php
 
if(isset($_POST['ok']))
{
	mysql_query("UPDATE `lvrp_users` SET Rank='".$_POST['rank']."' WHERE id='".$id."'");
	echo"
	<script type='text/javascript'>
	window.location.replace('index.php?p=gestionfaction');
	</script>
	";
}

?>
</p>
<form method="post" action="index.php?p=gestionfaction_edit&id=<?php echo $id; ?>" name="ok" class="box style">
<fieldset>
		<legend></legend>
		<div><label>Rang : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="rank" maxlength="1" type="number" value="<?php echo $dStats['Rank']; ?>" id="f1"/></div>
	<center><button class="input" name="ok" type="submit">Enregistrer</button></center>
</center>
	</fieldset></form>

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
