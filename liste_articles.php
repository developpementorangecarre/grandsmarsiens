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

<?php
include("functions.inc.php");
include("conf.inc.php");

$liste = filter_input(INPUT_GET, 'liste', FILTER_VALIDATE_INT);
if (!$liste) {
    die("Erreur : paramètre liste invalide.");
}

$connection = db_connect($host, $port, $db, $username, $password);

try {
    $query = "SELECT p.prenom, p.nom, p.id 
              FROM pm_liste_articles AS l
              JOIN pm_personnes AS p ON l.vendeur = p.id 
              WHERE l.id = :liste";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    
    $vendeur = '';
    $id = null;
    if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vendeur = $ligne['prenom'] . ' ' . $ligne['nom'];
        $id = $ligne['id'];
    }

    // Récupération du nombre d'articles avec état 1
    $query = "SELECT COUNT(*) AS compte
              FROM pm_articles AS a 
              WHERE a.liste = :liste AND a.etat = 1";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    
    $nombre_articles = 0;
    if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre_articles = $ligne['compte'];
    }

    $bourse = liste2bourse($liste, $connection);
    $code = liste2code($liste, $connection);

} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
}
	
// Affichage des informations générales
echo "
<table class='sample'>
<tr><td>Liste</td><td>Vendeur</td><td>Nom</td><td>- Payé -</td><td>Nombre d'articles</td></tr>
<tr>
    <td><h2>N° $code$bourse</h2></td>
    <td><h2>N° $id</h2></td>
    <td><h3>$vendeur</h3></td>
    <td><h3></h3></td>
    <td><h3>$nombre_articles</h3></td>
</tr>
</table>";

echo "<table class='sample'>
<tr bgcolor='#FFFFFF'>
    <td>n°</td>
    <td>Designation courte</td>
    <td>Designation</td>
    <td>Couleur</td>
    <td>Marque</td>
    <td>Type</td>
    <td>Taille</td>
    <td>Prix</td>
    <td>Net vendeur</td>
</tr>";

try {
    // Requête pour récupérer les articles
    $query = "
    SELECT 
        a.numero, a.designation, a.prix, a.prix_vendeur,
        ty.type, ty.image as type_image,
        ta.taille, 
        d.libelle as designation_courte, 
        c.libelle as couleur,
        m.libelle as marque  
    FROM pm_articles AS a
    JOIN pm_types AS ty ON a.type = ty.id
    JOIN pm_tailles AS ta ON a.taille = ta.id
    JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
    JOIN pm_couleurs AS c ON a.couleur = c.id
    JOIN pm_marques AS m ON a.marque = m.id
    WHERE a.liste = :liste
    ORDER BY a.numero";

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    $counter = 0;

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bgcolor = ($counter % 2 == 0) ? "#F0F0F0" : "#FFFFFF";

        echo "<tr bgcolor='$bgcolor'>
        <td>{$ligne['numero']}</td>
        <td>{$ligne['designation_courte']}</td>
        <td>{$ligne['designation']}</td>
        <td>{$ligne['couleur']}</td>
        <td>{$ligne['marque']}</td>
        <td><img src='{$ligne['type_image']}'></td>
        <td>{$ligne['taille']}</td>
        <td>{$ligne['prix']} €</td>
        <td>{$ligne['prix_vendeur']} €</td>
        </tr>";

        $counter++;
    }
} catch (PDOException $e) {
    echo "<p>Erreur lors de la récupération des articles : " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    // Requête pour les totaux
    $query = "SELECT SUM(prix) AS total_prix, SUM(prix_vendeur) AS total_prix_vendeur FROM pm_articles WHERE liste = :liste AND etat = 1";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
        <td colspan='7'>TOTAL</td>
        <td>{$ligne['total_prix']} €</td>
        <td>{$ligne['total_prix_vendeur']} €</td>
        </tr>";
    }
} catch (PDOException $e) {
    echo "<p>Erreur lors du calcul des totaux : " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</table>";

?>
<br>
<table class=sample width=1000>
  <tr><td colspan=2 >Je reconnais avoir pris connaissance du réglement de l'association Les p'tits marsiens.</td>
  </tr>
  <tr height=70>
  <td width=50% valign=top>Date</td><td width=50% valign=top>Signature</td>
  </tr>
</table>

	L'association Les P'tits Marsiens dispose de moyens informatiques destinés à gérer plus facilement les ventes et les dépôts d'articles.
	<br>
	Les informations enregistrées sont réservées à l'usage de l'association et ne peuvent être communiquées qu'aux adhérents assurant la gestion de l'assocation.
	<br>
	Conformément aux articles 39 et suivants de la loi n° 78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés, toute personne peut obtenir communication et, le cas échéant, rectification ou suppression des informations la concernant, en s'adressant par courrier ou par e-mail à l'association.
	<br>

</body>
</html>