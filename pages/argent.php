				
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Classement</a>
												<strong>Argent</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Classement argent </h1> </header>
	

		
	
		
	<div class="content clearfix">

		<p>
					<table class="zebra">
			<thead>
		<tr>
			<th class="center">#</th>
			<th class="center">Pseudo</th>
			<th class="center">Cash</th>
			<th class="center">Banque</th>
		</tr>
	</thead>
	<?php
$position = 0;
$req = $bdd->query('SELECT * FROM lvrp_users ORDER BY Bank DESC, Cash DESC LIMIT 20');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php $position++; if ($position == 1) { echo "<center><img src=./templates/lvrp/trophy/trophy_1.png></center>"; } elseif ($position == 2) { echo "<center><img src=./templates/lvrp/trophy/trophy_2.png></center>"; } elseif ($position == 3) { echo "<center><img src=./templates/lvrp/trophy/trophy_3.png></center>"; }	else { echo "<center>" . $position . "</center>"; }?></td><td><center><?php echo $donnees['Name']; ?></center></td></td><td><center><?php echo $donnees['Cash']; ?></center></td><td><center><?php echo $donnees['Bank']; ?></center></td></tr>
			<?php
				}
			
			?>	
							</table>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>