					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Voter pour le serveur</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Vote en cours </h1> </header>
	

		
	
		
	<div class="content clearfix">

<?php
$date = time();
$ecartminute = ($date - $row['HasVoted1'])/120;

if ($ecartminute > 120)
{
$id = $row['id'];
$tokens = $row['Tokens'] +1;
$vote = $row['Votes'] +1 ;

$req = "UPDATE lvrp_users SET Tokens ='$tokens', HasVoted1 = '$date', Votes = $vote WHERE id = '$id' ";
mysql_query($req);
echo"
<script type='text/javascript'>
window.location.replace('http://www.root-top.com/topsite/gta/in.php?ID=2382');
</script>
";

}
else
{
$restant = round(120 - $ecartminute, 0);

?><div class="attention">Vous devez attendre <b><?php echo $restant; ?> minutes</b> avant de pouvoir voter !</div>
  <div class="danger"> Si vous avez voter sans remplir le <b>captcha</b>, vous serez bannis pendant <b>24H</b> du serveur ! </div>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php }
				?>