<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

  <meta http-equiv="content-type" content="text/html; " />
  <title>Les p'tits marsiens...</title>


  <meta name="keywords" content="" />

  <meta name="description" content="" />

  <link href="default.css" rel="stylesheet" type="text/css" />

</head>


<body>

<div id="page"><?php include('menu_gauche.php'); ?>
<div id="content">
<div><img src="images/banner.jpg" alt="" height="220" width="740" /></div>

<div class="boxed">
<h1 class="title2">Stats Personnes</h1>

<?php include("functions.inc.php");
include("conf.inc.php"); 
$liste=$_GET['liste']; 
$connection=db_connect($host,$db,$username,$password); 
$query="SELECT count(distinct v.client) FROM pm_ventes as v" ; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
{ 
$resultat="$ligne[0] "; 
	echo "Clients : $resultat <br>";
} 

$query="SELECT count(distinct v.client) 
	FROM pm_ventes as v, pm_personnes as p 
	where v.client=p.id and p.ville='Saint Mars du Désert'" ; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
{ 
$resultat="$ligne[0] "; 
	echo "Clients sur Saint Mars du désert: $resultat <br>";
} 


$query="SELECT count(distinct l.vendeur) FROM pm_liste_articles as l" ; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
{ 
$resultat="$ligne[0] "; 
	echo "Vendeurs : $resultat <br>";
} 

$query="SELECT count(distinct l.vendeur)
	FROM pm_liste_articles as l, pm_personnes as p 
	where l.vendeur=p.id and p.ville='Saint Mars du Désert'" ; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
{ 
$resultat="$ligne[0] "; 
	echo "Vendeurs sur Saint Mars du désert: $resultat <br>";
} 



?>
</div>

</div>

<div style="clear: both;">&nbsp;</div>

</div>

<?php include("footer.php"); ?>
</body>
</html>
