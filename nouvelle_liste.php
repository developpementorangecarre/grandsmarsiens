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

// var_dump($_POST);

$posted = isset($_POST['posted']) ? $_POST['posted'] : null;
$create = isset($_POST['create']) ? $_POST['create'] : null;
$vendeur = isset($_POST['vendeur']) ? $_POST['vendeur'] : null;
$etat_liste = isset($_POST['etat_liste']) ? $_POST['etat_liste'] : null;
$nb_liste=0;
$code=0;
// echo "<h1>posted $posted</h1>";
// echo "<h1>create $create</h1>";

if ((isset($posted))and($posted=1)){
	
	if (!isset($_POST['vendeur']) || empty($_POST['vendeur']) || !is_numeric($_POST['vendeur'])) {
		die("Erreur : vous devez sélectionner un vendeur valide !");
	} else {
		$vendeur = (int) $_POST['vendeur']; // Convertir en entier pour éviter SQL injection
	}		

	
	// $query="select count(*) from pm_liste_articles where vendeur=$vendeur ";
	// echo($vendeur);

	try {
		$query = "SELECT count(*) FROM pm_liste_articles WHERE vendeur = ?";
		$stmt = $connection->prepare($query);
		$stmt->execute([(int)$vendeur]); 
	
		$nb_liste = $stmt->fetchColumn();

		if ($nb_liste < 1) {
			$bourse = getbourse($connection);
			$liste = creer_liste($vendeur, $bourse, $connection);
			ouvre_page("nouveau_article.php?liste=$liste");
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . $e->getMessage());
	}


	// ACTIVATION OU DESACTIVATION DE LISTEs	
	// Si liste à activer ou inactiver : test de la variable postée
	if (isset($etat_liste)) {
	$etat_liste=$_POST['etat_liste'];
	$liste=$_POST['liste'];
	$query="update pm_liste_articles set etat=$etat_liste where id=$liste " ;
	$result=exec_sql($query,$connection);
	}

	try {
		$query = "SELECT p.prenom, p.nom 
				FROM pm_personnes AS p 
				WHERE p.id = :vendeur";

		$stmt = $connection->prepare($query);
		$stmt->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
		$stmt->execute();

		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$vendeur_nom = $ligne['prenom'] . ' ' . $ligne['nom']; 
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . $e->getMessage());
	}

	// --------------------------------------- Affichage des listes actives	 ----------------------------------------------------------------------------------------
	try {
		$query = "SELECT l.id FROM pm_liste_articles AS l 
				WHERE l.vendeur = :vendeur AND l.etat = 0 
				ORDER BY l.id";

		$stmt = $connection->prepare($query);
		$stmt->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
		$stmt->execute();

		echo "<h1 class=\"title2\">Les Listes d'articles pour $vendeur_nom </h1>";

		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$liste = $ligne['id'];
			echo "<table class=sample width=100%><tr><td width=8%>";
			$bourse = liste2bourse($liste, $connection);
			$code = liste2code($liste, $connection);
			echo "<h3>&nbsp $code$bourse &nbsp</h3></td><td>";
			echo "<form method=get action=liste_articles.php target=get>";
			echo "<input type=submit name=envoyer value=\" Reçu imprimable\">";
			echo "<input type=hidden name=liste value=$liste>";
			echo "<input type=hidden name=posted value=1></form>";
			echo "</td><td>";
			echo "<form method=post action=nouveau_article.php>";
			echo "<input type=submit name=envoyer value=\" Ajouter des articles \">";
			echo "<input type=hidden name=liste value=$liste>";
			echo "<input type=hidden name=posted value=1></form>";
			echo "</td><td>";
			echo "<form method=get action=depot_articles.php>";
			echo "<input type=submit name=envoyer value=\" Modifier/supprimer des articles \">";
			echo "<input type=hidden name=liste value=$liste>";
			echo "<input type=hidden name=posted value=1></form>";
			echo "</td><td>";
			echo "<form method=post action=nouvelle_liste.php>";
			echo "<input type=submit name=envoyer value=\" Désactiver \">";
			echo "<input type=hidden name=liste value=$liste>";
			echo "<input type=hidden name=etat_liste value=2>";
			echo "<input type=hidden name=vendeur value=$vendeur>";
			echo "<input type=hidden name=posted value=1></form>";
			echo "</td></tr></table>";
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . $e->getMessage());
	}

	echo "<hr>";

	// --------------------------------------- Affichage des listes INactives	 ----------------------------------------------------------------------------------------
	try {
		$query = "SELECT l.id FROM pm_liste_articles AS l 
				  WHERE l.vendeur = :vendeur AND l.etat = 2 
				  ORDER BY l.id";
		$stmt = $connection->prepare($query);
		$stmt->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
		$stmt->execute();
	
		echo "<h2>Les Listes Inactives</h2>";
	
		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$liste = $ligne['id'];
			// Récupérer dynamiquement la bourse et le code pour la liste actuelle
			$bourse = liste2bourse($liste, $connection);
			$code   = liste2code($liste, $connection);
			
			echo "<table class='sample' width='100%'><tr><td width='8%'>";
			echo "<h3>&nbsp; $code$bourse &nbsp;</h3></td><td>";
			echo "<form method='get' action='liste_articles.php' target='get'>";
			echo "<input type='submit' name='envoyer' value=' Reçu imprimable'>";
			echo "<input type='hidden' name='liste' value='$liste'>";
			echo "<input type='hidden' name='etat_liste' value='0'>";
			echo "<input type='hidden' name='posted' value='1'></form>";
			echo "</td><td>";
			echo "<form method='post' action='nouvelle_liste.php'>";
			echo "<input type='submit' name='envoyer' value=' Réactiver la liste '>";
			echo "<input type='hidden' name='liste' value='$liste'>";
			echo "<input type='hidden' name='vendeur' value='$vendeur'>";
			echo "<input type='hidden' name='etat_liste' value='0'>";
			echo "<input type='hidden' name='posted' value='1'></form>";
			echo "</td></tr></table>";
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . $e->getMessage());
	}	

	echo "<hr>";

	try {
		$query = "SELECT id, statut FROM pm_bourses ORDER BY id ASC";
		$stmt = $connection->prepare($query);
		$stmt->execute();

		echo "<form method=post action=creer_liste.php>";
		echo "<center>code bourse : <select name=bourse>";

		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id = $ligne['id'];
			$statut = $ligne['statut'];
			$selected = ($statut == 1) ? "selected" : "";
			echo "<option value=\"$id\" $selected>$id</option>";
		}

		echo "</select>";
		echo "<input type=hidden name=posted value=2>";
		echo "<input type=hidden name=create value=4>";
		echo "<input type=hidden name=vendeur value=\"$vendeur\">";
		echo "<input type=submit name=envoyer value=\"              CREER UNE NOUVELLE LISTE            \"></center><br>";
		echo "</form>";
	} catch (PDOException $e) {
		die("Erreur SQL : " . $e->getMessage());
	}
}
elseif (isset($create) && $create == 4){
    echo "Création de la liste...";
    
    $liste = creer_liste($vendeur, $bourse, $connection);

    try {
        $query = "SELECT id FROM pm_liste_articles WHERE vendeur = ? ORDER BY id DESC LIMIT 1";
        $stmt = $connection->prepare($query);
        $stmt->execute([(int)$vendeur]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            $liste = $ligne['id'];
        }

        ouvre_page("nouveau_article.php?liste=" . urlencode($liste));
    } catch (PDOException $e) {
        die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

else {

//****************** Formulaire principal ***/

	echo "<h1 class=\"title2\">Créer une nouvelle liste</h1>
	<h3>Liste de $vendeur</h3>
	<form method=post action=nouvelle_liste.php>
	";
	echo "<hr>";
	echo "Vendeur : <select name=vendeur required>";

	try {
		$query = "SELECT * FROM pm_personnes ORDER BY nom ASC";
        $stmt = $connection->prepare($query);
        $stmt->execute();
		$first = true; 
		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id=$ligne['id'];
			$nom = htmlspecialchars($ligne['nom']); // Sécurité contre les attaques XSS
			$prenom = htmlspecialchars($ligne['prenom']); 

			$selected = ($first) ? "selected" : ""; // to select the first option by default
			$first = false;
			//echo($id);
			echo "<option value=$id>$nom $prenom</option>";
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
	}
	
	echo "</select>";

	echo "code bourse : <select name=bourse>";

	try {
		$query = "SELECT id, statut FROM pm_bourses ORDER BY id ASC";
        $stmt = $connection->prepare($query);
        $stmt->execute();

		while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id=$ligne['id'];
			$statut=$ligne['statut'];
			
			$selected = ($statut == 1) ? "selected" : "";
            echo "<option value=\"$id\" $selected>$id</option>";
		}
	} catch (PDOException $e) {
		die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
	}
	
	echo "</select>";

	
	echo "<hr>";
	echo "
	<center><input type=submit name=envoyer value=\"              ENVOYER            \"></center><br>
	<input type=hidden name=posted value=1><br>
	</form>
	";

}
?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>