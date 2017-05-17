	<div id="block-main"><div><div>
		<div class="wrapper">
		
						
						
						<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>L'Équipe</strong>
												</div></section>
												<div class="content clearfix">

		<p>
					
												
																		

<div id="system">
	
	
	<article class="item">

				<header>

										
							
			<?php 
			$membres = '<center>';	
			$req = $bdd->query('SELECT * FROM `lvrp_users` ORDER BY `id`');
			while($donnees = $req->fetch())
			{
				if($donnees['AdminLevel']>=1)
				{
					if($donnees['AdminLevel'] == '1')
						$donnees['AdminLevel']= 'Modérateur Test';
					elseif ($donnees['AdminLevel'] == '2')
						$donnees['AdminLevel']= 'Modérateur';
					elseif ($donnees['AdminLevel'] == '3')
						$donnees['AdminLevel']= 'Admin';
					elseif ($donnees['AdminLevel'] == '4')
						$donnees['AdminLevel']= 'Admin Général';
					elseif ($donnees['AdminLevel'] == '5')
						$donnees['AdminLevel']= 'Gestionnaire';
					elseif ($donnees['AdminLevel'] == '6')
						$donnees['AdminLevel'] = 'Co-Fondateur';
					elseif ($donnees['AdminLevel'] == '7')
						$donnees['AdminLevel']= 'Fondateur';
						
					if($donnees['Connected']==1) $donnees['Connected'] ='Connecté <img src="images/lvrp/user_online.gif">';
					else  $donnees['Connected']='Déconnecté <img src="images/lvrp/user_offline.gif">';
					
					$membres .= '<tr>
				<td>'.$donnees['Name'].'</td>
				<td>'.$donnees['AdminLevel'].'<br /></td>
				<td>'.$donnees['Connected'].'<br /></td>
				</tr></center>';
				}
				else if($donnees['Helper'] == 1)
				{
					if($donnees['Connected']==1) $donnees['Connected'] ='Connecté <img src="images/lvrp/user_online.gif">';
					else  $donnees['Connected']='Déconnecté <img src="images/lvrp/user_offline.gif">';
						
					$membres .= '<tr>
					<td>'.$donnees['Name'].'</td>
					<td>Helpeur<br /></td>
					<td>'.$donnees['Connected'].'<br /></td>
					</tr></center>';
				}
			}
			?>
			
			<h1 class="title">L'Équipe du serveur</h1><table class="zebra">
			<br/>
			<thead>
		<tr>
			<th class="center">Prénom_nom</th>
			<th class="center">Rang</th>
			<th class="center">Connecté</th>
		</tr>
	</thead>
	
	<?php echo $membres; ?>
	</table>

</div>									


				
		</header>
			
				
		<div class="content clearfix">
<p>

</p>





</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>