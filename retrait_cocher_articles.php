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

$liste = $_GET['liste'] ?? null;
$checked = $_GET['checked'] ?? null;
$posted = $_POST['posted'] ?? null;
$bourse_active = $_GET['bourse_active'] ?? null;

if (isset($bourse_active)) {
    $reference_article = $_GET['reference_article'] ?? '';
    $matches = [];
    preg_match('/([0-9]+)([A-Za-z]+)/', $reference_article, $matches);

    if (!empty($matches)) {
        $code = $matches[1];
        $bourse = $matches[2];
        $liste = codeetbourse2liste($code, $bourse, $connection);
    }
}

$bourse = liste2bourse($liste, $connection);
$code = liste2code($liste, $connection);

if (isset($posted)) {
    $liste = $_POST['liste'] ?? null;
    $coche = $_POST['coche'] ?? [];

    if ($liste !== null) {
        $query = "UPDATE pm_articles SET etat = 1 WHERE etat <> 2 AND liste = :liste";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
        $stmt->execute();

        foreach ($coche as $value) {
            $query = "UPDATE pm_articles SET etat = 3 WHERE id = :id AND liste = :liste";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':id', $value, PDO::PARAM_INT);
            $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}

// Récupération du vendeur et de sa catégorie
$query = "SELECT p.prenom, p.nom, p.categorie 
          FROM pm_liste_articles AS l 
          JOIN pm_personnes AS p ON l.vendeur = p.id
          WHERE l.id = :liste";

$stmt = $connection->prepare($query);
$stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
$stmt->execute();

$vendeur = null;
$categorie = null;

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $vendeur = $row['prenom'] . ' ' . $row['nom'];
    $categorie = $row['categorie'];
}

// Récupération du pourcentage en fonction de la catégorie
if ($categorie !== null) {
    $query = "SELECT c.pourcent 
              FROM pm_categories AS c 
              WHERE c.id = :categorie";

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':categorie', $categorie, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pourcent = $row['pourcent'];
    }
}

$bourse = liste2bourse($liste, $connection);
$code = liste2code($liste, $connection);

echo "<h1 class=\"title2\">Liste d'article N°$code$bourse 
        <a href=\"retrait_articles.php?liste=$liste\" target=\"_blank\">
        <img src=\"images/presse78.gif\" align=\"center\" border=\"0\"> 
        Voir le reçu imprimable</a></h1>
<h3>Liste de $vendeur</h3>";

$counter = 0;
$bgcolor = "#FFFFFF";

echo "<table class=\"sample\">
    <tr bgcolor=\"#FFFFBB\">
        <td>n°</td>
        <td>designation</td>
        <td>designation longue</td>
        <td>couleur</td>
        <td>marque</td>
        <td>type</td>
        <td>taille</td>
        <td>prix</td>
        <td>net vendeur</td>
        <td>etat</td>
        <td valign=\"center\">
            <form action=\"retrait_cocher_articles.php\" method=\"get\">
                <input type=\"hidden\" name=\"checked\" value=\"checked\">
                <input type=\"hidden\" name=\"liste\" value=\"$liste\">
                <input type=\"submit\" value=\"X\">
            </form>
        </td>
    </tr>
    <form method=\"post\" action=\"retrait_cocher_articles.php\">";

$query = "SELECT a.id AS id, a.numero AS numero, a.designation AS designation, a.prix AS prix, 
                 a.prix_vendeur AS prix_vendeur, ty.type AS type, ty.image AS type_image,
                 ta.taille AS taille, d.libelle AS designation_courte, c.libelle AS couleur,
                 m.libelle AS marque, e.etat AS etat_libelle, e.id AS etat
          FROM pm_articles AS a
          LEFT JOIN pm_types AS ty ON a.type = ty.id
          LEFT JOIN pm_tailles AS ta ON a.taille = ta.id
          LEFT JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
          LEFT JOIN pm_couleurs AS c ON a.couleur = c.id
          LEFT JOIN pm_marques AS m ON a.marque = m.id
          LEFT JOIN pm_etat_articles AS e ON e.id = a.etat
          WHERE a.liste = :liste AND a.etat <> 2
          ORDER BY a.numero";

$stmt = $connection->prepare($query);
$stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
$stmt->execute();

while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $ligne['id'];
    $numero = $ligne['numero'];
    $designation = $ligne['designation'];
    $type = $ligne['type'];
    $type_image = $ligne['type_image'];
    $taille = $ligne['taille'];
    $prix = $ligne['prix'];
    $prix_vendeur = $ligne['prix_vendeur'];
    $etat_libelle = $ligne['etat_libelle'];
    $etat = $ligne['etat'];
    $designation_courte = $ligne['designation_courte'];
    $couleur = $ligne['couleur'];
    $marque = $ligne['marque'];

    // Attribution de la couleur de l'état
    $color_etat = match ($etat) {
        1 => "#AAFFAA",
        2 => "#FF0000",
        3 => "#AAAAFF",
        default => "#FFFFFF"
    };

    echo "<tr bgcolor=\"$bgcolor\">
            <td>$numero</td>
            <td>$designation_courte</td>
            <td>$designation</td>
            <td>$couleur</td>
            <td>$marque</td>
            <td><img src=\"$type_image\"></td>
            <td>$taille</td>
            <td>$prix €</td>
            <td>$prix_vendeur €</td>
            <td bgcolor=\"$color_etat\">$etat_libelle</td>";

    if ($etat == 1) {
        echo "<td bgcolor=\"$color_etat\"><input type=\"checkbox\" name=\"coche[]\" value=\"$id\"></td>";
    } elseif ($etat == 3) {
        echo "<td bgcolor=\"$color_etat\"><input type=\"checkbox\" name=\"coche[]\" value=\"$id\" checked></td>";
    } else {
        echo "<td bgcolor=\"$color_etat\"></td>";
    }

    echo "</tr>";

    $counter++;
    $bgcolor = ($counter % 2) == 0 ? "#FFFFFF" : "#EEEEEE";
}

$query = "SELECT SUM(prix) AS total_prix, SUM(prix_vendeur) AS total_prix_vendeur
          FROM pm_articles 
          WHERE liste = :liste";

$stmt = $connection->prepare($query);
$stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
$stmt->execute();

if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td colspan=\"7\">TOTAL</td>
            <td>{$ligne['total_prix']} €</td>
            <td>{$ligne['total_prix_vendeur']} €</td>
          </tr>";
}

echo "</table>";
echo "<input type=\"submit\" name=\"envoyer\" value=\"Valider\"></center>";
echo "<input type=\"hidden\" name=\"liste\" value=\"$liste\">";
echo "<input type=\"hidden\" name=\"posted\" value=\"1\"></form>";

echo "<form action=\"retrait_cocher_articles.php\" method=\"get\">
        <input type=\"submit\" name=\"envoyer\" value=\"Annuler\"></center>
        <input type=\"hidden\" name=\"liste\" value=\"$liste\">
        <input type=\"hidden\" name=\"posted\" value=\"1\">
      </form>";

echo "<h3><a href=\"retrait_articles.php?liste=$liste\" target=\"_blank\">
        <img src=\"images/presse78.gif\" align=\"center\" border=\"0\"> Voir le reçu imprimable</a></h3>";

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>