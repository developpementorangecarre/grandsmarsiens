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


$nb_vente = 0;
$client = $_POST['client'] ?? null;
$posted = $_POST['posted'] ?? null;
$create = $_POST['create'] ?? null;

if ($posted == 1) {
    try {
        // Compter le nombre de ventes pour ce client
        $query = "SELECT count(*) FROM pm_ventes WHERE client = :client";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->execute();

        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        $nb_vente = $ligne ? $ligne['count(*)'] : 0;
        echo "-- > $nb_vente $client<HR>";

        if ($nb_vente < 1) {
            echo "initialisation de la vente...";

            // Ajouter la nouvelle vente avec la date actuelle
            $date = date('Y-m-d H:i:s'); // Récupère la date et l'heure actuelles
            $query = "INSERT INTO pm_ventes (client, date) VALUES (:client, :date)";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':client', $client, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();

            // Récupérer l'ID de la vente
            $query = "SELECT id FROM pm_ventes WHERE client = :client";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':client', $client, PDO::PARAM_INT);
            $stmt->execute();

            $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
            $vente = $ligne ? $ligne['id'] : null;

            if ($vente) {
                ouvre_page("vente_article.php?vente=$vente");
            }
        }

        // Liste des ventes à ce client
        $query = "SELECT id FROM pm_ventes WHERE client = :client ORDER BY id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->execute();

        echo "Listes des ventes à ce client ($client) : ";

        while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vente = $ligne['id'];
            echo "<form method='post' action='vente_article.php'>";
            echo "<li><a href='voir_vente.php?vente=$vente' target='_blank'>Voir la liste $vente</a>";
            echo "<input type='submit' name='envoyer' value='Ajouter des articles à la vente $vente'></center><br>";
            echo "<input type='hidden' name='vente' value='$vente'><br>";
            echo "<input type='hidden' name='posted' value='1'><br></form>";
        }

        echo "<hr>";
        echo "
        <form method='post' action='creer_vente.php'>
        <input type='hidden' name='posted' value='2'>
        <input type='hidden' name='create' value='4'>
        <input type='hidden' name='client' value='$client'><br>
        <center><input type='submit' name='envoyer' value='CREER UNE NOUVELLE VENTE'></center><br>
        </form>
        ";
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    }
} elseif ($create == 4) {
    try {
        echo "initialisation de la vente...";

        // Ajouter la nouvelle vente avec la date actuelle
        $date = date('Y-m-d H:i:s'); // Récupère la date et l'heure actuelles
        $query = "INSERT INTO pm_ventes (client, date) VALUES (:client, :date)";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        // Récupérer l'ID de la vente
        $query = "SELECT id FROM pm_ventes WHERE client = :client";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->execute();

        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        $vente = $ligne ? $ligne['id'] : null;

        if ($vente) {
            ouvre_page("vente_article.php?vente=$vente");
        }
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    }
} else {
    // Formulaire principal de création de vente
    echo "<h1 class='title2'>Créer une nouvelle vente</h1>";
    echo "<h3>Vente de " . htmlspecialchars($client ?? '', ENT_QUOTES, 'UTF-8') . "</h3>";
    echo "<form method='post' action='nouvelle_vente.php'>";
    echo "<hr>";
    echo "Client : <select name='client'>";

    try {
        $query = "SELECT id, nom, prenom FROM pm_personnes ORDER BY nom ASC";
        $stmt = $connection->prepare($query);
        $stmt->execute();

        while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $ligne['id'];
            $nom = htmlspecialchars($ligne['nom'], ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars($ligne['prenom'], ENT_QUOTES, 'UTF-8');
            echo "<option value='$id'>$nom $prenom</option>";
        }
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    }

    echo "</select><hr>";
    echo "<center><input type='submit' name='envoyer' value='ENVOYER'></center><br>";
    echo "<input type='hidden' name='posted' value='1'><br>";
    echo "</form>";
}


?>
<?php
tab_vente($connection);
echo "<br>";
tab_stock($connection);
?>
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>