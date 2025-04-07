<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 
  <meta http-equiv="content-type" content="text/html; " />
  <title>Les p'tits marsiens...</title>
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  
  <link href="default.css" rel="stylesheet" type="text/css" />
  
  <script language="javascript" type="text/javascript">
<!--

function ouvre_form(formulaire) { 

    document.getElementById(formulaire).submit(); 
} 

function GetId(id)
{
return document.getElementById(id);
}
var i=false; // La variable i nous dit si la bulle est visible ou non
 
function move(e) {
  if(i) {  // Si la bulle est visible, on calcul en temps reel sa position ideale
    if (navigator.appName!="Microsoft Internet Explorer") { // Si on est pas sous IE
    GetId("curseur").style.left=e.pageX + 5+"px";
    GetId("curseur").style.top=e.pageY + 10+"px";
    }
    else { // Modif propos� par TeDeum, merci �  lui
    if(document.documentElement.clientWidth>0) {
GetId("curseur").style.left=20+event.x+document.documentElement.scrollLeft+"px";
GetId("curseur").style.top=10+event.y+document.documentElement.scrollTop+"px";
    } else {
GetId("curseur").style.left=20+event.x+document.body.scrollLeft+"px";
GetId("curseur").style.top=10+event.y+document.body.scrollTop+"px";
         }
    }
  }
}
 
function montre(text) {
  if(i==false) {
  GetId("curseur").style.visibility="visible"; // Si il est cacher (la verif n'est qu'une securit�) on le rend visible.
  GetId("curseur").innerHTML = text; // on copie notre texte dans l'�l�ment html
  i=true;
  }
}
function cache() {
if(i==true) {
GetId("curseur").style.visibility="hidden"; // Si la bulle est visible on la cache
i=false;
}
}
document.onmousemove=move; // d�s que la souris bouge, on appelle la fonction move pour mettre � jour la position de la bulle.
//-->
</script>
</head>


<body>

<div id="page">
	

<?php include('menu_gauche.php'); ?>		

	
<div id="content">
		
<div><img src="images/banner.jpg" alt="" height="220" width="740" /></div>
		
<div class="boxed">
<h1 class="title2">Listes des personnes </h1>

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection=db_connect($host,$port,$db,$username,$password);

$nom = $_GET['nom'] ?? null;
$id = $_GET['id'] ?? null;
$debut = $_GET['debut'] ?? null;

try {
    $connection = db_connect($host, $port, $db, $username, $password);

    if (isset($id)) {
        $query = "SELECT * FROM pm_personnes WHERE id = :id ORDER BY id DESC";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } elseif (isset($debut)) {
        $query = "SELECT * FROM pm_personnes WHERE nom LIKE :debut ORDER BY id DESC";
        $stmt = $connection->prepare($query);
        $debut .= '%';
        $stmt->bindParam(':debut', $debut, PDO::PARAM_STR);
    } else {
        $query = "SELECT * FROM pm_personnes WHERE nom LIKE :nom ORDER BY id DESC";
        $stmt = $connection->prepare($query);
        $nom = "%$nom%";
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
    }

    $stmt->execute();
    $personnes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table class=sample>
        <td> Numéro </td>
        <td> Nom </td>
    ";

    $counter = 0;
    $bgcolor = "#FFFFFF";

    foreach ($personnes as $ligne) {
        $id = $ligne['id'];
        $nom = $ligne['nom'];
        $prenom = $ligne['prenom'];
        $adresse = $ligne['adresse'];
        $ville = $ligne['ville'];
        $tel = $ligne['tel'];
        $email = $ligne['email'];

        // Récupération des listes
        $query_liste = "
            SELECT a.liste, COUNT(*) AS nombre_articles 
            FROM pm_articles AS a
            JOIN pm_liste_articles AS l ON l.id = a.liste
            WHERE l.vendeur = :id
            GROUP BY a.liste
        ";
        $stmt_liste = $connection->prepare($query_liste);
        $stmt_liste->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_liste->execute();
        $listes = $stmt_liste->fetchAll(PDO::FETCH_ASSOC);

        $info_bulle_liste = "";
        foreach ($listes as $ligne_liste) {
            $liste_id = $ligne_liste['liste'];
            $nb_articles = $ligne_liste['nombre_articles'];
            $info_bulle_liste .= "<br> Liste $liste_id : $nb_articles articles";
        }

        // Récupération des ventes
        $query_vente = "
            SELECT a.vente, COUNT(*) AS nombre_articles 
            FROM pm_articles AS a
            JOIN pm_ventes AS v ON v.id = a.vente
            WHERE v.client = :id
            GROUP BY a.vente
        ";
        $stmt_vente = $connection->prepare($query_vente);
        $stmt_vente->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_vente->execute();
        $ventes = $stmt_vente->fetchAll(PDO::FETCH_ASSOC);

        $info_bulle_vente = "";
        foreach ($ventes as $ligne_vente) {
            $vente_id = $ligne_vente['vente'];
            $nb_articles = $ligne_vente['nombre_articles'];
            $info_bulle_vente .= "<br> Vente $vente_id : $nb_articles articles";
        }

        $info_bulle_personne = "Adresse : $adresse <br>Ville : $ville <br>E-Mail : $email <br> Téléphone : $tel <br>";

        echo "<tr bgcolor=$bgcolor>
            <td><center><a href=modifier_personne.php?id=$id> $id </a></center></td>
            <td onmouseover=\"montre('$info_bulle_personne');\" onmouseout=\"cache();\"> 
                <b>$nom</b> $prenom 
            </td>
            <td onmouseover=\"montre('$info_bulle_liste');\" onmouseout=\"cache();\">
                <form name=depot_$id action=nouvelle_liste.php method=post>
                    <input type=hidden name=vendeur value=$id>
                    <input type=hidden name=posted value=1>
                    <input type=submit value=\"DEPOTS\">
                </form>
            </td>
            <td onmouseover=\"montre('$info_bulle_vente');\" onmouseout=\"cache();\">
                <form name=vente_$id action=nouvelle_vente.php method=post>
                    <input type=hidden name=client value=$id>
                    <input type=hidden name=posted value=1>
                    <input type=submit value=\"ACHATS\">
                </form>
            </td>
            <td onmouseover=\"montre('$info_bulle_liste');\" onmouseout=\"cache();\">
                <form name=depot_$id action=nouveau_retrait.php method=post>
                    <input type=hidden name=vendeur value=$id>
                    <input type=hidden name=posted value=1>
                    <input type=submit value=\"RETRAIT\">
                </form>
            </td>
        </tr>";

        $counter++;
        $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des personnes : " . $e->getMessage();
}


?>
<div id="curseur" class="infobulle"></div>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>




<?php include("footer.php"); ?>


</body>
</html>