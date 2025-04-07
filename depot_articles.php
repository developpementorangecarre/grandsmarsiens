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

// Récupération des paramètres et connexion
$liste = $_GET['liste'] ?? null;
$etat_liste = $_POST['etat_liste'] ?? null;
$article_a_supprimer = $_POST['id_article'] ?? null;
$connection = db_connect($host, $port, $db, $username, $password);

$bourse_active = $_GET['bourse_active'] ?? null;
if (isset($bourse_active)):
    $reference_article = $_GET['reference_article'];
    $matches = array();
    preg_match('/([0-9]+)([A-Za-z]+)/', $reference_article, $matches);
    $code = $matches[1];
    $bourse = $matches[2];
    $liste = codeetbourse2liste($code, $bourse, $connection);
endif;

$bourse = liste2bourse($liste, $connection);
$code = liste2code($liste, $connection);

// Suppression d'un article si demandé
if (isset($article_a_supprimer)) {
    // On récupère la valeur de $liste depuis POST
    $liste = $_POST['liste'];
    $msg = "<h3>L'article a été retiré</h3>";
    
    $query = "SELECT * FROM pm_articles WHERE id = $article_a_supprimer AND etat <> 1";
    $result = exec_sql($query, $connection);
    while ($ligne = fetch_row($result)) {
        $msg = "<h3>Cet article a déjà été vendu ou repris et ne peut être supprimé</h3>";
    }
    
    $query = "DELETE FROM pm_articles WHERE id = $article_a_supprimer AND etat = 1";
    $result = exec_sql($query, $connection);
    $etat = 0;
}

// Activation/inactivation d'une liste
if (isset($_POST['etat_liste'], $_POST['liste'])) {
    $etat_liste = (int) $_POST['etat_liste'];
    $liste = (int) $_POST['liste'];
    
    $query = "UPDATE pm_liste_articles SET etat = :etat WHERE id = :id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':etat', $etat_liste, PDO::PARAM_INT);
    $stmt->bindParam(':id', $liste, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        die("Erreur lors de la mise à jour de la liste.");
    }
}

// Récupération des informations de la liste (vendeur, catégorie, état)
try {
    if (!isset($liste) || $liste <= 0) {
        throw new Exception("ID de liste invalide.");
    }
    
    // On utilise LEFT JOIN pour récupérer éventuellement les infos du vendeur et de l'état.
    // Remplacement de e.libelle par e.etat pour récupérer la valeur depuis pm_etat_liste.
    $query = "
        SELECT l.vendeur, p.prenom, p.nom, p.categorie, 
               COALESCE(e.etat, (CASE WHEN l.etat = 0 THEN 'Actif' ELSE 'Inactif' END)) AS etat_libelle, 
               l.etat AS etat_id
        FROM pm_liste_articles AS l 
        LEFT JOIN pm_personnes AS p ON l.vendeur = p.id 
        LEFT JOIN pm_etat_liste AS e ON e.id = l.etat
        WHERE l.id = :liste
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si les infos du vendeur ne sont pas présentes, on tente une récupération par son ID
    if ($ligne && (empty($ligne['prenom']) || empty($ligne['nom']))) {
        $vendor_id = $ligne['vendeur'] ?? null;
        if ($vendor_id) {
            $query_vendor = "SELECT prenom, nom, categorie FROM pm_personnes WHERE id = :vendeur";
            $stmt_vendor = $connection->prepare($query_vendor);
            $stmt_vendor->bindParam(':vendeur', $vendor_id, PDO::PARAM_INT);
            $stmt_vendor->execute();
            $ligne_vendor = $stmt_vendor->fetch(PDO::FETCH_ASSOC);
            if ($ligne_vendor) {
                $ligne['prenom'] = $ligne_vendor['prenom'];
                $ligne['nom'] = $ligne_vendor['nom'];
                $ligne['categorie'] = $ligne_vendor['categorie'];
            }
        }
    }
    
    $vendeur = (isset($ligne['prenom'], $ligne['nom']) && $ligne['prenom'] !== null && $ligne['nom'] !== null)
               ? htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom'], ENT_QUOTES, 'UTF-8')
               : 'Inconnu';
    $categorie = isset($ligne['categorie']) ? htmlspecialchars($ligne['categorie'], ENT_QUOTES, 'UTF-8') : 'Inconnue';
    $etat_liste_text = isset($ligne['etat_libelle']) ? htmlspecialchars($ligne['etat_libelle'], ENT_QUOTES, 'UTF-8') : 'Inconnu';
    $etat_liste_id = isset($ligne['etat_id']) ? (int)$ligne['etat_id'] : 0;
    
    $nouvel_etat = ($etat_liste_id == 0) ? "Inactiver la liste" : "Réactiver la liste";
    
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
} catch (Exception $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Récupération du pourcentage de la catégorie
try {
    $query = "SELECT pourcent FROM pm_categories WHERE id = :categorie";
    $stmt = $connection->prepare($query);
    // Conversion de la catégorie en entier si nécessaire
    $categorie_int = (int) $categorie;
    $stmt->bindParam(':categorie', $categorie_int, PDO::PARAM_INT);
    $stmt->execute();
    
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    $pourcent = $ligne ? (float) $ligne['pourcent'] : 0;
    
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
} catch (Exception $e) {
    die("Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$msg = $msg ?? "";
$code = $code ?? "";
$bourse = $bourse ?? "";
$etat_liste_text = $etat_liste_text ?? "";
$vendeur = $vendeur ?? "";
$liste = $liste ?? 0;
$etat_liste_id = $etat_liste_id ?? 0;
$nouvel_etat = $nouvel_etat ?? "";
$counter = 0;
$bgcolor = "#FFFFFF";

// Affichage de l'en-tête et du formulaire d'ajout d'articles
echo "
<h1 class=\"title2\">Liste d'article N°" . htmlspecialchars($code . $bourse) . " - " . htmlspecialchars($etat_liste_text) . "</h1>
<form method=\"post\" action=\"nouveau_article.php\">
    <input type=\"submit\" name=\"envoyer\" value=\"Ajouter des articles\">
    <input type=\"hidden\" name=\"liste\" value=\"" . htmlspecialchars($liste) . "\">
    <input type=\"hidden\" name=\"posted\" value=\"1\">
</form>
<h3>Liste de " . htmlspecialchars($vendeur) . "</h3>
$msg
";

// Affichage de la liste des articles
echo "<table class=\"sample\">
    <tr bgcolor=\"#FFFFBB\">
        <td>N°</td>
        <td>Désignation</td>
        <td>Désignation longue</td>
        <td>Couleur</td>
        <td>Marque</td>
        <td>Type</td>
        <td>Taille</td>
        <td>Prix</td>
        <td>Net vendeur</td>
        <td>État</td>
        <td>Actions</td>
    </tr>";

try {
    $query = "SELECT 
        a.id, a.numero, a.designation, a.prix, a.prix_vendeur, 
        e.etat AS etat_libelle, e.id AS etat, 
        ty.type, ty.image AS type_image, 
        ta.taille, d.libelle AS designation_courte, 
        c.libelle AS couleur, 
        m.libelle AS marque, 
        a.vente 
        FROM pm_articles AS a
        LEFT JOIN pm_types AS ty ON a.type = ty.id
        LEFT JOIN pm_tailles AS ta ON a.taille = ta.id
        LEFT JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
        LEFT JOIN pm_couleurs AS c ON a.couleur = c.id
        LEFT JOIN pm_marques AS m ON a.marque = m.id
        LEFT JOIN pm_etat_articles AS e ON a.etat = e.id
        WHERE a.liste = :liste
        ORDER BY numero";
    
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $ligne['id'];
        $numero = htmlspecialchars($ligne['numero'] ?? '', ENT_QUOTES, 'UTF-8');
        $designation = htmlspecialchars($ligne['designation'] ?? '', ENT_QUOTES, 'UTF-8');
        $designation_text = addslashes($designation);
        $type = htmlspecialchars($ligne['type'] ?? '', ENT_QUOTES, 'UTF-8');
        $type_image = htmlspecialchars($ligne['type_image'] ?? '', ENT_QUOTES, 'UTF-8');
        $taille = htmlspecialchars($ligne['taille'] ?? '', ENT_QUOTES, 'UTF-8');
        $prix = htmlspecialchars($ligne['prix'] ?? '', ENT_QUOTES, 'UTF-8');
        $prix_vendeur = htmlspecialchars($ligne['prix_vendeur'] ?? '', ENT_QUOTES, 'UTF-8');
        $etat_libelle = htmlspecialchars($ligne['etat_libelle'] ?? '', ENT_QUOTES, 'UTF-8');
        $etat = $ligne['etat'];
        $designation_courte = htmlspecialchars($ligne['designation_courte'] ?? '', ENT_QUOTES, 'UTF-8');
        $couleur = htmlspecialchars($ligne['couleur'] ?? '', ENT_QUOTES, 'UTF-8');
        $marque = htmlspecialchars($ligne['marque'] ?? '', ENT_QUOTES, 'UTF-8');
        $vente = htmlspecialchars($ligne['vente'] ?? '', ENT_QUOTES, 'UTF-8');
        
        // Définition de la couleur d'état en fonction de l'ID d'état
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
            <td><img src=\"$type_image\" alt=\"$type\"></td>
            <td>$taille</td>
            <td>$prix €</td>
            <td>$prix_vendeur €</td>
            <td bgcolor=\"$color_etat\">$etat_libelle <b>$vente</b></td>
            <td>
                <a href=\"modifier_article.php?id_article=$id\"><img src=\"images/edit.png\" alt=\"Modifier\"></a>
                <form name=\"supprimer_$id\" id=\"supprimer_$id\" method=\"post\" action=\"depot_articles.php\">
                    <input type=\"hidden\" name=\"id_article\" value=\"$id\">
                    <input type=\"hidden\" name=\"liste\" value=\"$liste\">
                </form>
                <a href=\"#\" onclick='confirmation_post($liste, $id, \"$designation_text\", \"supprimer_$id\")'>
                    <img src=\"images/delete.png\" alt=\"Supprimer\">
                </a>
            </td>
        </tr>";
        
        $counter++;
        $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan=\"10\">Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

try {
    $query = "SELECT SUM(prix) AS total_prix, SUM(prix_vendeur) AS total_prix_vendeur FROM pm_articles WHERE liste = :liste";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_prix = htmlspecialchars($ligne['total_prix'] ?? "0");
    $total_prix_vendeur = htmlspecialchars($ligne['total_prix_vendeur'] ?? "0");
    
    echo "<tr>
        <td colspan=\"7\">TOTAL</td>
        <td>$total_prix €</td>
        <td>$total_prix_vendeur €</td>
    </tr>";
} catch (PDOException $e) {
    echo "<tr><td colspan=\"10\">Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

echo "</table>";

// Lien vers le reçu imprimable et formulaire d'activation/inactivation de la liste
echo "
<h3><a href=\"liste_articles.php?liste=$liste\" target=\"_blank\">
    <img src=\"images/presse78.gif\" align=\"center\" border=\"0\"> Voir le reçu imprimable
</a></h3>
<form name=\"activation\" id=\"activation\" method=\"post\" action=\"depot_articles.php\">
    <input type=\"hidden\" name=\"etat_liste\" value=\"$etat_liste_id\">
    <input type=\"hidden\" name=\"liste\" value=\"$liste\">
    <input type=\"submit\" name=\"envoyer\" value=\"$nouvel_etat\">
</form>";

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php");  ?>


</body>
</html>