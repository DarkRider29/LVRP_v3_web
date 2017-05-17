<?php
 if(!isset($_SESSION['login'])){
?>								
					<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a>Nous rejoindre</a>
												<strong>Inscription</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Inscription au serveur </h1> </header>
	

		
	
		
	<div class="content clearfix">

				<p>
<div class="box-info">Avant toute inscription, veuillez lire attentivement <a href="?p=regles">le règlement !</a></div>

<?php 
if(isset($_POST['ok']))
{
$login = mysql_real_escape_string(htmlspecialchars(trim($_POST['name']) ) );
$pass_conf = mysql_real_escape_string(htmlspecialchars(trim($_POST['pass2']) ) );
$pass = mysql_real_escape_string(htmlspecialchars(trim($_POST['pass']) ) );
$mails = mysql_real_escape_string(htmlspecialchars(trim($_POST['mail']) ) );
$origine = mysql_real_escape_string(htmlspecialchars(trim($_POST['origine']) ) );
$languesec = mysql_real_escape_string(htmlspecialchars(trim($_POST['languesec']) ) );
$sex=mysql_real_escape_string(htmlspecialchars(trim(isset($_POST['sex']))));
//$age=$_POST['age'];
$captcha = $_POST['captcha'];
if($login=="" || $pass=="" || $mails=="" || $sex=="" || $languesec=="" || $origine=="")
{
	echo '<div class="danger"> Veuillez remplir tous les champs ! </div>';
}
else
{
if($login == $pass)
{
	echo '<div class="danger"> Votre mot de passe ne peut être votre nom de compte ! </div>';
}
else
{
if(!preg_match('~^[a-zA-Z0-9\._-]{4,20}$~',$pass_conf)){
	echo'<div class="danger">Le mot de passe est invalide ou trop court !</div>';
}
else
{
if(!preg_match('~^[a-zA-Z0-9\._-]{4,20}$~',$pass)){
	echo'<div class="danger">Le mot de passe est invalide ou trop court !</div>';
}
else
{
if(!preg_match('~^[a-zA-Z0-9\._-]{4,20}$~',$login)){
	echo'<div class="danger">Le nom de compte est invalide ou trop court !</div>';
}
else
{
$sql2 = 'SELECT * FROM lvrp_users WHERE Email="'.$mails.'"';
$req2 = mysql_query($sql2) or die('SQL Error '.$sql2.''.mysql_error());
$data2 = mysql_fetch_array($req2);
if($data2['email'] == $mails)
{
echo '<div class="danger"> L\'email est déjà utiliser ! </div>';
}
else
{
if(!preg_match('~^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$~',$mails)){

echo '<div class="danger"> L\'email est incorrect ! </div>';
}
else
{
if ($pass != $pass_conf){
echo '<div class="danger"> Les deux mots de passe sont différents ! </div>';
}
elseif($captcha != $_SESSION['captcha'])
{
echo '
<div class="danger">
Le Captcha est incorrect.
</div>';
}
else{
$sql1 = 'SELECT * FROM lvrp_users WHERE Name="'.$login.'"';
$req1 = mysql_query($sql1) or die('SQL Error !<br>'.$sql1.'<br>'.mysql_error());
$data1 = mysql_fetch_array($req1);
if($data1['Name'] == $login)
{
echo ' <div class="danger">Le nom de compte soumis est déjà utilisé.</div>';
}
else
{
$sex=mysql_real_escape_string(trim($_POST['sex']));
$passcrypt = sha1($_POST['pass']);
mysql_query("INSERT INTO lvrp_users (id,Name,Pass,Sex,Email) VALUES ('','".$login."','".$passcrypt."','".$sex."','".$mails."')")or die(mysql_error());
echo ('<div class="valid"> <b>Bravo</b> ! Vous êtes maitenant inscrit sur le serveur !</div>');
$sql = mysql_query("SELECT id FROM lvrp_users WHERE Name = '".$login."'");
$field = mysql_fetch_row($sql);
$_SESSION['login'] = $login;
$_SESSION['id'] = $field[0];
}
}
}}}}}}}}
?>
<form method="post" action="#" name="inscription" class="box style">

	<fieldset>
		<legend>Inscription au serveur</legend>
		<div><label>Prénom_Nom : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="name" type="text" maxlength="30" placeholder="Nom de compte" id="f1"/></div>
		<div><label>Mot de passe : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="pass" type="password" maxlength="32" placeholder="Mot de passe" id="f1"/></div>
		<div><label>Mot de passe  : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="pass2" type="password" maxlength="32" placeholder="Mot de passe (confirm)" id="f1"/></div>
		<div><label>Email :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="mail" maxlength="64" type="text" placeholder="Adresse Email" id="f1"/></div>
		<div><label>Sexe :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;M<input id="inputtext1" name="sex" value="1" type="radio"/>
F<input id="inputtext1" name="sex" value="2" type="radio" /></div>
		<!--<div><label>Age :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="age" maxlength="2" type="number" placeholder="Age" id="f1"/></div> -->
<div><label>Origine :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<select name="origine">
			<option value="Vice City">Vice City</option>	
			<option value="Liberty City">Liberty City</option>
			<option value="Chinatown Mars">Chinatown Wars</option>
			<option value="Los Santos">Los Santos</option>
			<option value="San Fierro"> San Fierro</option>
			<option value="Las Venturas">Las Venturas</option>
			<option value="Fort Carson">Fort Carson</option>
</select></div>
<div><label>Langue secondaire :</label> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="languesec">
			<option value="Japonais">Japonais</option>
			<option value="Espagnol">Espagnol</option>
			<option value="Russe">Russe</option>
			<option value="Arabe">Arabe</option>
			<option value="Italien">Italien</option>
			<option value="Allemand">Allemand</option>
			<option value="Anglais">Anglais</option>
			<option value="Chinois">Chinois</option>
			<option value="Portugais">Portugais</option>
			<option value="Turc">Turc</option>
			<option value="Antillais">Antillais</option>
			<option value="Mexiquain">Mexiquain</option>
			<option value="Créole">Créole</option>
			<option value="Jamaincain">Jamaicain</option>
			<option value="Coréen">Coréen</option>
			<option value="Cantonais">Cantonais</option>
			<option value="Ukrainien">Ukrainien</option>
</select></div>
		<div><label for="f1">Captcha :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="captcha/code.php" alt="securitycode"/>&nbsp;</label> 
		<input type="text" name="captcha" placeholder="Code de sécurité" id="f1"/></div>

		</fieldset>
	
	<center><button class="input" name="ok" value="S'inscrire" type="submit">S'inscrire !</button></center>
	
</form>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				
				<?php } else { ?>
				
	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Deconnexion</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Inscription </h1> </header>
	

		
	
		
	<div class="content clearfix">

<div class="danger"> Veuillez vous déconnecter pour pouvoir vous inscrire. </div>

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
				
				<?php } ?>