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

$pdo=db_connect($host,$port,$db,$username,$password);

$posted=$_POST['posted'] ?? null;

if (!empty($_POST['posted'])) {
    try {
        // Nettoyage des entrées avec text2sql()
        $nom = text2sql($_POST['nom']);
        $prenom = text2sql($_POST['prenom']);
        $adresse = text2sql($_POST['adresse']);
        $ville = text2sql($_POST['ville']);
        $tel = text2sql($_POST['tel']);
        $email = text2sql($_POST['email']);
        $categorie = $_POST['categorie'];

        // Vérification si la personne existe déjà
        $query = "SELECT COUNT(*) FROM pm_personnes 
                  WHERE nom = ? AND prenom = ? AND adresse = ? AND ville = ? 
                  AND email = ? AND tel = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nom, $prenom, $adresse, $ville, $email, $tel]);
        $compte = $stmt->fetchColumn();

        if ($compte < 1) {
            // Insérer la nouvelle personne
            $query = "INSERT INTO pm_personnes (nom, prenom, adresse, ville, tel, email, categorie) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nom, $prenom, $adresse, $ville, $tel, $email, $categorie]);

            echo "<h1 class=\"title2\">Nouvelle personne créée !!</h1>";
        } else {
            echo "<h1 class=\"title2\">Cette personne existe déjà !!</h1>";
        }

        // Récupérer les informations de la personne
        $query = "SELECT id, nom, prenom, adresse, ville, tel, email 
                  FROM pm_personnes 
                  WHERE nom = ? AND prenom = ? AND adresse = ? AND ville = ? 
                  AND email = ? AND tel = ? 
                  ORDER BY id ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nom, $prenom, $adresse, $ville, $email, $tel]);

        while ($ligne = $stmt->fetch()) {
            echo "<h3>Numéro " . htmlspecialchars($ligne['id']) . "</h3><br>";
            echo "<h4>
                    <table class=\"sample\">
                        <tr><td>NOM : " . htmlspecialchars($ligne['nom']) . "</td>
                            <td>PRÉNOM : " . htmlspecialchars($ligne['prenom']) . "</td>
                        </tr>
                        <tr><td colspan=\"2\">ADRESSE : " . htmlspecialchars($ligne['adresse']) . "</td></tr>
                        <tr><td colspan=\"2\">VILLE : " . htmlspecialchars($ligne['ville']) . "</td></tr>
                        <tr><td>EMAIL : " . htmlspecialchars($ligne['email']) . "</td>
                            <td>TÉL : " . htmlspecialchars($ligne['tel']) . "</td>
                        </tr>
                    </table>
                  </h4>";
        }

        // Sélection du code bourse
        echo '<form method="post" action="creer_liste.php">
                <center>
                    Code bourse : <select name="bourse">';
        
        $query = "SELECT id, statut FROM pm_bourses ORDER BY id ASC";
        $stmt = $pdo->query($query);
        
        while ($ligne = $stmt->fetch()) {
            $codebourse = htmlspecialchars($ligne['id']);
            $selected = ($ligne['statut'] == 1) ? 'selected' : '';
            echo "<option value=\"$codebourse\" $selected>$codebourse</option>";
        }

        echo '</select>
                <input type="hidden" name="posted" value="2">
                <input type="hidden" name="create" value="4">
                <input type="hidden" name="vendeur" value="' . htmlspecialchars($id) . '">
                <br><br>
                <input type="submit" name="envoyer" value="CRÉER UNE NOUVELLE LISTE">
              </center>
              </form>
              <br>
              <form method="post" action="nouvelle_vente.php">
                <input type="hidden" name="client" value="' . htmlspecialchars($id) . '">
                <center><input type="submit" name="envoyer" value="CLIENT : NOUVELLE VENTE"></center>
              </form>
              <br>
              <p>
                L\'association <strong>Les P\'tits Marsiens</strong> dispose de moyens informatiques destinés à gérer plus facilement les ventes et les dépôts d\'articles.
              </p>
              <p>
                Les informations enregistrées sont réservées à l\'usage de l\'association et ne peuvent être communiquées qu\'aux adhérents assurant la gestion de l\'association.
              </p>
              <p>
                Conformément aux articles 39 et suivants de la loi n° 78-17 du 6 janvier 1978 relative à l\'informatique, aux fichiers et aux libertés, toute personne peut obtenir communication et, le cas échéant, rectification ou suppression des informations la concernant, en s\'adressant par courrier ou par e-mail à l\'association.
              </p>';

    } catch (PDOException $e) {
        echo "<h1 class='title2'>Erreur lors de l'exécution de la requête.</h1>";
        echo "<p class='error'>Détails : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo '
    <h1 class="title2">Créer une nouvelle personne</h1>
    <form method="post" action="nouvelle_personne.php">
        <table>
            <tr><td><label for="nom">Nom :</label></td>
                <td><input type="text" size="80" name="nom" id="nom"></td></tr>
            <tr><td><label for="prenom">Prénom :</label></td>
                <td><input type="text" size="80" name="prenom" id="prenom"></td></tr>
            <tr><td><label for="adresse">Adresse :</label></td>
                <td><textarea cols="63" rows="2" name="adresse" id="adresse"></textarea></td></tr>
            <tr><td><label for="ville">Ville :</label></td>
                <td><input type="text" size="80" name="ville" id="ville" value="Saint Mars du Désert"></td></tr>
            <tr><td><label for="tel">Téléphone :</label></td>
                <td><input type="text" size="80" name="tel" id="tel"></td></tr>
            <tr><td><label for="email">Email :</label></td>
                <td><input type="email" size="80" name="email" id="email"></td></tr>
            <tr><td><label for="categorie">Catégorie :</label></td>
                <td><select name="categorie" id="categorie">';

    $stmt = $pdo->query("SELECT id, categorie FROM pm_categories ORDER BY id ASC");
    
    while ($ligne = $stmt->fetch()) {
        echo "<option value=\"" . htmlspecialchars($ligne['id']) . "\">" . htmlspecialchars($ligne['categorie']) . "</option>";
    }

    echo '</select></td></tr>
        </table>
        <br>
        <center><input type="submit" name="envoyer" value="ENVOYER"></center>
        <input type="hidden" name="posted" value="1">
    </form>
    <hr>';
}


?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>