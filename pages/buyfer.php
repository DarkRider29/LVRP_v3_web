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
if($row['Connected'] == 1){
echo '<div class="danger">Vous devez être deconnecté ! </div>';
}
else
{
$playername = $row['Name'];
$tokens = $row['Tokens'];
$renamexe = $row['PointsRename'] + 1;
$changnumxe = $row['ChangeNum'] + 1;
$respectexe = $row['Respect'] + 2;
$viptime = $row['VipTime']+2880;
$tokensvalue = 99;
$tokensvalues = 100;
if ($tokensvalues > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalues;
if ($tokens > $tokensvalue){
$req = "UPDATE lvrp_users SET Tokens ='$tokenscredit', VipTime = '$viptime', PointsRename = '$renamexe', ChangeNum='$changnumxe', Respect = '$respectexe' WHERE Name = '$playername'";
mysql_query($req);
if($row['DonateRank'] < 1)
	{mysql_query("UPDATE lvrp_users SET DonateRank=1 WHERE Name = '$playername'");}
	
if($row['CarUnLock4'] == 0)
	{mysql_query('UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='.$playername.'');}
elseif($row['CarUnLock5'] == 0)
	{mysql_query('UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='.$playername.'');}
elseif($row['CarUnLock6'] == 0)
	{mysql_query('UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='.$playername.'');}
	
log_Buy($playername,"Pack VIP FER");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}
}
?>
</p>

 </div>
					
					</div></section>
							
							
				</div>
				<?php
				}?>