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

<div id="page">
	

<?php include('menu_gauche.php'); ?>		

	
<div id="content">
		
<div><img src="images/banner.jpg" alt="" height="220" width="740" /></div>
		
<div class="boxed">
			
<h1 class="title2">Gérer les personnes</h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

?>
<h3>
<ul>
<li> <a href=nouvelle_personne.php>Créer une nouvelle personne</a>
<li> <a href=liste_personne.php>Lister/modifier les personnes</a>
<li> <a href=recherche_personne.php>Rechercher des personnes</a>
</ul>
</h3>
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>