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

$connection=db_connect($host,$port,$db,$username,$password);

$liste = $_GET['liste'] ?? null;
$bourse_active = $_GET['bourse_active'] ?? null;

if (!empty($liste)) {
    $liste = code2liste($liste, $connection);
}

if (!empty($bourse_active) && empty($liste)) {
    $globalquery = "SELECT l.id AS liste FROM pm_liste_articles AS l WHERE l.bourse = :bourse_active";
} else {
    $globalquery = "SELECT l.id AS liste FROM pm_liste_articles AS l WHERE l.id = :liste";
}

try {
    $stmt = $connection->prepare($globalquery);
    if (!empty($bourse_active) && empty($liste)) {
        $stmt->bindParam(':bourse_active', $bourse_active, PDO::PARAM_STR);
    } else {
        $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    }
    $stmt->execute();

    while ($global = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $listeId = $global['liste'];
        $bourse = liste2bourse($listeId, $connection);
        $code = liste2code($listeId, $connection);

        $query = "SELECT p.prenom, p.nom, p.id 
                  FROM pm_liste_articles AS l 
                  JOIN pm_personnes AS p ON l.vendeur = p.id 
                  WHERE l.id = :liste";
        $stmt_vendeur = $connection->prepare($query);
        $stmt_vendeur->bindParam(':liste', $listeId, PDO::PARAM_INT);
        $stmt_vendeur->execute();
        $vendeur = "Inconnu";
        $vendeurId = null;
        if ($ligne = $stmt_vendeur->fetch(PDO::FETCH_ASSOC)) {
            $vendeur = htmlspecialchars($ligne['prenom'] . " " . $ligne['nom']);
            $vendeurId = htmlspecialchars($ligne['id']);
        }

        echo "<table class='sample'>
                <tr><td>Liste</td><td>Vendeur</td><td>Nom</td></tr>
                <tr>
                    <td><h2>N° {$code}{$bourse}</h2></td>
                    <td><h2>N° {$vendeurId}</h2></td>
                    <td><h3>{$vendeur}</h3></td>
                </tr>
              </table>";

        echo "<h3>Articles déposés</h3>
              <table class='sample'>
                <tr bgcolor='#FFFFFF'>
                    <td>N°</td>
                    <td>Désignation</td>
                    <td>Designation longue</td>
                    <td>Couleur</td>
                    <td>Marque</td>
                    <td>Type</td>
                    <td>Taille</td>
                    <td>Prix</td>
                    <td>Net vendeur</td>
                </tr>";

        $query = "SELECT 
                    a.id, a.numero, a.designation, a.prix, a.prix_vendeur, 
                    ty.type, ty.image AS type_image, ta.taille, 
                    d.libelle AS designation_courte, c.libelle AS couleur, 
                    m.libelle AS marque, e.etat AS etat_libelle
                  FROM pm_articles AS a
                  LEFT JOIN pm_types AS ty ON a.type = ty.id
                  LEFT JOIN pm_tailles AS ta ON a.taille = ta.id
                  LEFT JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
                  LEFT JOIN pm_couleurs AS c ON a.couleur = c.id
                  LEFT JOIN pm_marques AS m ON a.marque = m.id
                  LEFT JOIN pm_etat_articles AS e ON e.id = a.etat
                  WHERE a.liste = :liste
                  ORDER BY a.numero";
        $stmt_articles = $connection->prepare($query);
        $stmt_articles->bindParam(':liste', $listeId, PDO::PARAM_INT);
        $stmt_articles->execute();

        while ($ligne = $stmt_articles->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr bgcolor='#FFFFFF'>
                    <td>{$ligne['numero']}</td>
                    <td>{$ligne['designation_courte']}</td>
                    <td>{$ligne['designation']}</td>
                    <td>{$ligne['couleur']}</td>
                    <td>{$ligne['marque']}</td>
                    <td><img src=\"" . htmlspecialchars($ligne['type_image']) . "\"></td>
                    <td>{$ligne['taille']}</td>
                    <td>{$ligne['prix']} €</td>
                    <td>{$ligne['prix_vendeur']} €</td>
                  </tr>";
        }

        $query = "SELECT SUM(prix) AS total_prix, SUM(prix_vendeur) AS total_vendeur 
                  FROM pm_articles  
                  WHERE liste = :liste AND etat = 1";
        $stmt_total = $connection->prepare($query);
        $stmt_total->bindParam(':liste', $listeId, PDO::PARAM_INT);
        $stmt_total->execute();
        if ($ligne = $stmt_total->fetch(PDO::FETCH_ASSOC)) {
            $totalPrix = $ligne['total_prix'] ?? 0;
            $totalVendeur = $ligne['total_vendeur'] ?? 0;
            echo "<tr>
                    <td colspan='7'>TOTAL (Articles non vendus)</td> 
                    <td>{$totalPrix} €</td>
                    <td>{$totalVendeur} €</td>
                  </tr>";
        }

        $mois = date('Y-m-01');
        $query = "SELECT SUM(a.prix) AS total_prix, SUM(a.prix_vendeur) AS total_vendeur 
                  FROM pm_articles AS a 
                  JOIN pm_ventes AS v ON v.id = a.vente
                  WHERE a.liste = :liste AND a.etat = 2 
                  AND v.date > :mois";
        $stmt_ventes = $connection->prepare($query);
        $stmt_ventes->bindParam(':liste', $listeId, PDO::PARAM_INT);
        $stmt_ventes->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt_ventes->execute();
        if ($ligne = $stmt_ventes->fetch(PDO::FETCH_ASSOC)) {
            $totalPrixVentes = $ligne['total_prix'] ?? 0;
            $totalVendeurVentes = $ligne['total_vendeur'] ?? 0;
            echo "<tr>
                    <td colspan='7'>TOTAL (Articles vendus)</td> 
                    <td>{$totalPrixVentes} €</td>
                    <td>{$totalVendeurVentes} €</td>
                  </tr>";
        }

        echo "</table>";

        echo "<br>
              <table class='sample' width='1000'>
                  <tr><td colspan='2'>Lu et approuvé</td></tr>
                  <tr height='70'>
                      <td width='50%' valign='top'>Date</td>
                      <td width='50%' valign='top'>Signature</td>
                  </tr>
              </table>
              <p>L'association Les P'tits Marsiens dispose de moyens informatiques destinés à gérer plus facilement les ventes et les dépôts d'articles.</p>
              <p>Les informations enregistrées sont réservées à l'usage de l'association et ne peuvent être communiquées qu'aux adhérents assurant la gestion de l'association.</p>
              <p>Conformément aux articles 39 et suivants de la loi n° 78-17 du 6 janvier 1978 relative à l'informatique, aux fichiers et aux libertés, toute personne peut obtenir communication et, le cas échéant, rectification ou suppression des informations la concernant, en s'adressant par courrier ou par e-mail à l'association.</p>
              <p style=\"page-break-before: always\"></p>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

		
?>

</body>
</html>