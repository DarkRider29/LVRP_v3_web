<?php if(!isset($_SESSION['login'])){?>

<aside id="sidebar-a" class="grid-box">
						

<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">
	
<div class="badge badge-hot"></div>

	<h3 class="module-title"><span class="color">Vote </span>& Gagne</h3>
<center><a href="http://www.root-top.com/topsite/gta/in.php?ID=2382"><img src="./images/lvrp/banner.gif"></a></center>
<br />
<center><a class="button-more" href="?p=apvote">Vote & Gagne</a></center>

	</div></div>
	
	<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">

<?php
$sampquery = new SampQuery('37.59.39.200','7777');
if($sampquery->isOnline()) 
{
	$sinfos = $sampquery->getInfo();
	echo '<center><h3 class="module-title"><span class="color">Statut</span> Du Serveur</h3>'.$sinfos['players']. '/' .$sinfos['maxplayers'].'<br />';
	echo ''.$sinfos['gamemode'].'<br />';
	echo ''.$sinfos['mapname'].'<br /><br />';
	echo '<a href="samp://37.59.39.200:7777"><big>Se connecter au serveur</big></a></center>';
}
else
	{echo '<center><a><big>Le serveur est Hors Ligne</big></a></center>';}
?>
</div></div>

<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">
	
<div class="badge badge-top"></div>

	<center><h3 class="module-title"><span class="color">Serveur </span>TS3</h3>
	<big>Ip : 5.39.0.117</big></center><br />
	<center><a href="ts3server://5.39.0.117:9987"><big>Se connecter au serveur</big></a></center>

	</div></div>
</aside>
								
						
			</div>
		</div>
	</div></div></div>
			<?php } else { ?>
<aside id="sidebar-a" class="grid-box">
						
								<div class="grid-box width100 grid-v">
								<div class="module mod-box  deepest">

		<h3 class="module-title"><span class="color">Mon </span>personnage<br /></span></h3>	<ul class="menu menu-sidebar">
		<li class="level1 item3"><a href="?p=profil" class="level1"><span>Mon profil </span></a></li>
		<li class="level1 item4"><a href="?p=faction" class="level1"><span>Faction</span></a></li>
		<li class="level1 item5"><a href="?p=biens" class="level1"><span>Biens </span></a></li>
		<li class="level1 item6"><a href="?p=job" class="level1"><span>Job</span></a></li>
		<li class="level1 item6"><a href="?p=inventaire" class="level1"><span>Inventaire</span></a></li>
		<li class="level1 item6"><a href="?p=vip" class="level1"><span>V.I.P</span></a></li>
		<li class="level1 item6"><a href="?p=casier" class="level1"><span>Casier</span></a></li>
		</ul>		
</div></div>

<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">
	
<div class="badge badge-hot"></div>

	<h3 class="module-title"><span class="color">Vote </span>& Gagne</h3>
<center><a href="http://www.root-top.com/topsite/gta/in.php?ID=2382"><img src="./images/lvrp/banner.gif"></a></center>
<br />
<center><a class="button-more" href="?p=apvote">Vote & Gagne</a></center>

	</div></div>
	
	<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">
<?php
$sampquery = new SampQuery('37.59.39.200','7777');
if($sampquery->isOnline()) 
{
	$sinfos = $sampquery->getInfo();
	echo '<center><h3 class="module-title"><span class="color">Statut</span> Du Serveur</h3>'.$sinfos['players']. '/' .$sinfos['maxplayers'].'<br />';
	echo ''.$sinfos['gamemode'].'<br />';
	echo ''.$sinfos['mapname'].'<br /><br />';
	echo '<a href="samp://37.59.39.200:7777"><big>Se connecter au serveur</big></a></center>';
}
else
	{echo '<center><a><big>Le serveur est Hors Ligne</big></a></center>';}
?>
</div></div>

<div class="grid-box width100 grid-v"><div class="module mod-box  deepest">
	
<div class="badge badge-top"></div>

	<center><h3 class="module-title"><span class="color">Serveur </span>TS3</h3>
	<big>Ip : 5.39.0.117</big></center><br />
	<center><a href="ts3server://5.39.0.117:9987"><big>Se connecter au serveur</big></a></center>

	</div></div>
</aside>
<?php }
?>						
			</div>
		</div>
	</div></div></div>