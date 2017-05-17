						<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Nouveautés</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
	<?php
		$result = mysql_query('SELECT * FROM lvrp_site_news ORDER BY `id` DESC LIMIT 0,4');
		while($dNews = mysql_fetch_array($result))
		{ 
			echo '
			<div class="content clearfix">
			<header> <h1 class="title">'.$dNews['Title'].'</h1> </header>
			<div class="content clearfix">
			<p>'.$dNews['Contenu'].'</p>
			<hr class="dotted"> 
			</div>
			<p align="right">
				<small>Ecrit le '.date('d/m/Y',$dNews['Date']).' par '.$dNews['Autor'].'</small>. 	
			</p>';
		}
	?>				
		
	</article>

</div>						
					</div></section>
							
							
				</div>