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

$posted = $_POST['posted'] ?? null;
$create = $_POST['create'] ?? null;
$vendeur = $_POST['vendeur'] ?? null;
$nb_liste = 0;

try {
    $connection = db_connect($host, $port, $db, $username, $password);

    if (isset($posted) && $posted == 1) {
        $query = "SELECT COUNT(*) as nb FROM pm_liste_articles WHERE vendeur = :vendeur";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
        $stmt->execute();
        $nb_liste = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];

        if ($nb_liste < 1) {
            echo "Aucune liste déposée...";
        } elseif ($nb_liste == 1) {
            $query_liste = "SELECT id FROM pm_liste_articles WHERE vendeur = :vendeur";
            $stmt_liste = $connection->prepare($query_liste);
            $stmt_liste->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
            $stmt_liste->execute();

            while ($ligne_liste = $stmt_liste->fetch(PDO::FETCH_ASSOC)) {
                $liste = $ligne_liste['id'];
                ouvre_page("retrait_cocher_articles.php?liste=$liste");
            }
        }

        $query = "SELECT id, code, bourse FROM pm_liste_articles WHERE vendeur = :vendeur ORDER BY id";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vendeur', $vendeur, PDO::PARAM_INT);
        $stmt->execute();
        $listes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Listes d'articles existantes pour ce vendeur : ";
        
        foreach ($listes as $ligne) {
            $liste = $ligne['id'];
            $code = $ligne['code'];
            $bourse = $ligne['bourse'];

            echo "<table class='sample' width='100%'><tr>
                <td width='8%'><h3>&nbsp $code $bourse &nbsp</h3></td>
                <td width='30%'>
                    <form method='get' action='retrait_articles.php' target='get'>
                        <input type='submit' name='envoyer' value=' Dépôt : Reçu imprimable '>
                        <input type='hidden' name='liste' value='$liste'>
                        <input type='hidden' name='posted' value='1'>
                    </form>
                </td>
                <td width='30%'>
                    <form method='get' action='retrait_cocher_articles.php'>
                        <input type='submit' name='envoyer' value='Retrait d&apos;article'>
                        <input type='hidden' name='liste' value='$liste'>
                        <input type='hidden' name='posted' value='1'>
                    </form>
                </td>
            </tr></table>";
        }
        echo "<hr>";

    } else {
        // ****************** Formulaire principal ***/
        echo "<h1 class='title2'>Retirer des articles d'une liste</h1>
        <h3>Liste de $vendeur</h3>
        <form method='post' action='nouveau_retrait.php'>
        <hr>
        Vendeur : <select name='vendeur'>";

        $query = "SELECT id, nom, prenom FROM pm_personnes ORDER BY nom ASC";
        $stmt = $connection->query($query);
        $vendeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vendeurs as $ligne) {
            $id = $ligne['id'];
            $nom = $ligne['nom'];
            $prenom = $ligne['prenom'];
            echo "<option value='$id'>$nom $prenom</option>";
        }

        echo "</select>
        <hr>
        <center><input type='submit' name='envoyer' value='ENVOYER'></center><br>
        <input type='hidden' name='posted' value='1'><br>
        </form>";
    }
} catch (PDOException $e) {
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