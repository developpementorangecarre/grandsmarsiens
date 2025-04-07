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

$posted = filter_input(INPUT_POST, 'posted', FILTER_SANITIZE_NUMBER_INT);
$create = filter_input(INPUT_POST, 'create', FILTER_SANITIZE_NUMBER_INT);
$client = filter_input(INPUT_POST, 'client', FILTER_SANITIZE_NUMBER_INT);
$nb_liste=0;
$vente = null;


// echo "<h1>posted $posted</h1>";
// echo "<h1>create $create</h1>";

if (isset($create) && $create == 4) {
    echo "Initialisation de la vente...";

    try {
        // Insérer une nouvelle vente
        $query = "INSERT INTO pm_ventes (client, etat) VALUES (:client, 1)";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->execute();

        // Récupérer l'ID de la dernière vente pour ce client
        $query = "SELECT id FROM pm_ventes WHERE client = :client ORDER BY id DESC LIMIT 1";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':client', $client, PDO::PARAM_INT);
        $stmt->execute();
        $vente = $stmt->fetchColumn(); // Récupère directement l'ID

        if ($vente) {
            ouvre_page("vente_article.php?vente=$vente");
        } else {
            echo "Erreur : Impossible de récupérer l'ID de la vente.";
        }
    } catch (PDOException $e) {
        echo "Erreur lors de l'initialisation de la vente : " . $e->getMessage();
    }
}




?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>