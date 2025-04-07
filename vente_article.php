<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<SCRIPT>

var fnOnClick = function(){
	if (confirm('Voulez-vous supprimer ?')){
		alert('OK');
		doc.getEl.sublmit();
	}else{
//		alert('OK');
	}
}

function confirmation(vente,id,designation) { 
var msg = 'confirmer la suppression de'+designation; 
if (confirm(msg)) {
    document.location.replace('vente_article.php?vente='+vente+'&id_article='+id); 
  }
  else
  {
  document.location.replace('vente_article.php?vente='+vente);   
  }
} 

function confirmation_post(vente,id,designation,formulaire) { 
var msg = ' Confirmer la suppression de : '+designation; 
if (confirm(msg)) {
    document.getElementById(formulaire).submit(); 
  }
  else
  {
  document.location.replace('vente_article.php?vente='+vente);   
  }
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
		
<div id="banner" style="height: 180px">

<?php
include("functions.inc.php");
include("conf.inc.php");

$msg = "";
$vente = filter_input(INPUT_GET, 'vente', FILTER_SANITIZE_NUMBER_INT);
$article_a_supprimer = filter_input(INPUT_POST, 'id_article', FILTER_SANITIZE_NUMBER_INT);
$connection=db_connect($host,$port,$db,$username,$password);
$etat = 0;

if (isset($article_a_supprimer)) {
    $vente = filter_input(INPUT_POST, 'vente', FILTER_SANITIZE_NUMBER_INT);
    $query = "UPDATE pm_articles SET etat=1, vente=0 WHERE id = :article_a_supprimer AND etat = 2";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':article_a_supprimer', $article_a_supprimer, PDO::PARAM_INT);
    $stmt->execute();
    $msg = "<h3>L'article a été retiré</h3>";
}

$posted = filter_input(INPUT_POST, 'posted', FILTER_SANITIZE_NUMBER_INT);
if (isset($posted)) {
	$reference_article = $_POST['reference_article'] ?? null;
	$vente = filter_input(INPUT_POST, 'vente', FILTER_SANITIZE_NUMBER_INT);

	if ($reference_article) {
		// Limiter la division à deux parties : le code et le numéro
		$split_result = preg_split('/[A-Za-z]+/', $reference_article, 2);
		
		if (count($split_result) < 2) {
			$msg = "Erreur dans le format de la référence article.";
		} else {
			list($code, $numero) = $split_result;
			
			$matches = [];
			// On essaie d'extraire la partie bourse avec une expression régulière
			if (preg_match('/([A-Za-z]+)/', $reference_article, $matches)) {
				$bourse = isset($matches[1]) ? $matches[1] : '';
			} else {
				$bourse = '';
			}
			
			// Vérifier si les parties code et bourse sont valides avant de procéder
			if ($code && $bourse) {
				$liste = codeetbourse2liste($code, $bourse, $connection);
			} else {
				$msg = "Erreur dans le format de la référence article.";
			}
		}
	} else {
		$msg = "Référence article invalide ou manquante.";
		echo($reference_article);
	}
    
    $query = "SELECT id FROM pm_articles WHERE liste = :liste AND numero = :numero";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $article = $ligne['id'];
    }
    
    $query = "SELECT a.designation, a.prix, a.liste, a.numero, a.etat, a.vente, 
                     l.code, l.bourse,
                     d.libelle AS designation_courte,  
                     c.libelle AS couleur,
                     m.libelle AS marque
              FROM pm_articles AS a
              JOIN pm_designations_courtes AS d ON d.id = a.designation_courte
              JOIN pm_couleurs AS c ON c.id = a.couleur
              JOIN pm_marques AS m ON m.id = a.marque
              JOIN pm_liste_articles AS l ON l.id = a.liste
              WHERE a.id = :article";
    
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':article', $article, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $designation = $ligne['designation'];
        $prix = $ligne['prix'];
        $liste = $ligne['liste'];
        $code = $ligne['code'];
        $bourse = $ligne['bourse'];
        $numero = $ligne['numero'];
        $etat = $ligne['etat'];
        $designation_courte = $ligne['designation_courte'];
        $couleur = $ligne['couleur'];
        $marque = $ligne['marque'];
        $vente_enregistree = $ligne['vente'];
        $msg = "<table class='sample'>
                    <tr>
                        <td><font color='#FF0000' size='+3'><b>$code$bourse-$numero</b></font></td>
                        <td><font color='#000000' size='+3'><b>$prix €</b></font></td>
                    </tr>
                </table>
                <font size='+1'>
                <table class='sample'>
                    <tr>
                        <td>$designation_courte</td>
                        <td>$designation</td>
                        <td>$couleur</td>
                        <td>$marque</td>
                    </tr>
                </table>
                </font>";
    }
    
    if ($etat == 2 && $vente == $vente_enregistree) {
        $msg .= "<br><font size='+3'><table class='sample'>
                    <tr>
                        <td><font color='#FF0000'><b>Cet article est déjà enregistré</b></font></td>
                    </tr>
                </table></font>";
    }
    
    if ($etat == 2 && $vente != $vente_enregistree) {
        $msg .= "<br><font size='+3'><table class='sample'>
                    <tr>
                        <td><font color='#FF0000'><b>Erreur : Cet article est déjà vendu !</b></font></td>
                    </tr>
                </table></font>";
    }
    
    if ($etat == 3) {
        $msg .= "<br><font size='+3'><table class='sample'>
                    <tr>
                        <td><font color='#FF0000'><b>Erreur : Cet article a été repris !</b></font></td>
                    </tr>
                </table></font>";
    }

    $query = "UPDATE pm_articles SET etat = 2, vente = :vente WHERE id = :article AND etat = 1";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
    $stmt->bindParam(':article', $article, PDO::PARAM_INT);
    $stmt->execute();
}

$query = "SELECT p.prenom, p.nom, p.categorie FROM pm_ventes AS v
          JOIN pm_personnes AS p ON v.client = p.id
          WHERE v.id = :vente";
$stmt = $connection->prepare($query);
$stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
$stmt->execute();

while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $nom_client = $ligne['prenom'] . " " . $ligne['nom'];
    $categorie = $ligne['categorie'];
}

$query = "SELECT v.etat FROM pm_ventes AS v WHERE v.id = :vente";
$stmt = $connection->prepare($query);
$stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
$stmt->execute();

while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $etat_vente = $ligne['etat'];
    if ($etat_vente == 2) {
        header("Location: vente_fin.php?vente=$vente");
        exit;
    }
}

echo $msg;
?>

<!-- <img src="images/banner.jpg" alt="" height="220" width="740" /> -->


</div>

<div class="boxed" >
			
<!-- <h3>Caisse</h3> -->

<?php



echo "<h1 class='title2'>Vente n°$vente : $nom_client</h1>";

if ($etat_vente == 1) {
    echo "
    <form method='post' action='vente_article.php' name='vente'>
        Référence Article (format numero_liste<b>-</b>numero_article_dans_liste) : 
        <br><input type='text' size='110' name='reference_article'>
        <hr>
        <input type='hidden' name='vente' value='$vente'>
        <center><input type='submit' name='envoyer' value='AJOUTER'></center>
        <input type='hidden' name='posted' value='1'>
    </form>";
}

$counter = 0;
$bgcolor = "#FFFFFF";

echo "<table class='sample'>
    <tr bgcolor='#FFFFBB'>
        <td>Référence</td>
        <td></td>
        <td>Désignation</td>
        <td></td>
        <td></td>
        <td>Type</td>
        <td>Taille</td>
        <td>Prix</td>
    </tr>";

$query = "SELECT 
            a.id AS id, a.numero AS numero, a.designation AS designation, a.prix AS prix, a.prix_vendeur AS prix_vendeur, a.liste AS liste,
            l.code AS code, l.bourse AS bourse,
            ty.type AS type, ty.image AS type_image,
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
          JOIN pm_liste_articles AS l ON a.liste = l.id
          WHERE a.vente = :vente
          ORDER BY liste, numero";

$stmt = $connection->prepare($query);
$stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
$stmt->execute();

while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $numero = $ligne['numero'];
    $liste = $ligne['liste'];
    $bourse = $ligne['bourse'];
    $code = $ligne['code'];
    $designation_text = text2js($ligne['designation']);
    $designation = htmlspecialchars($ligne['designation'], ENT_QUOTES, 'UTF-8');
    $type = htmlspecialchars($ligne['type'], ENT_QUOTES, 'UTF-8');
    $type_image = htmlspecialchars($ligne['type_image'], ENT_QUOTES, 'UTF-8');
    $taille = htmlspecialchars($ligne['taille'], ENT_QUOTES, 'UTF-8');
    $prix = $ligne['prix'];
    $id = $ligne['id'];
    $designation_courte = htmlspecialchars($ligne['designation_courte'], ENT_QUOTES, 'UTF-8');
    $couleur = htmlspecialchars($ligne['couleur'], ENT_QUOTES, 'UTF-8');
    $marque = htmlspecialchars($ligne['marque'], ENT_QUOTES, 'UTF-8');
    
    $prix = number_format($prix, 2, ',', ' ');
    
    $counter++;
    $bgcolor = ($counter % 2 == 0) ? "#F7F7F7" : "#FFFFFF";
    $supprimer_lien = "<a href='javascript:confirmation_post(\"vente_article.php\", \"id_article\", \"$id\");'><img src='img/supprimer.gif' alt='Supprimer' title='Supprimer'></a>";

    echo "<tr bgcolor='$bgcolor'>
            <td><b>$code$bourse-$numero</b></td>
            <td>$supprimer_lien</td>
            <td>$designation_courte $designation</td>
            <td>$couleur</td>
            <td>$marque</td>
            <td>$type</td>
            <td>$taille</td>
            <td>$prix €</td>
        </tr>";
}
echo "</table>";

echo "
<form method=post action=terminer_vente.php>
<input type=hidden name=vente value=$vente>
<center><input type=submit name=envoyer value=\"              TERMINER            \"></center>
<input type=hidden name=posted value=1>
</form>
";

//phpinfo();
echo "<hr>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
echo "<br>";
tab_vente($connection);
echo "<br>";
?>
	
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>
<script>
  document.vente.reference_article.focus();
</script>
<?php include("footer.php");?>


</body>
</html>