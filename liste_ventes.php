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
			
<h1 class="title2">Les ventes</h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

$tri = $_GET['tri'] ?? null;

if (isset($tri)) {
    if ($tri == 11) {
        $tri_sql = "ORDER BY v.id asc";
    } elseif ($tri == 12) {
        $tri_sql = "ORDER BY v.id desc";
    } elseif ($tri == 21) {
        $tri_sql = "ORDER BY nombre_articles asc";
    } elseif ($tri == 22) {
        $tri_sql = "ORDER BY nombre_articles desc";
    } elseif ($tri == 31) {
        $tri_sql = "ORDER BY somme asc";
    } elseif ($tri == 32) {
        $tri_sql = "ORDER BY somme desc";
    } elseif ($tri == 61) {
        $tri_sql = "ORDER BY p.nom asc";
    } elseif ($tri == 62) {
        $tri_sql = "ORDER BY p.nom desc";
    } else {
        $tri_sql = "ORDER BY v.id desc";
    }
} else {
    $tri_sql = "ORDER BY v.id desc";
}

try {
    $connection = db_connect($host, $port, $db, $username, $password);

    $query = "
        SELECT v.id, p.id AS client_id, p.prenom, p.nom, p.adresse, p.ville, 
               COUNT(a.id) AS nombre_articles, SUM(a.prix) AS somme, e.etat
        FROM pm_ventes AS v
        JOIN pm_personnes AS p ON v.client = p.id
        JOIN pm_articles AS a ON v.id = a.vente
        JOIN pm_etat_vente AS e ON e.id = v.etat
        GROUP BY a.vente
        $tri_sql
    ";

    $stmt = $connection->prepare($query);
    $stmt->execute();
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table class=sample>
        <td> N° vente <br>(<a href=liste_ventes.php?tri=11>-</a> / <a href=liste_ventes.php?tri=12>+</a>) </td>
        <td> Nb articles <br>(<a href=liste_ventes.php?tri=21>-</a> / <a href=liste_ventes.php?tri=22>+</a>) </td>
        <td> Montant <br>(<a href=liste_ventes.php?tri=31>-</a> / <a href=liste_ventes.php?tri=32>+</a>) </td>
        <td> N° Client</td>
        <td> Nom <br>(<a href=liste_ventes.php?tri=61>-</a> / <a href=liste_ventes.php?tri=62>+</a>) </td>
        <td> Adresse </td>
        <td> Ville </td>
        <td> Statut </td>
    ";

    $counter = 0;
    $bgcolor = "#FFFFFF";

    foreach ($ventes as $ligne) {
        $id = $ligne['id'];
        $client_id = $ligne['client_id'];
        $prenom = $ligne['prenom'];
        $nom = $ligne['nom'];
        $adresse = $ligne['adresse'];
        $ville = $ligne['ville'];
        $nb_articles = $ligne['nombre_articles'];
        $somme = $ligne['somme'];
        $etat = $ligne['etat'];

        echo "<tr bgcolor=$bgcolor>
            <td><center><a href=vente_article.php?vente=$id>__ $id __</a></center></td>
            <td> $nb_articles </td>
            <td> $somme € </td>
            <td> $client_id </td>
            <td> $nom $prenom </td>
            <td> $adresse </td>
            <td> $ville </td>
            <td> $etat</td>
        </tr>";

        $counter++;
        $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des ventes : " . $e->getMessage();
}

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>