<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

  <meta http-equiv="content-type" content="text/html; " />
  <title>Les p'tits marsiens...</title>


  <meta name="keywords" content="" />

  <meta name="description" content="" />

  <link href="printer.css" rel="stylesheet" type="text/css" />

</head>


<body>

<?php include("functions.inc.php"); include("conf.inc.php"); $liste=$_GET['liste']; $connection=db_connect($host,$db,$username,$password); $query="select p.prenom, p.nom , p.id from pm_liste_articles as l, pm_personnes as p where l.id=$liste and l.vendeur=p.id " ; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { $vendeur="$ligne[0] $ligne[1]"; $id=$ligne[2]; } echo " <table class=sample> <tr><td>Liste</td><td>Vendeur</td><td>Nom</td></tr><tr> <td><h2>N° $liste </h2></td> <td><h2>N° $id </h2></td> <td><h3>$vendeur </h3></td> </tr> </table>"; $bgcolor="#FFFFFF"; // echo "<h3>Articles laissés à l'association</h3>"; echo "<table class=sample> <tr bgcolor=#FFFFFF> <td> numero </td> <td> designation </td> <td> type </td> <td> taille </td> <td> prix </td> <td> net vendeur </td> </tr>"; $query="select a.numero as numero, a.designation as designation, a.prix as prix, a.prix_vendeur as prix_vendeur, ty.type as type, ta.taille as taille, e.etat as etat_libelle from pm_articles as a, pm_types as ty, pm_tailles as ta, pm_etat_articles as e where a.liste=$liste and a.taille=ta.id and a.type=ty.id and e.id=a.etat and a.etat=1 order by numero"; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { $numero=$ligne['numero']; $designation=$ligne['designation']; $type=$ligne['type']; $taille=$ligne['taille']; $prix=$ligne['prix']; $prix_vendeur=$ligne['prix_vendeur']; $etat_libelle=$ligne['etat_libelle']; echo "<tr bgcolor=$bgcolor> <td> $numero </td> <td> $designation </td> <td> $type </td> <td> $taille </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364;</td> </tr>"; $counter++; if (($counter%2) == 0): $bgcolor="#FFFFFF"; else: $bgcolor="#FFFFFF"; endif; } $query="select sum(prix),sum(prix_vendeur) from pm_articles where liste=$liste and etat=1 "; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { echo "<tr> <td colspan=4> TOTAL </td> <td> $ligne[0] &#8364;</td> <td> $ligne[1] &#8364;</td> </tr>"; } echo "</table>"; // --------------------- ARTICLES REPRIS --------------------- // echo "<h3>Articles repris</h3>"; echo "<table class=sample> <tr bgcolor=#FFFFFF> <td> numero </td> <td> designation </td> <td> type </td> <td> taille </td> <td> prix </td> <td> net vendeur </td> </tr>"; $query="select a.numero as numero, a.designation as designation, a.prix as prix, a.prix_vendeur as prix_vendeur, ty.type as type, ta.taille as taille, e.etat as etat_libelle from pm_articles as a, pm_types as ty, pm_tailles as ta, pm_etat_articles as e where a.liste=$liste and a.taille=ta.id and a.type=ty.id and e.id=a.etat and a.etat=3 order by numero"; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { $numero=$ligne['numero']; $designation=$ligne['designation']; $type=$ligne['type']; $taille=$ligne['taille']; $prix=$ligne['prix']; $prix_vendeur=$ligne['prix_vendeur']; $etat_libelle=$ligne['etat_libelle']; echo "<tr bgcolor=$bgcolor> <td> $numero </td> <td> $designation </td> <td> $type </td> <td> $taille </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364;</td> </tr>"; $counter++; if (($counter%2) == 0): $bgcolor="#FFFFFF"; else: $bgcolor="#FFFFFF"; endif; } $query="select sum(prix),sum(prix_vendeur) from pm_articles where liste=$liste and etat=3 "; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { echo "<tr> <td colspan=4> TOTAL </td> <td> $ligne[0] &#8364;</td> <td> $ligne[1] &#8364;</td> </tr>"; } echo "</table>"; // --------------------- ARTICLES vendus dans le MOIS --------------------- // $mois = date("Y-m-01"); echo "<h3>Articles vendus </h3>"; echo "<table class=sample> <tr bgcolor=#FFFFFF> <td> numero </td> <td> designation </td> <td> type </td> <td> taille </td> <td> prix </td> <td> net vendeur </td> </tr>"; $query="select a.numero as numero, a.designation as designation, a.prix as prix, a.prix_vendeur as prix_vendeur, ty.type as type, ta.taille as taille, e.etat as etat_libelle from pm_articles as a, pm_types as ty, pm_tailles as ta, pm_etat_articles as e ,pm_ventes as v where a.liste=$liste and a.taille=ta.id and a.type=ty.id and e.id=a.etat and a.etat=2 and v.date > '$mois' and v.id=a.vente order by numero"; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { $numero=$ligne['numero']; $designation=$ligne['designation']; $type=$ligne['type']; $taille=$ligne['taille']; $prix=$ligne['prix']; $prix_vendeur=$ligne['prix_vendeur']; $etat_libelle=$ligne['etat_libelle']; echo "<tr bgcolor=$bgcolor> <td> $numero </td> <td> $designation </td> <td> $type </td> <td> $taille </td> <td> $prix &#8364;</td> <td> $prix_vendeur &#8364;</td> </tr>"; $counter++; if (($counter%2) == 0): $bgcolor="#FFFFFF"; else: $bgcolor="#FFFFFF"; endif; } $query="select sum(a.prix),sum(a.prix_vendeur) from pm_articles as a , pm_ventes as v where a.liste=$liste and a.etat=2 and v.id=a.vente and v.date > '$mois' and v.id=a.vente "; $result=exec_sql($query,$connection); while ($ligne=fetch_row($result)) { echo "<tr> <td colspan=4> TOTAL </td> <td> $ligne[0] &#8364;</td> <td> $ligne[1] &#8364;</td> </tr>"; } echo "</table>"; ?>
<br />

<table class="sample" width="1000">

  <tbody>

    <tr>

      <td colspan="2">Lu et approuv&eacute;</td>

    </tr>

    <tr height="70">

      <td valign="top" width="50%">Date</td>

      <td valign="top" width="50%">Signature</td>

    </tr>

  </tbody>
</table>

L'association Les P'tits Marsiens dispose de moyens informatiques
destin&eacute;s &agrave; g&eacute;rer plus facilement les
ventes et les d&eacute;p&ocirc;ts d'articles. <br />

Les informations enregistr&eacute;es sont
r&eacute;serv&eacute;es &agrave; l&rsquo;usage de
l'association et ne peuvent &ecirc;tre communiqu&eacute;es
qu&rsquo;aux adh&eacute;rents assurant la gestion de
l'assocation. <br />

Conform&eacute;ment aux articles 39 et suivants de la loi
n&deg; 78-17 du 6 janvier 1978 relative &agrave;
l&rsquo;informatique, aux fichiers et aux libert&eacute;s,
toute personne peut obtenir communication et, le cas
&eacute;ch&eacute;ant, rectification ou suppression des
informations la concernant, en s&rsquo;adressant par courrier ou
par e-mail &agrave; l'association. <br />

</body>
</html>
