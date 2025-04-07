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
			
<h1 class="title2">Recherche des personnes </h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection=db_connect($host,$db,$username,$password);

echo "
<form method=GET action=liste_personne.php>
<table>
<tr>
<td width=200 align=right>Par le nom </td><td> <input type=text name=nom></td>
</tr>
<tr><td colspan=2>
<input type=submit value=\"RECHERCHER par le NOM\">
</tr></td>
</table>
</form>
";
echo "
<form method=GET action=liste_personne.php>
<table>
<tr>
<td width=200 align=right>Le nom commence par</td><td> <input type=text name=debut></td>
</tr>
<tr><td colspan=2>
<input type=submit value=\"RECHERCHER par le DEBUT DU NOM\">
</tr></td>
</table>
</form>
";

echo "
<form method=GET action=liste_personne.php>
<table>
<tr>
<td width=200 align=right>Par le numéro </td><td> <input type=text name=id></td>
</tr>
<tr><td colspan=2>
<input type=submit value=\"RECHERCHER par le NUMERO\">
</tr></td>
</table>
</form>
";
?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>