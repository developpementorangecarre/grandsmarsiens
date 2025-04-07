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

$tri = isset($_GET['tri']) ? (int) $_GET['tri'] : null; // Sécurisation et conversion en entier

if ($tri !== null) {
    switch ($tri) {
        case 11:
            $tri_sql = "ORDER BY l.bourse ASC";
            break;
        case 12:
            $tri_sql = "ORDER BY l.bourse DESC";
            break;
        case 21:
            $tri_sql = "ORDER BY nombre_articles ASC";
            break;
        case 22:
            $tri_sql = "ORDER BY nombre_articles DESC";
            break;
        case 31:
            $tri_sql = "ORDER BY p.id ASC";
            break;
        case 32:
            $tri_sql = "ORDER BY p.id DESC";
            break;
        case 61:
            $tri_sql = "ORDER BY p.nom ASC";
            break;
        case 62:
            $tri_sql = "ORDER BY p.nom DESC";
            break;
        default:
            $tri_sql = "ORDER BY l.id DESC"; // Valeur par défaut si tri invalide
            break;
    }
} else {
    $tri_sql = "ORDER BY l.id DESC"; // Valeur par défaut si 'tri' n'est pas défini
}

$connection=db_connect($host,$port,$db,$username,$password);


$query = "
SELECT l.id, p.id AS vendeur_id, p.prenom, p.nom, p.adresse, p.ville, 
       COUNT(a.id) AS nombre_articles, l.code, l.bourse
FROM pm_liste_articles AS l
JOIN pm_personnes AS p ON l.vendeur = p.id
JOIN pm_articles AS a ON l.id = a.liste
GROUP BY l.id
$tri_sql
";

$stmt = $connection->prepare($query);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$articles) {
    echo "<p>Aucun résultat trouvé.</p>";
    exit;
}

echo "<form name='barcode' action='print_barcodes.php' method='POST' target='_blank'>";
echo "<table class='sample'>
<tr>
    <th>N° liste <br>(<a href='barcode_articles.php?tri=11'>-</a> / <a href='barcode_articles.php?tri=12'>+</a>)</th>
    <th>Nb articles <br>(<a href='barcode_articles.php?tri=21'>-</a> / <a href='barcode_articles.php?tri=22'>+</a>)</th>
    <th>N° vendeur <br>(<a href='barcode_articles.php?tri=31'>-</a> / <a href='barcode_articles.php?tri=32'>+</a>)</th>
    <th>Prénom</th>
    <th>Nom <br>(<a href='barcode_articles.php?tri=61'>-</a> / <a href='barcode_articles.php?tri=62'>+</a>)</th>
    <th>Adresse</th>
    <th>Ville</th>
    <th>Sélection</th>
</tr>";

$counter = 0;
$bgcolor = "#FFFFFF";

foreach ($articles as $ligne) {
    $id = htmlspecialchars($ligne['id'], ENT_QUOTES, 'UTF-8');
    $vendeur_id = htmlspecialchars($ligne['vendeur_id'], ENT_QUOTES, 'UTF-8');
    $prenom = htmlspecialchars($ligne['prenom'], ENT_QUOTES, 'UTF-8');
    $nom = htmlspecialchars($ligne['nom'], ENT_QUOTES, 'UTF-8');
    $adresse = htmlspecialchars($ligne['adresse'], ENT_QUOTES, 'UTF-8');
    $ville = htmlspecialchars($ligne['ville'], ENT_QUOTES, 'UTF-8');
    $nb_articles = (int) $ligne['nombre_articles'];
    $code = htmlspecialchars($ligne['code'], ENT_QUOTES, 'UTF-8');
    $bourse = htmlspecialchars($ligne['bourse'], ENT_QUOTES, 'UTF-8');

    echo "<tr bgcolor='$bgcolor'>
        <td><center><a href='depot_articles.php?liste=$id'>$code $bourse</a></center></td>
        <td>$nb_articles</td>
        <td>$vendeur_id</td>
        <td>$prenom</td>
        <td>$nom</td>
        <td>$adresse</td>
        <td>$ville</td>
        <td><input type='checkbox' name='bar[]' value='$id'></td>
    </tr>";

    $counter++;
    $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
}

echo "</table>
<input type='hidden' name='posted' value='1'><br>
<center><input type='submit' name='envoyer' value='              LISTE CODES à BARRES            '></center><br>
</form>";


?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>