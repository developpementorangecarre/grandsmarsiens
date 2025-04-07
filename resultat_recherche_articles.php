<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<SCRIPT>

function confirmation_post(liste,id,designation,formulaire) { 
var msg = ' Confirmer la suppression de : '+designation; 
if (confirm(msg)) {
    document.getElementById(formulaire).submit(); 
  }
  else
  {
  document.location.replace('depot_articles.php?liste='+liste);   
  }
} 

</SCRIPT>

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

$liste = $_GET['liste'] ?? null;
$marque = $_GET['marque'] ?? null;
$type = $_GET['type'] ?? null;
$designation_courte = $_GET['designation_courte'] ?? null;
$designation = $_GET['designation'] ?? null;
$couleur = $_GET['couleur'] ?? null;
$taille = $_GET['taille'] ?? null;
$etat_liste = $_GET['etat_liste'] ?? null;

$connection = db_connect($host, $port, $db, $username, $password);

$criteres = "";
$params = [];

if ($liste !== null) {
    $criteres .= " AND a.liste = ?";
    $params[] = $liste;
}
if (!empty($marque)) {
    $criteres .= " AND a.marque = ?";
    $params[] = $marque;
}
if (!empty($type)) {
    $criteres .= " AND a.type = ?";
    $params[] = $type;
}
if (!empty($designation_courte)) {
    $criteres .= " AND a.designation_courte = ?";
    $params[] = $designation_courte;
}
if (!empty($designation)) {
    $criteres .= " AND a.designation LIKE ?";
    $params[] = "%$designation%";
}
if (!empty($couleur)) {
    $criteres .= " AND a.couleur = ?";
    $params[] = $couleur;
}
if (!empty($taille)) {
    $criteres .= " AND a.taille = ?";
    $params[] = $taille;
}
if (!empty($etat_liste)) {
    $criteres .= " AND l.etat = ?";
    $params[] = $etat_liste;
}

$query = "SELECT COUNT(*) as compte FROM pm_articles as a 
          JOIN pm_types as ty ON a.type = ty.id
          JOIN pm_tailles as ta ON a.taille = ta.id
          JOIN pm_designations_courtes as d ON a.designation_courte = d.id
          JOIN pm_couleurs as c ON a.couleur = c.id
          JOIN pm_marques as m ON a.marque = m.id
          JOIN pm_etat_articles as e ON e.id = a.etat
          JOIN pm_liste_articles as l ON l.id = a.liste
          WHERE 1=1 $criteres";

try {
    $stmt = $connection->prepare($query);
    $stmt->execute($params);
    $compte = $stmt->fetch(PDO::FETCH_ASSOC)['compte'];

    if ($compte < 2000) {
        echo "<h1 class=\"title2\">Résultat de recherche </h1><h3>Résultat</h3>";

        $query = "SELECT a.liste, a.id, a.numero, a.designation, a.prix, a.prix_vendeur, e.etat as etat_libelle, e.id as etat, ty.type, ty.image as type_image, ta.taille, d.libelle as designation_courte, c.libelle as couleur, m.libelle as marque
                  FROM pm_articles as a
                  JOIN pm_types as ty ON a.type = ty.id
                  JOIN pm_tailles as ta ON a.taille = ta.id
                  JOIN pm_designations_courtes as d ON a.designation_courte = d.id
                  JOIN pm_couleurs as c ON a.couleur = c.id
                  JOIN pm_marques as m ON a.marque = m.id
                  JOIN pm_etat_articles as e ON e.id = a.etat
                  JOIN pm_liste_articles as l ON l.id = a.liste
                  WHERE 1=1 $criteres ORDER BY a.id";
        
        $stmt = $connection->prepare($query);
        $stmt->execute($params);

        echo "<table class=sample>
                <tr bgcolor=#FFFFBB>
                    <td>Liste</td><td>N°</td><td>Désignation</td><td>Désignation longue</td><td>Couleur</td>
                    <td>Marque</td><td>Type</td><td>Taille</td><td>Prix</td><td>Net vendeur</td><td>État</td>
                </tr>";

        $bgcolor = "#FFFFFF";
        while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $color_etat = match ($ligne['etat']) {
                1 => "AAFFAA",
                2 => "FF0000",
                3 => "AAAAFF",
                default => "FFFFFF",
            };

            echo "<tr bgcolor=$bgcolor>
                    <td><a href=depot_articles.php?liste={$ligne['liste']} style='text-decoration:none;'>{$ligne['liste']}</a></td>
                    <td>{$ligne['numero']}</td>
                    <td>{$ligne['designation_courte']}</td>
                    <td>{$ligne['designation']}</td>
                    <td>{$ligne['couleur']}</td>
                    <td>{$ligne['marque']}</td>
                    <td><img src='{$ligne['type_image']}'></td>
                    <td>{$ligne['taille']}</td>
                    <td>{$ligne['prix']} €</td>
                    <td>{$ligne['prix_vendeur']} €</td>
                    <td bgcolor=$color_etat>{$ligne['etat_libelle']}</td>
                </tr>";

            $bgcolor = ($bgcolor == "#FFFFFF") ? "#EEEEEE" : "#FFFFFF";
        }
        echo "</table>$compte Articles correspondant aux critères";
    } else {
        echo "<br><h3>$compte articles correspondants<br>Le nombre d'articles est trop important pour ces critères</h3>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
    echo "<p>Une erreur est survenue lors de l'exécution de la requête.</p>";
}
?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php");  ?>


</body>
</html>