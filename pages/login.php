<?php
 if(!isset($_SESSION['login'])){
?>
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
		
	<header> <h1 class="title"> Connexion </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>

<?php

	$compte = 'compte';

if (isset($_POST['logon']))
{			
	
			$sql   = "SELECT Name FROM lvrp_users WHERE Name = '". mysql_real_escape_string($_POST['login']) ."'";
            $sql   = mysql_query($sql);
            $sql   = mysql_num_rows($sql);
			
			$donnees = mysql_fetch_array(mysql_query("SELECT * FROM lvrp_users WHERE Name = '".mysql_real_escape_string($_POST['login'])."'"));

            if ($sql != NULL)
			{
				if (sha1(mysql_real_escape_string($_POST['passlog'])) == $donnees['Pass'])
				{
					$logOK = TRUE;
					$_SESSION['login'] = $donnees['Name'];
					
				
					$_SESSION['id'] = $donnees['id'];
					?>
					<?php
					echo '<p><div class="valid"> Connexion avec succès ! </div></p>';
					?>
					<meta http-equiv="refresh" content="0; URL=?p=home">				
					<?php
				}
			
				else
				{
					$logERR = TRUE;
					$logERRMSG = "Votre mot de passe incorrect.";
				}
			}
			else
			{
			$logERR = TRUE;
			$logERRMSG = "Votre nom de compte est incorrect.";
			}
	
}
?>

		<?php if(isset($logOK)) { ?>
	
	<?php } ?>
		<?php if(isset($logERR)) 
		{ 
		echo '<div class="danger">'.$logERRMSG.'</div>' ; } 
?>

 </p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				


				
					<?php
					} else {
					?>
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

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php
				}
				?>