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

try {
	$pdo=db_connect($host,$port,$db,$username,$password);

    // Récupération des paramètres avec une valeur par défaut
    $liste = htmlspecialchars($_GET['liste'] ?? '', ENT_QUOTES, 'UTF-8');
    $bourse_active = htmlspecialchars($_GET['bourse_active'] ?? '', ENT_QUOTES, 'UTF-8');

    // Logique principale
    if ($bourse_active && $liste) {
        $liste = code2liste($liste, $pdo);
    }

    if ($bourse_active && !$liste) {
        $stmt = $pdo->prepare("SELECT id as liste FROM pm_liste_articles WHERE bourse = ?");
        $stmt->execute([$bourse_active]);
        $globalresult = $stmt->fetchAll();
    } elseif ($liste) {
        $stmt = $pdo->prepare("SELECT id as liste FROM pm_liste_articles WHERE id = ?");
        $stmt->execute([$liste]);
        $globalresult = $stmt->fetchAll();
    } else {
        throw new Exception("Paramètres manquants");
    }

    // Génération des résultats
    foreach ($globalresult as $global) {
        $liste_id = $global['liste'];
        $bourse = liste2bourse($liste_id, $pdo);
        $code = liste2code($liste_id, $pdo);

        // Récupération vendeur
        $stmt = $pdo->prepare("SELECT p.prenom, p.nom, p.id 
                             FROM pm_liste_articles l
                             JOIN pm_personnes p ON l.vendeur = p.id
                             WHERE l.id = ?");
        $stmt->execute([$liste_id]);
        $vendeur = $stmt->fetch();
        
        // Affichage entête
        echo renderHeader($code, $bourse, $vendeur);

        // Génération des tables d'articles
        generateArticleTable($pdo, $liste_id, 1, "Articles laissés à l'association");
        generateArticleTable($pdo, $liste_id, 3, "Articles repris");
        generateRecentSalesTable($pdo, $liste_id);

        echo renderFooter();
    }

} catch (PDOException $e) {
    error_log("Erreur de base de données : " . $e->getMessage());
    die("Une erreur est survenue");
} catch (Exception $e) {
    error_log("Erreur générale : " . $e->getMessage());
    die($e->getMessage());
}

// Fonctions de rendu
function renderHeader($code, $bourse, $vendeur) {
    // Assurer que toutes les valeurs sont des chaînes
    $code = $code ?? '';
    $bourse = $bourse ?? '';
    $vendeur_id = $vendeur['id'] ?? '';
    $vendeur_nom = $vendeur['nom'] ?? '';
    $vendeur_prenom = $vendeur['prenom'] ?? '';

    $html = '<table class="sample">
            <tr><td>Liste</td><td>Vendeur</td><td>Nom</td></tr>
            <tr>
                <td><h2>N° '.htmlspecialchars($code.$bourse).'</h2></td>
                <td><h2>N° '.htmlspecialchars($vendeur_id).'</h2></td>
                <td><h3>'.htmlspecialchars($vendeur_prenom.' '.$vendeur_nom).'</h3></td>
            </tr>
        </table>';
    return $html;
}

function generateArticleTable(PDO $pdo, $liste_id, $etat, $title) {
    $query = "SELECT a.id, a.numero, a.designation, a.prix, a.prix_vendeur,
                ty.type, ty.image as type_image,
                ta.taille, d.libelle as designation_courte,
                c.libelle as couleur, m.libelle as marque
              FROM pm_articles a
              LEFT JOIN pm_types ty ON a.type = ty.id
              LEFT JOIN pm_tailles ta ON a.taille = ta.id
              LEFT JOIN pm_designations_courtes d ON a.designation_courte = d.id
              LEFT JOIN pm_couleurs c ON a.couleur = c.id
              LEFT JOIN pm_marques m ON a.marque = m.id
              WHERE a.liste = ? AND a.etat = ?
              ORDER BY a.numero";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$liste_id, $etat]);
    $articles = $stmt->fetchAll();

    echo "<h3>$title</h3>";
    echo '<table class="sample">
            <tr>
                <th>N°</th><th>Désignation</th><th>Désignation longue</th>
                <th>Couleur</th><th>Marque</th><th>Type</th>
                <th>Taille</th><th>Prix</th><th>Net vendeur</th>
            </tr>';

    foreach ($articles as $ligne) {
        echo '<tr>
                <td>'.htmlspecialchars($ligne['numero'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['designation_courte'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['designation'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['couleur'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['marque'] ?? '').'</td>
                <td><img src="'.htmlspecialchars($ligne['type_image'] ?? '').'"></td>
                <td>'.htmlspecialchars($ligne['taille'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['prix'] ?? '').' €</td>
                <td>'.htmlspecialchars($ligne['prix_vendeur'] ?? '').' €</td>
              </tr>';
    }

    // Total
    $stmt = $pdo->prepare("SELECT SUM(prix), SUM(prix_vendeur) 
                          FROM pm_articles 
                          WHERE liste = ? AND etat = ?");
    $stmt->execute([$liste_id, $etat]);
    $total = $stmt->fetch();

    echo '<tr>
            <td colspan="7">TOTAL</td>
            <td>'.htmlspecialchars($total[0] ?? '').' €</td>
            <td>'.htmlspecialchars($total[1] ?? '').' €</td>
          </tr></table>';
}

function generateRecentSalesTable(PDO $pdo, $liste_id) {
    $date = (new DateTime('-2 months'))->format('Y-m-d');
    
    $query = "SELECT a.*, ty.type, ty.image as type_image,
                ta.taille, d.libelle as designation_courte,
                c.libelle as couleur, m.libelle as marque
              FROM pm_articles a
              LEFT JOIN pm_types ty ON a.type = ty.id
              LEFT JOIN pm_tailles ta ON a.taille = ta.id
              LEFT JOIN pm_designations_courtes d ON a.designation_courte = d.id
              LEFT JOIN pm_couleurs c ON a.couleur = c.id
              LEFT JOIN pm_marques m ON a.marque = m.id
              LEFT JOIN pm_ventes v ON a.vente = v.id
              WHERE a.liste = ? AND a.etat = 2 AND v.date >= ?
              ORDER BY a.numero";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$liste_id, $date]);
    $articles = $stmt->fetchAll();

    $dateText = (new DateTime('-2 months'))->format('d/m/Y');
    echo "<h3>Articles vendus depuis le $dateText</h3>";
    echo '<table class="sample">
            <tr>
                <th>N°</th><th>Désignation</th><th>Désignation longue</th>
                <th>Couleur</th><th>Marque</th><th>Type</th>
                <th>Taille</th><th>Prix</th><th>Net vendeur</th>
            </tr>';

    foreach ($articles as $ligne) {
        echo '<tr>
                <td>'.htmlspecialchars($ligne['numero'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['designation_courte'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['designation'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['couleur'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['marque'] ?? '').'</td>
                <td><img src="'.htmlspecialchars($ligne['type_image'] ?? '').'"></td>
                <td>'.htmlspecialchars($ligne['taille'] ?? '').'</td>
                <td>'.htmlspecialchars($ligne['prix'] ?? '').' €</td>
                <td>'.htmlspecialchars($ligne['prix_vendeur'] ?? '').' €</td>
              </tr>';
    }

    echo '</table>';
}

function renderFooter() {
    return '<br><table class="sample" width="1000">
            <tr><td colspan="2">Lu et approuvé</td></tr>
            <tr height="70">
                <td width="50%" valign="top">Date</td>
                <td width="50%" valign="top">Signature</td>
            </tr>
            </table>
            <p>L\'association Les P\'tits Marsiens dispose de moyens informatiques...</p>';
}
		
?>

</body>
</html>