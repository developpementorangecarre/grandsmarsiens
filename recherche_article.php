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

$msg = "";
$connection = db_connect($host, $port, $db, $username, $password);

echo "<h1 class=\"title2\">Rechercher un article</h1>
<form method=\"get\" action=\"resultat_recherche_articles.php\" name=\"article\" id=\"article\">";

// DESIGNATION COURTE
echo "Désignation courte : <select name=\"designation_courte\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_designations_courtes ORDER BY ordre ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

// COULEUR
echo "<br>Couleur : <select name=\"couleur\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_couleurs ORDER BY ordre ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

// MARQUE
echo "<br>Marque : <input type=\"text\" id=\"marque_saisie\" onchange=\"refresh_dropdown()\" size=\"1\">";
echo "<select id=\"marque\" name=\"marque\" onchange=\"refresh_input()\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_marques ORDER BY ordre ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

// DESIGNATION
echo "<br>Désignation : <input type=\"text\" size=\"110\" name=\"designation\">";

// TYPE
echo "<br>Type : <select name=\"type\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_types ORDER BY id ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

// TAILLE
echo "<br>Taille : <select name=\"taille\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_tailles ORDER BY id ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

// ÉTAT DE LA LISTE
echo "<br>État de la liste : <select name=\"etat_liste\">
        <option value=\"\">Critère vide</option>";

try {
    $stmt = $connection->prepare("SELECT * FROM pm_etat_liste ORDER BY id ASC");
    $stmt->execute();
    while ($ligne = $stmt->fetch(PDO::FETCH_NUM)) {
        $id = $ligne[0];
        $valeur = $ligne[1];
        echo "<option value=\"$id\">" . htmlspecialchars($valeur) . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
}

echo "</select>";

echo "<hr>
<center><input type=\"submit\" name=\"envoyer\" value=\"Rechercher\"></center><br>
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