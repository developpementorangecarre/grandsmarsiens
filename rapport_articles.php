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
<h1 class="title2">D&eacute;pots d'articles</h1>

<?php include("functions.inc.php");
include("conf.inc.php"); $liste=$_GET['liste']; $connection=db_connect($host,$db,$username,$password); 
$query="select p.prenom, p.nom , p.id from pm_liste_articles as l, pm_personnes as p where l.id=$liste and l.vendeur=p.id " ; 

$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
	{ $vendeur="$ligne[0] $ligne[1]"; 
		$id=$ligne[2]; 
	} 
echo " <table class=sample width=100%> <tr><td align=center><h3>Rapport par état</h3></td></tr> </table><br>"; 
$bgcolor="#FFFFBB"; 
echo "<table class=sample> <tr bgcolor=#FFFFFF> <td><b> Etat </b></td> <td><b> Nombre </b></td> <td><b> prix </b></td> <td><b> net vendeur </b></td> </tr>"; 
$query="select count(a.id) as compte,e.etat as etat, sum(a.prix) as prix,sum(a.prix_vendeur) as prix_vendeur from pm_articles as a, pm_etat_articles as e where e.id=a.etat and a.etat=1 group by a.etat"; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
	{ 
	$compte=$ligne['compte']; 
	$etat=$ligne['etat']; 
	$prix=$ligne['prix']; 
	$prix_vendeur=$ligne['prix_vendeur']; 
	echo "<tr bgcolor=$bgcolor> <td> $etat </td> <td> $compte </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364; </td> </tr>"; 
	$counter++; 
	if (($counter%2) == 0): 
		$bgcolor="#FFFFBB"; 
		else: $bgcolor="#FFFFFF"; 
	endif; } 
	$query="select sum(prix),sum(prix_vendeur) from pm_articles "; 
	$result=exec_sql($query,$connection); 
	/*
	while ($ligne=fetch_row($result)) 
	{ 
		echo "<tr> <td colspan=2> TOTAL </td> <td> $ligne[0] &#8364;</td> 
			<td> $ligne[1] &#8364;</td> </tr>"; 
	} */
	echo "</table>"; 

echo "<h1> ventes à la bourse du printemps	</H1>";

$bgcolor="#FFFFBB"; 
echo "<table class=sample> <tr bgcolor=#FFFFFF> <td><b> Etat </b></td> <td><b> Nombre </b></td> <td><b> prix </b></td> <td><b> net vendeur </b></td> </tr>"; 
$query="select count(a.id) as compte,e.etat as etat, sum(a.prix) as prix,sum(a.prix_vendeur) as prix_vendeur 
			from pm_articles as a, pm_etat_articles as e , pm_ventes as v
			where e.id=a.etat and a.etat=2 and v.date > '2010-01-01 10:30:00' and v.id=a.vente and v.date < '2010-09-01 10:30:00'
			group by a.etat"; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
	{ 
	$compte=$ligne['compte']; 
	$etat=$ligne['etat']; 
	$prix=$ligne['prix']; 
	$prix_vendeur=$ligne['prix_vendeur']; 
	echo "<tr bgcolor=$bgcolor> <td> $etat </td> <td> $compte </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364; </td> </tr>"; 
	$counter++; 
	if (($counter%2) == 0): 
		$bgcolor="#FFFFBB"; 
		else: $bgcolor="#FFFFFF"; 
	endif; } 
	$query="select sum(prix),sum(prix_vendeur) from pm_articles "; 
	$result=exec_sql($query,$connection); 
	/*
	while ($ligne=fetch_row($result)) 
	{ 
		echo "<tr> <td colspan=2> TOTAL </td> <td> $ligne[0] &#8364;</td> 
			<td> $ligne[1] &#8364;</td> </tr>"; 
	} */
	echo "</table>"; 

echo "<h1> ventes à la bourse de septembre</H1>";

$bgcolor="#FFFFBB"; 
echo "<table class=sample> <tr bgcolor=#FFFFFF> <td><b> Etat </b></td> <td><b> Nombre </b></td> <td><b> prix </b></td> <td><b> net vendeur </b></td> </tr>"; 
$query="select count(a.id) as compte,e.etat as etat, sum(a.prix) as prix,sum(a.prix_vendeur) as prix_vendeur 
			from pm_articles as a, pm_etat_articles as e , pm_ventes as v
			where e.id=a.etat and a.etat=2 and v.date > '2010-09-01 10:30:00' and v.id=a.vente and  v.date < '2010-10-01 10:30:00'
			group by a.etat"; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
	{ 
	$compte=$ligne['compte']; 
	$etat=$ligne['etat']; 
	$prix=$ligne['prix']; 
	$prix_vendeur=$ligne['prix_vendeur']; 
	echo "<tr bgcolor=$bgcolor> <td> $etat </td> <td> $compte </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364; </td> </tr>"; 
	$counter++; 
	if (($counter%2) == 0): 
		$bgcolor="#FFFFBB"; 
		else: $bgcolor="#FFFFFF"; 
	endif; } 
	$query="select sum(prix),sum(prix_vendeur) from pm_articles "; 
	$result=exec_sql($query,$connection); 
	/*
	while ($ligne=fetch_row($result)) 
	{ 
		echo "<tr> <td colspan=2> TOTAL </td> <td> $ligne[0] &#8364;</td> 
			<td> $ligne[1] &#8364;</td> </tr>"; 
	} */
	echo "</table>"; 
	
	//-----------------------//-----------------------//-----------------------//-----------------------//----------------------- echo "<br><br> <table class=sample width=100% > <tr><td align=center><h3>Rapport par type et âge</h3></td></tr> </table><br>"; $bgcolor="#FFFFFF"; echo "<table class=sample> <tr bgcolor=#FFFFAA> <td> Type </td> <td> Age </td> <td> Nombre </td> <td> prix </td> <td> net vendeur </td> </tr>"; $query="select count(a.id) as compte,ty.type as type, ta.taille as taille, sum(a.prix) as prix,sum(a.prix_vendeur) as prix_vendeur from pm_articles as a, pm_tailles as ta, pm_types as ty where ty.id=a.type and ta.id=a.taille group by a.type, a.taille"; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { $compte=$ligne['compte']; $taille=$ligne['taille']; $type=$ligne['type']; $prix=$ligne['prix']; $prix_vendeur=$ligne['prix_vendeur']; echo "<tr bgcolor=$bgcolor> <td> $type </td> <td> $taille </td> <td> $compte </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364; </td> </tr>"; $counter++; if (($counter%2) == 0): $bgcolor="#A0FFB0"; else: $bgcolor="#FFFFFF"; endif; } $query="select sum(prix),sum(prix_vendeur) from pm_articles "; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { echo "<tr> <td colspan=2> TOTAL </td> <td> $ligne[0] &#8364;</td> <td> $ligne[1] &#8364;</td> </tr>"; } echo "</table>"; $heure[0]='2008-05-17 09:00:00';

$heure[1]='2008-05-17 09:30:00';
$heure[2]='2008-05-17 10:00:00';
$heure[3]='2008-05-17 10:30:00';
$heure[4]='2008-05-17 11:00:00';
$heure[5]='2008-05-17 11:30:00';
$heure[6]='2008-05-17 12:00:00';
$heure[7]='2008-05-17 12:30:00';
$heure[8]='2008-05-17 13:00:00';
$heure[9]='2008-05-17 13:30:00';
$heure[10]='2008-05-17 14:00:00';
$heure[11]='2008-05-17 14:30:00';
$heure[11]='2008-05-17 15:00:00';
$heure[12]='2008-05-17 15:30:00';
$heure[13]='2008-05-17 16:00:00';
$heure[14]='2008-05-17 16:30:00';
$heure[15]='2008-05-17 17:00:00';
$heure[16]='2008-05-17 17:30:00';
$counter=0;
while ($counter<16)
{
$date1=$heure[$counter];
$date2=$heure[$counter+1];
$query="select count(*) from pm_ventes where date > $date1 and date < $date2 ";
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
{ 
echo "$date1";
echo $ligne[0];
echo "<hr>";
}
$counter++;
}
?>
</div>

</div>

<div style="clear: both;">&nbsp;</div>

</div>

<?php include("footer.php"); ?>
</body>
</html>
