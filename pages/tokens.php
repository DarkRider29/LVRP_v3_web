<?php
 if(!isset($_SESSION['login'])){
?>				
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Boutique</a>
												<strong>Tokens</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Achat de tokens </h1> </header>
	

		
	
		
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
		<div class="attention"> Veuillez toujours entrer le code dans ce formulaire et non directement dans le script starpass </div> 
<?php
$ptsachat = '100';
$lien_buy_confirm = '?p=buytokensontrue';
if(isset($_SESSION['login']))
{
	echo '<br /><center>Vous avez actuellement <b><a>';
	echo number_format($tok,1);
	echo '</b></a> Tokens.</center><br />';
	$idd = 134099;
	
	echo '<div align="center">
            L\'achat de points vous permettra de gagner  <a><b>'.$ptsachat.'</b></a> Points.<br />
	
            Les micropaiements s\'effectue par <a href="http://www.starpass.fr/" target="_blank">StarPass™</a>, éditée par <a href="http://www.bdmultimedia.fr/" target="_blank">BD Multimédia</a><br /><br />
            <b>Pour obtenir vos codes d\'accès :</b><br /><br />
Veuillez cliquer sur l\'image pour obtenir le numéro<br /><br />
<a onclick="window.open(this.href,\'StarPass\',\'width=700,height=500,scrollbars=yes,resizable=yes\');return false;" href="./allopass/achat.php"><img class="aImg" src="./allopass/starpass.png"/></a>

            <form method="post" action="'.$lien_buy_confirm.'" class="box style">
			<fieldset>
			<legend>Entrer votre code ci-dessous</legend>
              <div><label><input class="input-xlarge" type="text" name="code1" placeholder="Inserer le code ici" maxlength="8" /></label></div>
			  <button value="Login" type="submit" name="send" value="Envoyer">Envoyer</button></span>
              

			  </fieldset>
            </form>
          </div>
		  <div class="attention"> Les payements PayPal ne sont pas instantanés ! Livraison sous 24 heures. </div> 
		  <div align="center">
			Afin d\'éviter de vous faire perdre de l\'argent en payant par CB/PayPal sur starpass, nous avons décidé d\'accepter les dons par Paypal.
			<br/><br/>Sachez que <a>1,5 €</a> = <a>100</a> tokens.<br/><br/>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="ATQC67DTDE8EG">
			<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
			<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
			</form>
		  </div>
		  
		  
		  ';
}
?>
		</p>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				<?php 
				}?>