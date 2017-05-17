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
												<a><?php echo $row['Name'];?></a>
												<strong>Virer un joueur</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Virer un joueur </h1> </header>
	

		
	
		
<div class="content clearfix">
<p>
<?php
	$rStats = mysql_query("SELECT * FROM `lvrp_users` WHERE `id`='".$_GET['id']."'");
	$dStats = mysql_fetch_array($rStats);
	if($dStats['Member'] == $row['Leader'] && $dStats['Connected'] == 0)
	{
		mysql_query("UPDATE `lvrp_users` SET Member=0, Rank=0, Leader=0 WHERE `id` = '".$_GET['id']."'");
		echo"
	<script type='text/javascript'>
	window.location.replace('index.php?p=gestionfaction');
	</script>
	";
	}
	else
	{
		echo"
	<script type='text/javascript'>
	window.location.replace('index.php?p=gestionfaction');
	</script>
	";
	}
?>
</p>

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
