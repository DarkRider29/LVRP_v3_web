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
$dollars = mysql_real_escape_string(htmlspecialchars(trim($_POST['dollars'])));
$cashs = $row['Cash'] + $dollars;
if ($dollars == 1000){
$tokensvalues = 25;
$tokensvalue = 24;}
if ($dollars == 2500){
$tokensvalues = 50;
$tokensvalue = 49;}
if ($dollars == 5000){
$tokensvalues = 100;
$tokensvalue = 99;}
if ($dollars == 10000){
$tokensvalues = 200;
$tokensvalue = 199;}
if ($dollars == 20000){
$tokensvalues = 300;
$tokensvalue = 299;}
if ($tokensvalue > $tokens){
echo '<div class="danger">Vous n\'avez pas assez de tokens pour cet achat ! </div>';
}
$tokenscredit = $tokens - $tokensvalues;
if ($tokens > $tokensvalue){
$req = "UPDATE lvrp_users set Tokens ='$tokenscredit', Cash = '$cashs' WHERE Name = '$playername'";
mysql_query($req);
log_Buy($playername,"Cash '$cashs'");
echo"
<script type='text/javascript'>
window.location.replace('index.php?p=valid');
</script>
";
}}}
?>
<div class="info"> Les packs VIP servent à payer les hébergements pour la continuité du serveur, en aucuns cas ils sont bénéfiques à des fins personnelles.<br />
<b>Livraison immédiate après achat !</b>


 </div>

<div class="box-content">		<center><h2>Pack VIP Fer<br /></h2></center>
<br />
<hr />
	<dl class="separator">
		<dt><center><br /><br /><br />&nbsp;&nbsp;&nbsp;<img src="./images/lvrp/cart.png" height="64px" width="64px"><a class="button-more" href="?p=buyfer">Acheter</a></center><br /><center></dt>
		<dd>

		<ul class="check">
			<li>1 Slot de véhicule en plus</li>
			<li>1 Rename</li>
			<li>1 Changement de numéro personnalisé</li>
			<li>2 Points de respect</li>
			<li>Canal VIP </li>
			<li>Possibilité de mettre son armure à 50 toutes les 60 minutes </li>
			<li>Accès aux PM + Chat VIP </li>
			<li>Titre 'VIP' sur les canaux IG et sur le forum </li>
		
</ul>
<p align="right"><img src="./images/lvrp/time.png"> <a><b>48 heures</b></a> de jeu</p>	<p align="right"><img src="./images/lvrp/money.png"> <a><b>100</b></a> tokens </p>		</ul>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>
		<hr class="dotted">
	
<div class="box-content">		<center><h2>Pack VIP Argent<br /></h2></center>
<br />
<hr />

	<dl class="separator">
		<dt><center><br /><br /><br /><br />&nbsp;&nbsp;&nbsp;<img src="./images/lvrp/cart.png" height="64px" width="64px"><a class="button-more" href="?p=buyargent">Acheter</a></center><br /><center></dt>
		<dd>

		<ul class="check">
			<li>1 Slot de véhicule en plus</li>
			<li>2 Renames</li>
			<li>1 Changement de numéro personnalisé</li>
			<li>1 Changement de plaque</li>
			<li>4 Points de respect</li>
			<li>+ Intéret 5 % aux payes</li>
			<li>Canal VIP </li>
			<li>Possibilité de mettre son armure à 50 toutes les 60 minutes </li>
			<li>Accès aux PM + Chat VIP </li>
			<li>Titre 'VIP' sur les canaux IG et sur le forum </li>
		
</ul>
<p align="right"><img src="./images/lvrp/time.png"> <a><b>96 heures</b></a> de jeu</p>	<p align="right"><img src="./images/lvrp/money.png"> <a><b>300</b></a> tokens </p>		</ul>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>

<div class="box-content">		<center><h2>Pack VIP OR<br /></h2></center>
<br />
<hr />

	<dl class="separator">
		<dt><center><br /><br /><br /><br />&nbsp;&nbsp;&nbsp;<img src="./images/lvrp/cart.png" height="64px" width="64px"><a class="button-more" href="?p=buyor">Acheter</a></center><br /><center></dt>
		<dd>

		<ul class="check">
			<li>2 Slots de véhicule en plus</li>
			<li>3 Renames</li>
			<li>1 Changement de numéro personnalisé</li>
			<li>2 Changements de plaque</li>
			<li>8 Points de respect</li>
			<li>+ Intéret 10% aux payes</li>
			<li>Canal VIP </li>
			<li>Possibilité de mettre son armure à 50 toutes les 60 minutes </li>
			<li>Accès aux PM + Chat VIP </li>
			<li>Titre 'VIP' sur les canaux IG et sur le forum </li>
		
</ul>
<p align="right"><img src="./images/lvrp/time.png"> <a><b>192 heures</b></a> de jeu</p>	<p align="right"><img src="./images/lvrp/money.png"> <a><b>500</b></a> tokens </p>		</ul>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>

		<hr class="dotted">
	
<div class="box-content">		<center><h2>Pack VIP Diamant<br /></h2></center>
<br />
<hr />

	<dl class="separator">
		<dt><center><br /><br /><br /><br /><br />&nbsp;&nbsp;&nbsp;<img src="./images/lvrp/cart.png" height="64px" width="64px"><a class="button-more" href="?p=buydiamant">Acheter</a></center><br /><center></dt>
		<dd>

		<ul class="check">
			<li>3 Slots de véhicules en plus</li>
			<li>4 Renames</li>
			<li>2 Changement de numéro personnalisé</li>
			<li>2 Changements de plaques</li>
			<li>16 Points de respect</li>
			<li>+ Intéret 20 % aux payes</li>
			<li>1 Changement d'age</li>
			<li>Accès au sac VIP (+2 Slots d'arme et 750 Kg Max.)</li>
			<li>Canal VIP </li>
			<li>Possibilité de mettre son armure à 50 toutes les 60 minutes </li>
			<li>Accès aux PM + Chat VIP </li>
			<li>Titre 'VIP' sur les canaux IG et sur le forum </li>
		
</ul>
	
<p align="right"><img src="./images/lvrp/time.png"> <a><b>384 heures</b></a> de jeu</p>	<p align="right"><img src="./images/lvrp/money.png"> <a><b>800</b></a> tokens </p>		</ul>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>
		<hr class="dotted">

<div class="box-content">		<center><h2>Argent<br /></h2></center>
<br />
<hr />

	<dl class="separator">
		<dt><center><br /><img src="./images/lvrp/tokens.png" height="128px" width="128px"><br /><center></dt>
		<dd>
<form method="post" action="?p=boutique" name="dollars" class="box style">
<fieldset>
		<div><label>Nombre de dollars :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<select name="dollars">
			<option value="1000">1000 $ - 25 tokens</option>	
			<option value="2500">2500 $ - 50 tokens</option>
			<option value="5000">5000 $ - 100 tokens</option>
			<option value="10000">10 000 $ - 200 tokens</option>
			<option value="20000"> 20 000 $ - 300 tokens</option>
</select></div>

	<center><button class="input" name="ok" value="argent" type="submit">Acheter de l'argent !</button></center>
</center>
	</fieldset></form>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>
		<hr class="dotted" />
<div class="box-content">		<center><h2>Autres services<br /></h2></center>
<br />
<hr />

	<dl class="separator">
		<dt><center><img src="./images/lvrp/service.png" height="128px" width="128px"><br /><center></dt>
		<dd>
<form method="post" action="?p=services" name="service" class="box style">
<fieldset>
		<div><label>Services :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<select name="servv">
			<option value="1">1 Rename - 50 tokens</option>	
			<option value="2">1 Changement de numéro - 50 tokens</option>
			<option value="3">1 Changement de plaque - 50 tokens</option>
			<option value="4">1 Respect - 25 tokens</option>
			<option value="5">1 Level - 400 tokens</option>
			<option value="6">1 Changement d'age - 50 tokens</option>
			<option value="7">1 Changement de sexe - 50 tokens</option>
			</select>
</div>
	<center><button class="input" name="ok" value="Service" type="submit">Acheter le service !</button></center>
</fieldset></form>


			</dd>
			
	</dl>
<hr />

		</p>
		
</div>		
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php
				}?>