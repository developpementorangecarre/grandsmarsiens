<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 
  <meta http-equiv="content-type" content="text/html; " />
  <title>Les p'tits marsiens...</title>
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  
  <link href="printer.css" rel="stylesheet" type="text/css" />
</head>


<body>

<?php
include("functions.inc.php");
include("conf.inc.php");

$vente = $_GET['vente'] ?? null;
if ($vente) {
    try {
        $connection = db_connect($host, $port, $db, $username, $password);

        // Récupérer les informations de vente et du client
        $query = "SELECT p.prenom, p.nom, p.id, v.date, v.id AS vente 
                  FROM pm_ventes AS v, pm_personnes AS p 
                  WHERE v.id = :vente AND v.client = p.id";

        $stmt = $connection->prepare($query);
        $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
        $stmt->execute();
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ligne) {
            $nom_client = htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom']);
            $id = $ligne['id'];
            $date = $ligne['date'];
            $vente = $ligne['vente'];

            echo "
                <table>
                <tr>
                    <td><h2>Vente N° $vente</h2></td>
                    <td><h3>$nom_client, le $date</h3></td>
                </tr>
                </table>";

            // Affichage des articles de la vente
            $counter = 0;
            $bgcolor = "#FFFFFF";
            echo "<table class='sample'>
                    <tr bgcolor='#FFFFFF'>
                        <td>Référence</td>
                        <td>Désignation courte</td>
                        <td>Désignation</td>
                        <td>Couleur</td>
                        <td>Marque</td>
                        <td>Type</td>
                        <td>Taille</td>
                        <td>Prix</td>
                    </tr>";

            $query = "SELECT a.id AS id, a.numero AS numero, a.designation AS designation, a.prix AS prix, 
                             a.prix_vendeur AS prix_vendeur, a.liste AS liste, l.bourse AS bourse, 
                             l.code AS code, ty.type AS type, ty.image AS type_image, ta.taille AS taille, 
                             d.libelle AS designation_courte, c.libelle AS couleur, m.libelle AS marque  
                      FROM pm_articles AS a
                      LEFT JOIN pm_types AS ty ON a.type = ty.id
                      LEFT JOIN pm_tailles AS ta ON a.taille = ta.id
                      LEFT JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
                      LEFT JOIN pm_couleurs AS c ON a.couleur = c.id
                      LEFT JOIN pm_marques AS m ON a.marque = m.id
                      LEFT JOIN pm_liste_articles AS l ON a.liste = l.id 
                      WHERE a.vente = :vente
                      ORDER BY liste, numero";

            $stmt = $connection->prepare($query);
            $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
            $stmt->execute();

            while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $numero = $ligne['numero'];
                $liste = $ligne['liste'];
                $designation_text = text2js($ligne['designation']);
                $designation = htmlspecialchars($ligne['designation']);
                $type = $ligne['type'];
                $type_image = $ligne['type_image'];
                $taille = $ligne['taille'];
                $prix = $ligne['prix'];
                $id = $ligne['id'];
                $designation_courte = $ligne['designation_courte'];
                $couleur = $ligne['couleur'];
                $marque = $ligne['marque'];
                $code = $ligne['code'];
                $bourse = $ligne['bourse'];
                $message = "Voulez-vous supprimer $liste-$numero $designation ?";

                echo "<tr bgcolor='$bgcolor'>
                        <td>$code $bourse-$numero</td>
                        <td>$designation_courte</td>
                        <td>$designation</td>
                        <td>$couleur</td>
                        <td>$marque</td>
                        <td><img src='$type_image' alt='$type'></td>
                        <td>$taille</td>
                        <td>$prix €</td>
                      </tr>";

                $counter++;
                $bgcolor = ($counter % 2 == 0) ? "#FFFFFF" : "#EEEEEE";
            }

            // Calcul du total
            $query = "SELECT SUM(prix) AS total_prix, COUNT(*) AS total_articles 
                      FROM pm_articles WHERE vente = :vente";

            $stmt = $connection->prepare($query);
            $stmt->bindParam(':vente', $vente, PDO::PARAM_INT);
            $stmt->execute();
            $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ligne) {
                echo "<tr>
                        <td colspan='7'>TOTAL</td>
                        <td>{$ligne['total_prix']} €</td>
                      </tr>";
                echo "<tr>
                        <td colspan='6'></td>
                        <td colspan='2'>{$ligne['total_articles']} articles</td>
                      </tr>";
            }

            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Aucune vente spécifiée.";
}

?>



</body>
</html>