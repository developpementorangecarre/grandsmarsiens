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
			
<h1 class="title2">Les listes d'articles </h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection = db_connect($host, $port, $db, $username, $password);

$tri_sql = "ORDER BY l.id DESC";

$bourse_selection = filter_input(INPUT_GET, 'bourse_selection', FILTER_SANITIZE_SPECIAL_CHARS);
$bourse_selection = ($bourse_selection !== "") ? $bourse_selection : null;

$tri = filter_input(INPUT_GET, 'tri', FILTER_VALIDATE_INT);

if (!is_null($tri)) {
    switch ($tri) {
        case 11: $tri_sql = "ORDER BY l.bourse, l.code ASC"; break;
        case 12: $tri_sql = "ORDER BY l.bourse, l.code DESC"; break;
        case 21: $tri_sql = "ORDER BY nombre_articles ASC"; break;
        case 22: $tri_sql = "ORDER BY nombre_articles DESC"; break;
        case 31: $tri_sql = "ORDER BY p.id ASC"; break;
        case 32: $tri_sql = "ORDER BY p.id DESC"; break;
        case 61: $tri_sql = "ORDER BY p.nom ASC"; break;
        case 62: $tri_sql = "ORDER BY p.nom DESC"; break;
        case 81: $tri_sql = "ORDER BY l.etat ASC"; break;
        case 82: $tri_sql = "ORDER BY l.etat DESC"; break;
    }
}

$critere = "";
$params = [];

if (!empty($bourse_selection)) {
    $critere = "AND l.bourse = :bourse";
    $params[':bourse'] = $bourse_selection;
}

$query = "
SELECT l.id, p.id AS vendeur_id, p.prenom, p.nom, p.adresse, p.ville, COUNT(a.id) AS nombre_articles, e.etat, l.code, l.bourse
FROM pm_liste_articles AS l
JOIN pm_personnes AS p ON l.vendeur = p.id
JOIN pm_articles AS a ON l.id = a.liste
JOIN pm_etat_liste AS e ON l.etat = e.id
WHERE 1=1 
$critere
GROUP BY a.liste
$tri_sql
";

try {
    $stmt = $connection->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
}

echo "<table class='sample'>
<tr>
    <td>N° liste (<a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=11'>-</a> / <a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=12'>+</a>)</td>
    <td>Nb articles (<a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=21'>-</a> / <a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=22'>+</a>)</td>
    <td>N° vendeur (<a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=31'>-</a> / <a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=32'>+</a>)</td>
    <td>Prénom</td>
    <td>Nom (<a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=61'>-</a> / <a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=62'>+</a>)</td>
    <td>Adresse</td>
    <td>Ville</td>
    <td>État (<a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=81'>-</a> / <a href='?bourse_selection=" . htmlspecialchars($bourse_selection ?? '') . "&tri=82'>+</a>)</td>
</tr>";

foreach ($result as $ligne) {
    echo "<tr>
    <td><a href='depot_articles.php?liste=" . htmlspecialchars($ligne['id']) . "'>__ " . htmlspecialchars($ligne['code']) . " " . htmlspecialchars($ligne['bourse']) . " __</a></td>
    <td>" . htmlspecialchars($ligne['nombre_articles']) . "</td>
    <td>" . htmlspecialchars($ligne['vendeur_id']) . "</td>
    <td>" . htmlspecialchars($ligne['prenom']) . "</td>
    <td>" . htmlspecialchars($ligne['nom']) . "</td>
    <td>" . htmlspecialchars($ligne['adresse']) . "</td>
    <td>" . htmlspecialchars($ligne['ville']) . "</td>
    <td>" . htmlspecialchars($ligne['etat']) . "</td>
    </tr>";
}

echo "</table>";

echo "<form method='get' action='liste_listes_articles.php'>
    Filtre sur une bourse : <select name='bourse_selection'>";

$query = "SELECT id, statut FROM pm_bourses ORDER BY id ASC";
try {
    $stmt = $connection->query($query);
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = htmlspecialchars($ligne['id']);
        $selected = ($bourse_selection === $id) ? "selected" : "";
        echo "<option value='$id' $selected>$id</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
}

echo "</select>
<input type='submit' name='envoyer' value='ENVOYER'>
</form>";

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>