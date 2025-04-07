<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<SCRIPT>

function confirmation_post(liste,id,designation,formulaire) { 
//alert(' '+formulaire+' ');
var msg = ' Confirmer la suppression de : '+designation; 
if (confirm(msg)) {
    document.getElementById(formulaire).submit(); 
  }
  else
  {
    document.location.replace('nouveau_article.php?liste='+liste);   
  }
} 

function refresh_dropdown() 
{
var toto=document.getElementById("marque_saisie").value;
var trouve=0;
  for (var i=0;i<document.article.marque.options.length;i++) {
      if (document.article.marque.options[i].value == toto) {
          document.article.marque.options[i].selected = true;
          trouve=1;
          }
      if (trouve==0){
          document.article.marque.options[0].selected = true;
      }
  }
}

function refresh_input() 
{
var toto=document.getElementById("marque").value;
document.getElementById("marque_saisie").value=toto;

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

$id_article = filter_input(INPUT_GET, 'id_article', FILTER_VALIDATE_INT);

$connection = db_connect($host, $port, $db, $username, $password);

$posted = filter_input(INPUT_POST, 'posted', FILTER_VALIDATE_BOOLEAN);

if ($posted) {
    // Récupération et nettoyage des champs texte avec htmlspecialchars pour éviter le double encodage
    $designation_courte = htmlspecialchars(filter_input(INPUT_POST, 'designation_courte', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);
    $designation = htmlspecialchars(filter_input(INPUT_POST, 'designation', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);

    // Pour les autres chaînes, vous pouvez choisir d'utiliser FILTER_DEFAULT suivi de htmlspecialchars
    $couleur = htmlspecialchars(filter_input(INPUT_POST, 'couleur', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);
    $marque = htmlspecialchars(filter_input(INPUT_POST, 'marque', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);
    $type = htmlspecialchars(filter_input(INPUT_POST, 'type', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);
    $etat = htmlspecialchars(filter_input(INPUT_POST, 'etat', FILTER_DEFAULT) ?? '', ENT_QUOTES, 'UTF-8', false);

    // Champs numériques
    $taille = filter_input(INPUT_POST, 'taille', FILTER_VALIDATE_INT);
    $liste = filter_input(INPUT_POST, 'liste', FILTER_VALIDATE_INT);
    $pourcent = filter_input(INPUT_POST, 'pourcent', FILTER_VALIDATE_FLOAT);
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);
    $id_article = filter_input(INPUT_POST, 'id_article', FILTER_VALIDATE_INT);

    if ($id_article !== null && $id_article !== false) {
        if ($prix !== false && $prix !== null && $pourcent !== false && $pourcent !== null) {
            $prix_vendeur = $prix * (1 - ($pourcent / 100));
        } else {
            $prix_vendeur = 0;
        }

        $query = "
            UPDATE pm_articles 
            SET designation_courte = :designation_courte,
                couleur = :couleur,
                marque = :marque,
                designation = :designation,
                type = :type,
                taille = :taille,
                prix_vendeur = :prix_vendeur,
                prix = :prix,
                etat = :etat
            WHERE id = :id_article
        ";

        try {
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':designation_courte', $designation_courte, PDO::PARAM_STR);
            $stmt->bindParam(':couleur', $couleur, PDO::PARAM_STR);
            $stmt->bindParam(':marque', $marque, PDO::PARAM_STR);
            $stmt->bindParam(':designation', $designation, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':taille', $taille, PDO::PARAM_INT);
            $stmt->bindParam(':prix_vendeur', $prix_vendeur, PDO::PARAM_STR);
            $stmt->bindParam(':prix', $prix, PDO::PARAM_STR);
            $stmt->bindParam(':etat', $etat, PDO::PARAM_STR);
            $stmt->bindParam(':id_article', $id_article, PDO::PARAM_INT);

            $stmt->execute();
        } catch (PDOException $e) {
            die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
        }
    } else {
        echo "Erreur : Données invalides ou manquantes.";
    }
}

// ------------- Affichage Formulaire ---------
try {
    // Requête sécurisée avec des paramètres liés
    $query = "
        SELECT 
            a.id AS id, a.numero AS numero, a.designation AS designation, a.prix AS prix, a.prix_vendeur AS prix_vendeur, 
            a.marque AS marque, a.liste AS liste, a.etat AS etat,
            ty.id AS type, ty.image AS type_image,
            a.taille AS taille, 
            a.designation_courte AS designation_courte, 
            a.couleur AS couleur,
            m.libelle AS marque_libelle  
        FROM pm_articles AS a
        LEFT JOIN pm_types AS ty ON ty.id = a.type
        LEFT JOIN pm_marques AS m ON m.id = a.marque
        WHERE a.id = :id_article
    ";

    // Préparation de la requête avec PDO
    $stmt = $connection->prepare($query);
    // Liaison du paramètre :id_article
    $stmt->bindParam(':id_article', $id_article, PDO::PARAM_INT);
    $stmt->execute();

    // Récupération des données
    if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $numero = $ligne['numero'];
        $liste = $ligne['liste'];
        $designation = $ligne['designation'];
        $type = $ligne['type'];
        $type_image = $ligne['type_image'];
        $taille = $ligne['taille'];
        $prix = $ligne['prix'];
        $prix_vendeur = $ligne['prix_vendeur'];
        $designation_courte = $ligne['designation_courte'];
        $couleur = $ligne['couleur'];
        $marque = $ligne['marque'];
        $etat = $ligne['etat'];
        if ($prix !== null && $prix != 0) {
            $pourcent = 100 * (1 - ($prix_vendeur / $prix));
        } else {
            $pourcent = 0;
        }
    } else {
        $msg = "Article non trouvé.";
        ouvre_page("erreur.php?message=" . urlencode($msg));
        exit();
    }

    // Vérification de l'état de l'article
    if ($etat == 2) {
        $msg = "Cet article a déjà été vendu et ne peut être modifié.";
        ouvre_page("erreur.php?message=" . urlencode($msg));
        exit();
    }

    // Affichage du formulaire
    echo "<h1 class=\"title2\">Modifier un article</h1>";
    echo "<h3>Article référence $liste-$numero</h3>";
    echo "<form method=\"post\" name=\"article\" id=\"article\" action=\"modifier_article.php\">";

	// DESIGNATION COURTE
	echo "Designation courte : <select name=\"designation_courte\">";
	$query = "SELECT id, libelle FROM pm_designations_courtes ORDER BY ordre ASC"; // Utiliser 'libelle' ici
	$stmt = $connection->prepare($query);
	$stmt->execute();
	while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$id = $ligne['id'];
		$valeur = htmlspecialchars($ligne['libelle']); // Utiliser 'libelle' pour afficher la valeur
		if ($id == $designation_courte) {
			echo "<option selected value=\"$id\">$valeur</option>";
		} else {
			echo "<option value=\"$id\">$valeur</option>";
		}
	}
	echo "</select><br>";

	// COULEUR
	echo "Couleur : <select name=\"couleur\">";
	$query = "SELECT id, libelle FROM pm_couleurs ORDER BY ordre ASC"; // Utiliser 'libelle' ici
	$stmt = $connection->prepare($query);
	$stmt->execute();
	while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$id = $ligne['id'];
		$valeur = htmlspecialchars($ligne['libelle']); // Utiliser 'libelle' pour afficher la couleur
		if ($id == $couleur) {
			echo "<option selected value=\"$id\">$valeur</option>";
		} else {
			echo "<option value=\"$id\">$valeur</option>";
		}
	}
	echo "</select><br>";

    // MARQUE
    echo "Marque : <input type=\"text\" id=\"marque_saisie\" onchange=\"refresh_dropdown()\" size=\"1\" value=\"$marque\">";
    echo "<select id=\"marque\" name=\"marque\" onchange=\"refresh_input()\">";
    $query = "SELECT * FROM pm_marques ORDER BY ordre ASC";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $ligne['id'];
        // Vérification de la présence de la clé et de sa valeur
        $valeur = isset($ligne['libelle']) ? htmlspecialchars($ligne['libelle']) : '';
        if ($id == $marque) {
            echo "<option selected value=\"$id\">$valeur</option>";
        } else {
            echo "<option value=\"$id\">$valeur</option>";
        }
    }
    echo "</select><br>";

    // DESIGNATION
    echo "Designation : <input type=\"text\" size=\"110\" name=\"designation\" value=\"" . htmlspecialchars($designation) . "\"><br><hr>";

    // TYPE
    echo "Type : <select name=\"type\">";
    $query = "SELECT id, type FROM pm_types ORDER BY id ASC";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $type_id = $ligne['id'];
        $type_libelle = htmlspecialchars($ligne['type']);
        if ($type == $type_id) {
            echo "<option selected value=\"$type_id\">$type_libelle</option>";
        } else {
            echo "<option value=\"$type_id\">$type_libelle</option>";
        }
    }
    echo "</select><br>";

    // TAILLE
    echo "Taille : <select name=\"taille\">";
    $query = "SELECT id, taille FROM pm_tailles ORDER BY id ASC";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $taille_id = $ligne['id'];
        $taille_libelle = htmlspecialchars($ligne['taille']);
        if ($taille == $taille_id) {
            echo "<option selected value=\"$taille_id\">$taille_libelle</option>";
        } else {
            echo "<option value=\"$taille_id\">$taille_libelle</option>";
        }
    }
    echo "</select><br>";

    // ETAT
    echo "Etat : <select name=\"etat\">";
    $query = "SELECT id, etat FROM pm_etat_articles WHERE id <> 2 ORDER BY id ASC";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $etat_id = $ligne['id'];
        $etat_libelle = htmlspecialchars($ligne['etat']);
        if ($etat == $etat_id) {
            echo "<option selected value=\"$etat_id\">$etat_libelle</option>";
        } else {
            echo "<option value=\"$etat_id\">$etat_libelle</option>";
        }
    }
    echo "</select><br><hr>";

    // PRIX ET POURCENTAGE
    echo "Prix : <input type=\"text\" size=\"10\" name=\"prix\" value=\"$prix\"><br>";
    echo "Pourcentage : <input type=\"text\" size=\"5\" name=\"pourcent\" value=\"$pourcent\"><br>";
    echo "Prix vendeur: <b>$prix_vendeur €</b><br>";

    // Champs cachés
    echo "<input type=\"hidden\" name=\"liste\" value=\"$liste\">";
    echo "<br><input type=\"submit\" name=\"envoyer\" value=\"ENREGISTRER\">";
    echo "<input type=\"hidden\" name=\"posted\" value=\"1\">";
    echo "<input type=\"hidden\" name=\"id_article\" value=\"$id_article\">";
    echo "</form>";

    // Formulaires supplémentaires
    echo "<form method=\"get\" action=\"modifier_article.php\">";
    echo "<input type=\"hidden\" name=\"id_article\" value=\"$id_article\">";
    echo "<input type=\"submit\" name=\"envoyer\" value=\"ANNULER\">";
    echo "</form>";

    echo "<form method=\"get\" action=\"depot_articles.php\">";
    echo "<input type=\"hidden\" name=\"liste\" value=\"$liste\">";
    echo "<input type=\"submit\" name=\"envoyer\" value=\"RETOUR A LA LISTE N°$liste\">";
    echo "</form>";

} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
}

?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>