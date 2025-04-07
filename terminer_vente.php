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
$vente = filter_input(INPUT_POST, 'vente', FILTER_SANITIZE_NUMBER_INT);
$nb_liste=0;

if (isset($posted) && $posted == 1) {
    echo "<h1 class=\"title2\">Fin de la vente...</h1>";

    try {
        $query = "UPDATE pm_ventes SET etat = 2 WHERE id = :vente";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
        $stmt->execute();

        echo "<h3><a href=\"voir_vente.php?vente=$vente\" target=_blank>
                <img src='images/presse78.gif' align=center border=0> Voir le reçu imprimable
              </a></h3>";

        $counter = 0;
        $bgcolor = "#FFFFFF";

        echo "<table class=sample>
            <tr bgcolor=#FFFFBB>
                <td> Référence </td>
                <td> </td>
                <td> Désignation </td>
                <td> </td>
                <td> </td>
                <td> Type </td>
                <td> Taille </td>
                <td> Prix </td>
            </tr>";

        $query = "SELECT 
            a.id, a.numero, a.designation, a.prix, a.prix_vendeur, a.liste,
            ty.type, ty.image as type_image,
            ta.taille, 
            d.libelle as designation_courte, 
            c.libelle as couleur,
            m.libelle as marque  
        FROM pm_articles AS a
        JOIN pm_types AS ty ON a.type = ty.id
        JOIN pm_tailles AS ta ON a.taille = ta.id
        JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
        JOIN pm_couleurs AS c ON a.couleur = c.id
        JOIN pm_marques AS m ON a.marque = m.id
        WHERE a.vente = :vente
        ORDER BY liste, numero";

        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($articles as $ligne) {
            $numero = $ligne['numero'];
            $liste = $ligne['liste'];
            $designation = $ligne['designation'];
            $type = $ligne['type'];
            $type_image = $ligne['type_image'];
            $taille = $ligne['taille'];
            $prix = $ligne['prix'];
            $designation_courte = $ligne['designation_courte'];
            $couleur = $ligne['couleur'];
            $marque = $ligne['marque'];

            $bourse = liste2bourse($liste, $connection);
            $code = liste2code($liste, $connection);

            echo "<tr bgcolor=$bgcolor>
                <td> $code $bourse-$numero  </td>
                <td> $designation_courte </td>
                <td> $designation  </td>
                <td> $couleur </td>
                <td> $marque </td>
                <td><img src=\"$type_image\"></td>
                <td> $taille  </td> 
                <td> $prix  €</td>
            </tr>";

            $counter++;
            $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
        }

        $query = "SELECT SUM(prix) FROM pm_articles WHERE vente = :vente";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
        $stmt->execute();
        $total = $stmt->fetchColumn();

        if ($total !== false) {
            echo "<tr>
                <td colspan=7> TOTAL </td> 
                <td> $total €</td>
            </tr>";
        }

        echo "</table>";

    } catch (PDOException $e) {
        echo "Erreur lors de la finalisation de la vente : " . $e->getMessage();
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