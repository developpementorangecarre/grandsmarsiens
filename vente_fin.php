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

$msg = "";
$vente = filter_input(INPUT_GET, 'vente', FILTER_SANITIZE_NUMBER_INT);

try {
    $stmt = $connection->prepare("SELECT p.prenom, p.nom, p.categorie 
                                  FROM pm_ventes AS v 
                                  JOIN pm_personnes AS p ON v.client = p.id 
                                  WHERE v.id = :vente");
    $stmt->execute(['vente' => $vente]);
    
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($client) {
        $nom_client = "{$client['prenom']} {$client['nom']}";
        $categorie = $client['categorie'];
    }

    $stmt = $connection->prepare("SELECT v.etat FROM pm_ventes AS v WHERE v.id = :vente");
    $stmt->execute(['vente' => $vente]);
    
    $etat_vente = $stmt->fetchColumn();
    if ($etat_vente == 2) {
        $msg = "<h3>Cette vente est terminée</h3>
                <h3><a href=\"voir_vente.php?vente=$vente\" target=\"_blank\"><img src=\"images/presse78.gif\" align=\"center\" border=\"0\"> Voir le reçu imprimable</a></h3>";
    }
    
    echo "<h1 class=\"title2\">Vente n°$vente : $nom_client</h1>$msg";

    echo "<table class=\"sample\">
            <tr bgcolor=\"#FFFFBB\">
                <td>référence</td>
                <td>designation courte</td>
                <td>designation</td>
                <td>couleur</td>
                <td>marque</td>
                <td>type</td>
                <td>taille</td>
                <td>prix</td>
            </tr>";
    
    $stmt = $connection->prepare("SELECT a.id AS id, a.numero AS numero, a.designation AS designation, a.prix AS prix, a.prix_vendeur AS prix_vendeur, a.liste AS liste,
                                  ty.type AS type, ty.image AS type_image, ta.taille AS taille, 
                                  d.libelle AS designation_courte, c.libelle AS couleur, m.libelle AS marque
                                  FROM pm_articles AS a
                                  LEFT JOIN pm_types AS ty ON a.type = ty.id
                                  LEFT JOIN pm_tailles AS ta ON a.taille = ta.id
                                  LEFT JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
                                  LEFT JOIN pm_couleurs AS c ON a.couleur = c.id
                                  LEFT JOIN pm_marques AS m ON a.marque = m.id
                                  WHERE a.vente = :vente
                                  ORDER BY a.liste, a.numero");
    $stmt->execute(['vente' => $vente]);

    $counter = 0;
    $bgcolor = "#FFFFFF";
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $numero = $ligne['numero'];
        $liste = $ligne['liste'];
        $designation_text = text2js($ligne['designation']);
        $designation = $ligne['designation'];
        $type = $ligne['type'];
        $type_image = $ligne['type_image'];
        $taille = $ligne['taille'];
        $prix = $ligne['prix'];
        $id = $ligne['id'];
        $designation_courte = $ligne['designation_courte'];
        $couleur = $ligne['couleur'];
        $marque = $ligne['marque'];
        $message = "Voulez-vous supprimer $liste-$numero $designation ?";

        echo "<tr bgcolor=\"$bgcolor\">
                <td>$liste-$numero</td>
                <td>$designation_courte</td>
                <td>$designation</td>
                <td>$couleur</td>
                <td>$marque</td>
                <td><img src=\"$type_image\"></td>
                <td>$taille</td>
                <td>$prix €</td>
              </tr>";
        
        $counter++;
        $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
    }

    // Requête pour obtenir les totaux de la vente
    $stmt = $connection->prepare("SELECT SUM(prix), COUNT(*) FROM pm_articles WHERE vente = :vente");
    $stmt->execute(['vente' => $vente]);

    // Affichage des totaux
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<tr>
            <td colspan=\"4\">TOTAL</td>
            <td>{$totals['SUM(prix)']} €</td>
          </tr>";
    echo "<tr>
            <td colspan=\"3\"></td>
            <td colspan=\"2\">{$totals['COUNT(*)']} articles</td>
          </tr>";
    echo "</table>";
    
} catch (PDOException $e) {
    // Gestion des erreurs avec try-catch
    echo "Erreur de base de données : " . $e->getMessage();
} catch (Exception $e) {
    // Gestion des autres erreurs
    echo "Erreur : " . $e->getMessage();
}

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>