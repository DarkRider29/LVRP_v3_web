<?php
 if(!isset($_SESSION['login'])){
?>
<div id="block-main"><div><div>
		<div class="wrapper">
		
						
						
						<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Érreur 404</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">

				<header>

										
							
							
			<h1 class="title">Érreur 404</h1>

				
		</header>
			
				
		<div class="content clearfix">
		<p>
<div class="box-warning"> La page n'existe pas !</div>
		</p>





</div>
<?php } else {
?>
<div id="block-main"><div><div>
		<div class="wrapper">
		
						
						
						<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Déconnexion</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">

				<header>

										
							
							
			<h1 class="title">Déconnectée</h1>

				
		</header>
			
				
		<div class="content clearfix">
		<p>
<?php
if(isset($_SESSION['login']) && $_SESSION['login'] != '')
{
   session_unset();
   session_destroy();
    echo '<meta http-equiv="refresh" content="0; url=?p=home">';
}
else
{
   echo '<meta http-equiv="refresh" content="0; url=?p=home">';
}
?>
		</p>





</div>
</div>
		</p>





</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
<?php } ?>