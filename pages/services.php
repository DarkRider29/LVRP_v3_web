<?php
 if(!isset($_SESSION['login'])){
?>				
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Boutique</a>
												<strong>Achat en jeu</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Boutique </h1> </header>
	

		
	
		
	<div class="content clearfix">

		<p>
<div class="info"> Veuillez vous connectez pour accéder à cette page . </div>
		</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php } else {
				?>
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Boutique</a>
												<strong>Achat</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Boutique </h1> </header>
	

		
	
		
	<div class="content clearfix">

<p>

<?php
if(isset($_POST['ok']))
{
if($row['Connected'] == 1){
echo '<div class="danger">Vous devez être deconnecté ! </div>';
}
else
{
$playername = $row['Name'];
$tokens = $row['Tokens'];
$rservices = mysql_real_escape_string(htmlspecialchars(trim($_POST['servv'])));
$renamexe = $row['PointsRename'] + 1;
$changnumexe = $row['ChangeNum'] + 1;
$plaquexe = $row['ChangePlaque'] + 1;
$respectexe = $row['Respect'] + 1;
$levelexe = $row['Level'] + 1;
$changesexxe = $row['ChangeSex'] + 1;
$changeagexe = $row['ChangeAge'] + 1;
if ($rservices == 1){
$tokensvalues = 50;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalues;
if ($tokens > 49){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', PointsRename = '$renamexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 Rename");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 2){
$tokensvalue = 50;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > 49){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', ChangeNum = '$changnumexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 ChangeNum");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 3){
$tokensvalue = 50;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > 49){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', ChangePlaque = '$plaquexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 ChangePlaque");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 4){
$tokensvalue = 25;
if ($tokensvalue > 24){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > $tokensvalue){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', Respect = '$respectexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 Respect");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 5){
$tokensvalue = 400;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > 399){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', Level = '$levelexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 Level");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 6){
$tokensvalue = 50;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > 49){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', ChangeAge = '$changeagexe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 ChangeSexe");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
if ($rservices == 7){
$tokensvalue = 50;
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalue;
if ($tokens > 49){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', ChangeSex = '$changesexxe' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"1 ChangeAge");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}
}}
else
{
echo '<div class="attention"> Veuillez s&eacute;lectionner une option </div>';
}
?>
</p>

 </div>
					
					</div></section>
							
							
				</div>
				<?php
				}?>