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
  <script>
  function focus_designation()
  {
    document.article.designation_courte.focus();
  }
  </script>
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

// Constantes

$liste = $_POST['liste'] ?? $_GET['liste'] ?? null;
$article_a_supprimer = isset($_POST['id_article']) ? $_POST['id_article'] : null;
$posted = isset($_POST['posted']) ? $_POST['posted'] : null;
$msg="";
$connection=db_connect($host,$port,$db,$username,$password);

$vendeur = '';  
$vendeur_id = '';  
$categorie = '';  
$pourcent = '';

// Suppression d'un article
if (isset($article_a_supprimer)) {
    $article_a_supprimer = (int) $article_a_supprimer; 
    $liste = isset($_POST['liste']) ? (int) $_POST['liste'] : null;
    $msg = "<h3>L'article a été retiré</h3>";

    try {
        // Vérifier si l'article peut être supprimé (non vendu ou repris)
        $query = "SELECT id FROM pm_articles WHERE id = :article_id AND etat <> 1";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':article_id', $article_a_supprimer, PDO::PARAM_INT);
        $stmt->execute();
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            $msg = "<h3>Cet article a déjà été vendu ou repris et ne peut être supprimé</h3>";
        } else {
            // Supprimer l'article si son état est 1 (encore disponible)
            $query = "DELETE FROM pm_articles WHERE id = :article_id AND etat = 1";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':article_id', $article_a_supprimer, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $msg = "<h3>L'article a été supprimé avec succès</h3>";
            } else {
                $msg = "<h3>Aucune suppression effectuée : l'article n'existe pas ou n'est pas supprimable</h3>";
            }
        }
    } catch (PDOException $e) {
        die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    echo $msg;
}


// Création d'un nouvel article
if (isset($_POST['posted'])) {
    try {
        // Vérification et sécurisation des entrées
        $designation = isset($_POST['designation']) ? htmlspecialchars($_POST['designation'], ENT_QUOTES, 'UTF-8') : '';
        $type = isset($_POST['type']) ? (int) $_POST['type'] : 0;
        $taille = isset($_POST['taille']) ? (int) $_POST['taille'] : 0;
        $liste = isset($_POST['liste']) ? (int) $_POST['liste'] : 0;
        $pourcent = isset($_POST['pourcent']) ? (float) $_POST['pourcent'] : 0.0;
        $prix = isset($_POST['prix']) ? (float) $_POST['prix'] : 0.0;
        $designation_courte = isset($_POST['designation_courte']) ? (int) $_POST['designation_courte'] : 0;
        $couleur = isset($_POST['couleur']) ? (int) $_POST['couleur'] : 0;
        $marque = isset($_POST['marque']) ? (int) $_POST['marque'] : 0;
        $etat = 1;

        // Calcul du prix vendeur
        $prix_vendeur = $prix * (1 - ($pourcent / 100));

        // Vérification de la connexion à la base de données
        if (!isset($connection)) {
            die("Erreur : connexion à la base de données non définie.");
        }

        // Requête pour récupérer le nombre d'articles dans la liste
        $query = "SELECT COUNT(*) as total FROM pm_articles WHERE liste = :liste";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
        $stmt->execute();

        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($ligne && isset($ligne['total'])) ? (int) $ligne['total'] : 0;

        // Si la liste est vide, on commence à 1, sinon, on récupère le dernier numéro et on incrémente
        if ($numero == 0) {
            $numero = 1;
        } else {
            $query_compte = "SELECT numero FROM pm_articles WHERE liste = :liste ORDER BY numero DESC LIMIT 1";
            $stmt_compte = $connection->prepare($query_compte);
            $stmt_compte->bindParam(':liste', $liste, PDO::PARAM_INT);
            $stmt_compte->execute();

            $ligne_compte = $stmt_compte->fetch(PDO::FETCH_ASSOC);
            $numero = ($ligne_compte && isset($ligne_compte['numero'])) ? (int) $ligne_compte['numero'] + 1 : 1;
        }

        // Insertion des données dans la base
        $query_insert = "
            INSERT INTO pm_articles
            (designation, liste, numero, type, taille, prix_vendeur, prix, etat, designation_courte, couleur, marque)
            VALUES
            (:designation, :liste, :numero, :type, :taille, :prix_vendeur, :prix, :etat, :designation_courte, :couleur, :marque)
        ";
        $stmt_insert = $connection->prepare($query_insert);

        // Bind des paramètres
        $stmt_insert->bindParam(':designation', $designation, PDO::PARAM_STR);
        $stmt_insert->bindParam(':liste', $liste, PDO::PARAM_INT);
        $stmt_insert->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt_insert->bindParam(':type', $type, PDO::PARAM_INT);
        $stmt_insert->bindParam(':taille', $taille, PDO::PARAM_INT);
        $stmt_insert->bindParam(':prix_vendeur', $prix_vendeur, PDO::PARAM_STR);
        $stmt_insert->bindParam(':prix', $prix, PDO::PARAM_STR);
        $stmt_insert->bindParam(':etat', $etat, PDO::PARAM_INT);
        $stmt_insert->bindParam(':designation_courte', $designation_courte, PDO::PARAM_INT);
        $stmt_insert->bindParam(':couleur', $couleur, PDO::PARAM_INT);
        $stmt_insert->bindParam(':marque', $marque, PDO::PARAM_INT);

        // Exécution de la requête d'insertion
        $stmt_insert->execute();

    } catch (PDOException $e) {
        // Gestion des erreurs
        die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}



try {
    $query = "SELECT p.prenom, p.nom, p.categorie, p.id 
              FROM pm_liste_articles AS l 
              JOIN pm_personnes AS p ON l.vendeur = p.id
              WHERE l.id = :liste"; 

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $vendeur = $ligne['prenom'] . " " . $ligne['nom']; 
        $categorie = $ligne['categorie'];
        $vendeur_id = $ligne['id'];
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}


try {
    $query = "SELECT c.pourcent 
              FROM pm_categories AS c 
              WHERE c.id = :categorie";

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':categorie', $categorie, PDO::PARAM_INT);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pourcent = $ligne['pourcent']; 
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}

/////// DEBUT FORMULAIRE ///////

$bourse=liste2bourse($liste,$connection);
$code=liste2code($liste,$connection);

echo "<h1 class=\"title2\">Créer un nouvel article</h1>
<h3>Liste de $vendeur (n° $code$bourse, vendeur n° $vendeur_id)</h3>
$msg
<form method=post action=nouveau_article.php name=article id=article>";

// DESIGNATION COURTE

echo "
Designation courte : <select name='designation_courte'>";

$query = "SELECT id, libelle FROM pm_designations_courtes ORDER BY ordre ASC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = htmlspecialchars($ligne['id']);
        $valeur = htmlspecialchars($ligne['libelle']);
        echo "<option value='$id'>$valeur</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

echo "</select>";


// COULEUR

echo "
Couleur : <select name='couleur'>";

$query = "SELECT id, libelle FROM pm_couleurs ORDER BY ordre ASC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = htmlspecialchars($ligne['id']); 
        $valeur = htmlspecialchars($ligne['libelle']);
        echo "<option value='$id'>$valeur</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

echo "</select>";


// MARQUE

echo "Marque : ";
echo "<input type='text' id='marque_saisie' onchange='refresh_dropdown()' size='1'>";
echo "<select id='marque' name='marque' onchange='refresh_input()'>";

// Requête SQL sécurisée avec PDO
$query = "SELECT id, libelle FROM pm_marques ORDER BY ordre ASC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = htmlspecialchars($ligne['id']); // Protection XSS
        $valeur = htmlspecialchars($ligne['libelle']); // Protection XSS
        echo "<option value='$id'>$valeur</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
echo "</select>";


// DESIGNATION 

echo "
Designation : <input type=texte size=110 name=designation>
";

echo "<hr>";


//// TYPE
echo "Type : <select id='type' name='type'>";

$query = "SELECT id, type FROM pm_types ORDER BY id ASC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = htmlspecialchars($ligne['id']); 
        $valeur = htmlspecialchars($ligne['type']); 
        echo "<option value='$id'>$valeur</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
echo "</select>";


//////// TAILLE
echo "Taille : <select id='taille' name='taille'>";

$query = "SELECT id, taille FROM pm_tailles ORDER BY id ASC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Assurer que les valeurs ne sont pas nulles avant de les passer à htmlspecialchars()
        $id = htmlspecialchars($ligne['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $valeur = htmlspecialchars($ligne['taille'] ?? '', ENT_QUOTES, 'UTF-8');
        echo "<option value='$id'>$valeur</option>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
echo "</select>";

echo "<hr>";

echo "Prix : <input type='text' size='30' name='prix'>";
echo "<input type='hidden' name='pourcent' value='" . htmlspecialchars($pourcent ?? '', ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' name='liste' value='" . htmlspecialchars($liste ?? '', ENT_QUOTES, 'UTF-8') . "'>";

echo "
<center>
    <input type='submit' name='envoyer' value='ENVOYER'>
</center>
<br>
<input type='hidden' name='posted' value='1'>
<br>
</form>";



// Affiche la liste
$counter = 0;
$bgcolor = "#FFFFFF";

echo "<table class='sample'>
<tr bgcolor='#FFFFBB'>
    <td>N°</td>
    <td>Désignation</td>
    <td>Désignation longue</td>
    <td>Couleur</td>
    <td>Marque</td>
    <td>Type</td>
    <td>Taille</td>
    <td>Prix</td>
    <td>Net vendeur</td>
</tr>";

// Requête SQL pour récupérer les articles
$query = "
  SELECT 
    a.id AS id,
    a.numero AS numero,
    a.designation AS designation,
    a.prix AS prix,
    a.prix_vendeur AS prix_vendeur, 
    ty.type AS type,
    ty.image AS type_image,
    ta.taille AS taille, 
    d.libelle AS designation_courte, 
    c.libelle AS couleur,
    m.libelle AS marque  
  FROM pm_articles AS a
  JOIN pm_types AS ty ON a.type = ty.id
  JOIN pm_tailles AS ta ON a.taille = ta.id
  JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
  JOIN pm_couleurs AS c ON a.couleur = c.id
  JOIN pm_marques AS m ON a.marque = m.id
  WHERE a.liste = :liste
  ORDER BY numero
";

try {
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $ligne['id'];
        $numero = htmlspecialchars($ligne['numero']);  
        $designation = htmlspecialchars($ligne['designation']);  
        $designation_text = text2js(htmlspecialchars($ligne['designation']));  
        $type = htmlspecialchars($ligne['type']);  
        $type_image = htmlspecialchars($ligne['type_image']);  
        $taille = htmlspecialchars($ligne['taille']);  
        $prix = htmlspecialchars($ligne['prix']);  
        $prix_vendeur = htmlspecialchars($ligne['prix_vendeur']);  
        $designation_courte = htmlspecialchars($ligne['designation_courte']);  
        $couleur = htmlspecialchars($ligne['couleur']);  
        $marque = htmlspecialchars($ligne['marque']);  

        // Affichage de la ligne dans le tableau
        echo "<tr bgcolor='$bgcolor'>
            <td>$numero</td>
            <td>$designation_courte</td>
            <td>$designation</td>
            <td>$couleur</td>
            <td>$marque</td>
            <td><img src='$type_image'></td>
            <td>$taille</td>
            <td>$prix €</td>
            <td>$prix_vendeur €</td>
            <td>
                <form name='supprimer_$id' id='supprimer_$id' method='post' action='nouveau_article.php'>
                    <input type='hidden' name='id_article' value='$id'>
                    <input type='hidden' name='liste' value='$liste'>
                </form>
                <a href='#' onclick='confirmation_post($liste, $id, \"$designation_text\", \"supprimer_$id\")'>
                    <img src='images/delete.png'>
                </a>
            </td>
            <td><a href='modifier_article.php?id_article=$id'><img src='images/edit.png'></a></td>
        </tr>";

        // Changement de couleur de fond pour chaque ligne
        $counter++;
        $bgcolor = ($counter % 2) == 0 ? "#FFFFFF" : "#EEEEEE"; 
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Requête SQL pour calculer le total des prix et prix vendeur
$query = "
  SELECT SUM(prix) AS total_prix, SUM(prix_vendeur) AS total_prix_vendeur
  FROM pm_articles
  WHERE liste = :liste
";

try {
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ligne) {
        echo "<tr>
            <td colspan='7'>TOTAL</td> 
            <td>{$ligne['total_prix']} €</td>
            <td>{$ligne['total_prix_vendeur']} €</td>
        </tr>";
    }
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

echo "</table>";

// Lien vers le reçu imprimable
echo "
<h3><a href='liste_articles.php?liste=$liste' target='_blank'>
    <img src='images/presse78.gif' align='center' border='0'> Voir le reçu imprimable
</a></h3>
";

?>
<script>
  focus_designation();
</script>
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>