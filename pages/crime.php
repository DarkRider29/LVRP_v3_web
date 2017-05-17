	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Classement</a>
												<strong> Crime </strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Classement crime </h1> </header>
			
	
		
	<div class="content clearfix">

<p>
					<table class="zebra">
			<thead>
		<tr>
			<th class="center">#</th>
			<th class="center">Prénom_Nom</th>
			<th class="center">Crimes</th>
		</tr>
	</thead>

	<?php

$position = 0;
$req = $bdd->query('SELECT * FROM lvrp_users_casiers ORDER BY Crimes DESC LIMIT 0, 20');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php $position++; if ($position == 1) { echo "<center><img src=./templates/lvrp/trophy/trophy_1.png></center>"; } elseif ($position == 2) { echo "<center><img src=./templates/lvrp/trophy/trophy_2.png></center>"; } elseif ($position == 3) { echo "<center><img src=./templates/lvrp/trophy/trophy_3.png></center>"; }	else { echo "<center>" . $position . "</center>"; }?></td><td><center><?php $pseudo = mysql_query ("SELECT Name FROM lvrp_users WHERE id = '".$donnees['SQLid']."' "); $row = mysql_fetch_row($pseudo); echo $row[0]; ?></center></td></td><td><center><?php echo $donnees['Crimes']; ?></center></td></tr>
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