<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 
  <meta http-equiv="content-type" content="text/html; " />
<?php
include("functions.inc.php");
include("conf.inc.php");
$connection=db_connect($host,$port,$db,$username,$password);

echo "";
$valeur=getval("title",$connection);
echo"<title>$valeur</title>";
?>
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
			
<h1 class="title2">Bienvenue chez les grands marsiens !</h1>
			
<p><strong><br /></strong><em></em></p>



<?php


$query="select p.id, p.nom
		from pm_personnes as p order by id desc " ;
$result=exec_sql($query,$connection);
?>
<h3>DEPOT</h3>
<ul>
<li> <a href=nouvelle_liste.php>DEPOT : Ajouter des articles</a>
<li> <a href=liste_listes_articles.php>DEPOT : Les listes d'articles</a>
<li> <a href=barcode_articles.php>Codes à Barres d'articles</a>
<?php
echo"
<form method=get action=liste_depots.php>
	Imprimer les dépôts de la bourse : <select name=bourse_active>";
	
	$query="select id,statut from pm_bourses order by id asc";
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$id=$ligne['id'];
		$statut=$ligne['statut'];
		if ( $statut == 1 ):
			echo "<option value=$id selected>$id</option>";
		else:
			echo "<option value=$id>$id</option>";
		endif;
	}
	echo "</select>";


echo "<input type=submit name=envoyer value=\" OK \">
</form>";

?>
</ul>
<h3>VENTE</h3>
<ul>
<li> <a href=nouvelle_vente.php>VENTE : Faire une vente</a>
<li> <a href=liste_ventes.php>VENTE : Liste des ventes</a>
</ul>
<h3>PERSONNE</h3>
<ul>
<li> <a href=recherche_personne.php>Rechercher des personnes</a>
<li> <a href=nouvelle_personne.php>Créer une nouvelle personne</a>
<li> <a href=liste_personne.php>Lister/modifier les données </a>
</ul>

<h3>BOURSES</h3>
<ul>
<li> 
<?php
echo"
<form method=get action=liste_listes_articles.php>
	Liste rattachées à la bourse : <select name=bourse_selection>";
	
	$query="select id,statut from pm_bourses order by id asc";
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$id=$ligne['id'];
		$statut=$ligne['statut'];
		if ( $statut == 1 ):
			echo "<option value=$id selected>$id</option>";
		else:
			echo "<option value=$id>$id</option>";
		endif;
	}
	echo "</select>";


echo "<input type=submit name=envoyer value=\" OK \">
</form>";

?>
<?php
echo"
<form method=get action=retrait_articles.php>
	Imprimer les reçus de la bourse : <select name=bourse_active>";
	
	$query="select id,statut from pm_bourses order by id asc";
	$result=exec_sql($query,$connection);
	while ($ligne=fetch_row($result))
	{
		$id=$ligne['id'];
		$statut=$ligne['statut'];
		if ( $statut == 1 ):
			echo "<option value=$id selected>$id</option>";
		else:
			echo "<option value=$id>$id</option>";
		endif;
	}
	echo "</select>";


echo "<input type=submit name=envoyer value=\" OK \">
</form>";

?>
<li> <a href=gerer_bourses.php>Gérer les bourses</a>

<br>



<h3><a href=recherche_article.php>Recherche d'article</a></h3>

<h3><a href=emails.php>Liste de mails</a></h3>

<h3><a href=emailscolonnes.php>Liste de mails et noms</a></h3>

<hr>

<?php
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
tab_vente($connection);
echo "<br>";
tab_stock($connection);
echo "<br>";
tab_stock_par_taille_type($connection);
?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>