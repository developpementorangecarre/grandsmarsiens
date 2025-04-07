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

$connection=db_connect($host,$db,$username,$password);

$posted=$_POST['posted'];
$create=$_POST['create'];
$nb_vente=0;
$client=$_POST['client'];
//echo "<h1>posted $posted</h1>";
//echo "<h1>create $create</h1>";


	if ((isset($posted))and($posted==1)):

	$query="select count(*) from pm_ventes where client=$client";
	//echo $query;
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$nb_vente=$ligne[0];
		//echo "nombre de listes : $nb_liste";

// On teste si la personne n'a pas déjà une vente. 
// Si aucune liste existe on crée une liste et on ouvre une page de saise d'articles 

  echo "-- > $nb_vente $client<HR>";
		if ($nb_vente<1):
			echo "initilatisation de la vente...";		
			$query="insert into pm_ventes values(NULL,$client,NULL,1,NULL) ";
			$result=exec_sql($query,$connection);
			
			$query="select id from pm_ventes where client=$client ";
			$result=exec_sql($query,$connection);
			while ($ligne=fetch_row($result))
			{
				$vente=$ligne[0];
			}
			
			ouvre_page("vente_article.php?vente=$vente");
		endif;
	}	
	
	$query="select id from pm_ventes where client=$client order by id ";
	echo "Listes des ventes à ce client ($client) : ";
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$vente=$ligne[0];
		echo " <form method=post action=vente_article.php>";
		echo " <li> <a href=voir_vente.php?vente=$vente target=_blank>Voir la liste $vente </a>";
		echo " <input type=submit name=envoyer value=\" Ajouter des articles à la vente $vente \"></center><br>";
		echo " <input type=hidden name=vente value=$vente><br>";
		echo " <input type=hidden name=posted value=1><br>	</form>";
		
	}
	echo "<hr>";
	echo "
	<form method=post action=creer_vente.php>
	<input type=hidden name=posted value=2>
	<input type=hidden name=create value=4>
	<input type=hidden name=client value=$client><br>
	<center><input type=submit name=envoyer value=\"              CREER UNE NOUVELLE VENTE            \"></center><br>
	</form>
	";
	
elseif ((isset($create))and($create=4)):
			echo "initilatisation de la vente...";		
			$query="insert into pm_ventes values(NULL,$client,NULL,1,NULL) ";
			$result=exec_sql($query,$connection);
			
			$query="select id from pm_ventes where client=$client ";
			$result=exec_sql($query,$connection);
			while ($ligne=fetch_row($result))
			{
				$vente=$ligne[0];
			}
			
			ouvre_page("vente_article.php?vente=$vente");
else:

//****************** Formulaire principal ***/

	echo "<h1 class=\"title2\">Cr&eacute;er une nouvelle vente</h1>
	<h3>Vente de $client</h3>
	<form method=post action=nouvelle_vente.php>
	";
	echo "<hr>";
	echo "Client : <select name=client>";
	$query="select id,nom,prenom from pm_personnes order by nom asc";
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$id=$ligne[0];
		$nom=$ligne['nom'];
		$prenom=$ligne['prenom'];
		echo "<option value=$id>$nom $prenom</option>";
	}
	echo "</select>
  ";

	echo "<hr>";
	echo "
	<center><input type=submit name=envoyer value=\"              ENVOYER            \"></center><br>
	<input type=hidden name=posted value=1><br>
	</form>
	";

endif;



?>
<?php
tab_vente($connection);
echo "<br>";
tab_stock($connection);
?>
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>