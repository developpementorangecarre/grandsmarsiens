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
			
<h1 class="title2">Code Barre d'articles </h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection=db_connect($host,$port,$db,$username,$password);
echo "<form name=barcode action=print_planche_barcodes.php method=POST  target=\"_blank\">";

echo "<table class=sample><tr height=0>";
$counter=0;
while ($counter < 40)
{
  if (($counter%4==0))
  {
    $numero_ligne=($counter/4)+1;
    echo "</tr><tr><td>$numero_ligne</td>";
  }
  echo "<td><input type=text size=6 name=bar[$counter]></td>";
  $counter++;
}


echo "</table>
<input type=hidden name=posted value=1><br>
	<center><input type=submit name=envoyer value=\"              LISTE CODES à BARRES            \"></center><br>
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