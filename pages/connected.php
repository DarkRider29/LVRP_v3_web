				
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Classement</a>
												<strong>Temps de jeu</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Classement Temps de jeu </h1> </header>
	

		
	
		
	<div class="content clearfix">

		<p>
					<table class="zebra">
			<thead>
		<tr>
			<th class="center">#</th>
			<th class="center">Prénom_Nom</th>
			<th class="center">temps de jeu (heures)</th>
		</tr>
	</thead>
	<?php
$position = 0;
$req = $bdd->query('SELECT * FROM lvrp_users ORDER BY ConnectedTime DESC LIMIT 0, 20');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php $position++; if ($position == 1) { echo "<center><img src=./templates/lvrp/trophy/trophy_1.png></center>"; } elseif ($position == 2) { echo "<center><img src=./templates/lvrp/trophy/trophy_2.png></center>"; } elseif ($position == 3) { echo "<center><img src=./templates/lvrp/trophy/trophy_3.png></center>"; }	else { echo "<center>" . $position . "</center>"; }?></td><td><center><?php echo $donnees['Name']; ?></center></td></td><td><center><?php echo $donnees['ConnectedTime']; ?></center></td></tr>
			<?php
				}
			
			?>	
							</table>
		</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>