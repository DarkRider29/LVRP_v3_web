<?php if(!isset($_SESSION['login'])){?>

	<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a href="?p=home">Acceuil</a>
												<strong>Page introuvable</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Érreur 404 </h1> </header>
	

		
	
		
	<div class="content clearfix">

<div class="danger"> La page que vous souhaitez visitez n'existe pas ! </div>
Redirection vers <a href="?p=home"> l'acceuil dans 3 secondes </a>
<meta http-equiv="refresh" content="3; URL=?p=home">

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
			<?php } else { $admin = $row['AdminLevel'] > 4; if ($admin){ ?>	
			
		<div id="main" class="grid-block">
			
				<div id="maininner" class="grid-box">
				
							
										<section id="content" class="grid-block"><div>
					
												<section id="breadcrumbs">
												<div class="breadcrumbs">
												<a><?php echo $row['Name'];?></a>
												<strong>Bannir un joueur</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Bannir un joueur </h1> </header>
	

		
	
		
<div class="content clearfix">
<p>
<?php
 
if(isset($_POST['ok']))
{
$bannername = $row['Name'];
$name_ip = mysql_real_escape_string(htmlspecialchars(trim($_POST['name_ip'])));
$banunban = mysql_real_escape_string(htmlspecialchars(trim($_POST['banunban'])));
$raison = mysql_real_escape_string(htmlspecialchars(trim($_POST['raison'])));
if ($banunban == "banip"){
$row2 = mysql_query("SELECT * FROM lvrp_users WHERE ip='".$name_ip."'");
$row3 = mysql_fetch_array($row2);
$sql1 = 'SELECT * FROM lvrp_users_bans WHERE ip="'.$name_ip.'"';
$req1 = mysql_query($sql1) or die('SQL Error !<br>'.$sql1.'<br>'.mysql_error());
$data1 = mysql_fetch_array($req1);
$ip = $row3['Ip'];
$id = $row3['id'];
if ($ip == $data1['Ip']){
echo '<div class="info">L\'ip "'.$ip.'" est d&eacute;j&agrave; banni du serveur.</div>';
}
else
{
$req = ("INSERT INTO lvrp_users_bans (`SQLid`,`BannedBy`,`Ip`,`Reason`) VALUES ('$id','$bannername','$ip','$raison')")or die(mysql_error());
mysql_query($req);
echo '<div class="info">L\'ip "'.$ip.'" a &eacute;t&eacute; bannis avec succ&egrave;s </div>';
}}
if ($banunban == "banjoueur"){
$row2 = mysql_query("SELECT * FROM lvrp_users WHERE Name='".$name_ip."'");
$row3 = mysql_fetch_array($row2);
$ip = $row3['Ip'];
$id = $row3['id'];
$sql1 = 'SELECT * FROM lvrp_users_bans WHERE ip="'.$ip.'"';
$req1 = mysql_query($sql1) or die('SQL Error !<br>'.$sql1.'<br>'.mysql_error());
$data1 = mysql_fetch_array($req1);
if ($ip == $data1['Ip']){
echo '<div class="info">L\'ip du joueur "'.$name_ip.'" est d&eacute;j&agrave; banni du serveur.</div>';
}
else
{
$req = ("INSERT INTO lvrp_users_bans (`SQLid`,`BannedBy`,`Ip`,`Reason`) VALUES ('$id','$bannername','$ip','$raison')")or die(mysql_error());
mysql_query($req);
echo '<div class="info">L\'ip "'.$ip.'" du joueur "'.$name_ip.'" a &eacute;t&eacute; bannis avec succ&egrave;s </div>';
}}
if ($banunban == "unbanjoueur"){
$row2 = mysql_query("SELECT * FROM lvrp_users WHERE Name='".$name_ip."'");
$row3 = mysql_fetch_array($row2);
$ip = $row3['Ip'];
$id = $row3['id'];
$sql1 = 'SELECT * FROM lvrp_users_bans WHERE ip="'.$ip.'"';
$req1 = mysql_query($sql1) or die('SQL Error !<br>'.$sql1.'<br>'.mysql_error());
$data1 = mysql_fetch_array($req1);
if ($ip != $data1['Ip']){
echo '<div class="info">L\'ip du joueur "'.$name_ip.'" n\'est pas banni du serveur.</div>';
}
else
{
$req = ("DELETE FROM lvrp_users_bans WHERE SQLid = '$id' ")or die(mysql_error());
mysql_query($req);
echo '<div class="info">L\'ip "'.$ip.'" du joueur "'.$name_ip.'" a &eacute;t&eacute; unban avec succ&egrave;s </div>';
}}
if ($banunban == "unbanip"){
$row2 = mysql_query("SELECT * FROM lvrp_users WHERE ip='".$name_ip."'");
$row3 = mysql_fetch_array($row2);
$sql1 = 'SELECT * FROM lvrp_users_bans WHERE ip="'.$name_ip.'"';
$req1 = mysql_query($sql1) or die('SQL Error !<br>'.$sql1.'<br>'.mysql_error());
$data1 = mysql_fetch_array($req1);
$ip = $row3['Ip'];
$id = $row3['id'];
if ($ip != $data1['Ip']){
echo '<div class="info">L\'ip "'.$ip.'" n\'est pas banni du serveur.</div>';
}
else
{
$req = ("DELETE FROM lvrp_users_bans WHERE SQLid = '$id'")or die(mysql_error());
mysql_query($req);
echo '<div class="info">L\'ip "'.$ip.'" a &eacute;t&eacute; unban avec succ&egrave;s </div>';
}}}
?>
</p>
<form method="post" action="#" name="ok" class="box style">
<fieldset>
		<legend>Bannir ou debannir</legend>
		<div><label>Prénom ou IP : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> <input name="name_ip" type="text" placeholder="Prénom ou IP" id="f1"/></div>
		<div><label>Services :</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<select name="banunban">
			<option value="banip">Bannir une IP</option>	
			<option value="banjoueur">Bannir un joueur</option>
			<option value="unbanjoueur">Unban un joueur</option>
			<option value="unbanip">Unban une IP</option>
</select></div>
<div><label for="f5">Raison:</label><br/><textarea name="raison" rows="5" cols="30" id="f5">Raison du ban</textarea></div>

	<center><button class="input" name="ok" value="argent" type="submit">Bannir l'ip ou le joueur !</button></center>
</center>
	</fieldset></form>
	
	<br />
	<h1> Les personnages bannis du serveur </h1>
	<table class="zebra">
			<thead>
		<tr>
			<th class="center">#</th>
			<th class="center">Pseudo</th>
			<th class="center">Bannis par</th>
			<th class="center">Ip</th>
			<th class="center">Raison</th>
			<th class="center">Date</th>
			<th class="center">Time</th>
		</tr>
	</thead>

	<?php

$position = 0;
$req = $bdd->query('select * FROM lvrp_users_bans ORDER BY Date DESC LIMIT 0, 1000');
while ($donnees = $req->fetch())
				{
			?>


			<tr><td>
			<?php $position++; if ($position == 1) { echo "<center><img src=./templates/lvrp/trophy/trophy_1.png></center>"; } elseif ($position == 2) { echo "<center><img src=./templates/lvrp/trophy/trophy_2.png></center>"; } elseif ($position == 3) { echo "<center><img src=./templates/lvrp/trophy/trophy_3.png></center>"; }	else { echo "<center>" . $position . "</center>"; }?></td><td><center><?php $pseudo = mysql_query ("SELECT Name FROM lvrp_users WHERE id = '".$donnees['SQLid']."' "); $row2 = mysql_fetch_row($pseudo); echo $row2[0]; ?></center></td></td><td><center><?php echo $donnees['BannedBy']; ?></center></td><td><center><?php echo $donnees['Ip']; ?></center></td><td><center><?php echo $donnees['Reason']; ?></center></td><td><center><?php echo $donnees['Date']; ?></center></td><td><center><?php echo $donnees['Time']; ?></center></td></tr>
			<?php
				}
			
			?>	
							</table>

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
												<strong>Page introuvable</strong>
												</div></section>
												
						

<div id="system">
	
	
	<article class="item">
			
				
		<div class="content clearfix">
		
	<header> <h1 class="title"> Érreur 404 </h1> </header>
	

		
	
		
	<div class="content clearfix">

<div class="danger"> La page que vous souhaitez visitez n'existe pas ! </div>
Redirection vers <a href="?p=home"> l'acceuil dans 3 secondes </a>
<meta http-equiv="refresh" content="3; URL=?p=home">

</div>


				
		
	</article>

</div>						
					</div></section>
							
							
				</div>
<?php } }
?>	
