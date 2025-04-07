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

$nouvelle_bourse=$_POST['nouvelle_bourse'] ?? null;
$bourse_active=$_POST['bourse_active'] ?? null;
$posted = $_POST['posted'] ?? null;
$liste = $_GET['liste'] ?? $_POST['liste'] ?? null;

$msg="";
$connection=db_connect($host,$port,$db,$username,$password);

try {
 // Création d'une nouvelle bourse
 if (!empty($_POST['nouvelle_bourse'])) {
    $nouvelle_bourse = trim($_POST['nouvelle_bourse']);

    $msg = "<h3>La bourse " . htmlspecialchars($nouvelle_bourse, ENT_QUOTES, 'UTF-8') . " a été créée et activée</h3>";

    // Exécution en transaction
    $connection->beginTransaction();

    // Désactiver uniquement les bourses actives
    $query = "UPDATE pm_bourses SET statut=0 WHERE statut=1";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    // Insérer la nouvelle bourse et l'activer
    $query = "INSERT INTO pm_bourses VALUES (:nouvelle_bourse, NULL, NULL, 1)";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':nouvelle_bourse', $nouvelle_bourse, PDO::PARAM_STR);
    $stmt->execute();

    $connection->commit(); // Valider la transaction
  }

  // Activation d'une bourse existante
  if (!empty($_POST['bourse_active'])) {
    $bourse_active = $_POST['bourse_active']; // Sécuriser en forçant un entier

    // Exécution en transaction
    $connection->beginTransaction();

    // Désactiver uniquement les bourses actives
    $query = "UPDATE pm_bourses SET statut=0 WHERE statut=1";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    // Activer la bourse sélectionnée
    $query = "UPDATE pm_bourses SET statut=1 WHERE id=:bourse_active";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':bourse_active', $bourse_active, PDO::PARAM_STR);
    $stmt->execute();

    $connection->commit(); // Valider la transaction
    }
} catch (PDOException $e) {
  $connection->rollBack(); // Annuler la transaction en cas d'erreur
  die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}


/////// DEBUT FORMULAIRE ///////


try {
  $bourse = liste2bourse($liste, $connection);
  $code = liste2code($liste, $connection);
} catch (Exception $e) {
  die("Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}



echo "<h1 class=\"title2\">Gérer les bourses</h1>";

if (!empty($msg)) {
    echo "<p class='message'>$msg</p>";
}

echo "<table class='sample'>";

try {
    $query = "SELECT id, statut FROM pm_bourses ORDER BY id ASC";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        foreach ($result as $ligne) {
            $id = htmlspecialchars($ligne['id'], ENT_QUOTES, 'UTF-8');
            $statut = (int) $ligne['statut']; // Assure que c'est bien un entier

            echo "<tr>";
            echo "<td><span style='font-size: 1.5em;'>$id</span></td>";

            if ($statut === 0) {
                echo "<td>
                        <form method='post' action='gerer_bourses.php'>
                            <input type='hidden' name='bourse_active' value='$id'>
                            <input type='submit' value='Activer cette bourse'>
                        </form>
                      </td>";
            } else {
                echo "<td style='background-color: #22FF22; font-weight: bold; font-size: 1.5em;'>BOURSE ACTIVE</td>";
            }

            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='2'>Aucune bourse trouvée.</td></tr>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
}

echo "</table>";

echo "<hr><h2>Créer une nouvelle bourse :</h2>";

echo "<form method='post' action='gerer_bourses.php'>
        <label for='nouvelle_bourse'>Nom :</label>
        <input type='text' name='nouvelle_bourse' id='nouvelle_bourse' size='20' required>
        <input type='submit' value='Créer cette bourse'>
      </form>";

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