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
			
<!-- <h3>Caisse</h3> -->

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection=db_connect($host,$port,$db,$username,$password);

$posted=$_POST['posted'];
$create=$_POST['create'];
$nb_liste=0;
$vendeur=$_POST['vendeur'];
$bourse=$_POST['bourse'];
echo "<h1>posted $posted</h1>";
echo "<h1>create $bourse</h1>";

if ((isset($create))and($create=4)):
			echo "création de la liste...";		
			$liste=creer_liste($vendeur,$bourse,$connection);
			while ($ligne=fetch_row($result))
			{
				$liste=$ligne[0];
			}
			
			ouvre_page("nouveau_article.php?liste=$liste");

	

endif;



?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>