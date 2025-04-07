<html>
<head>
<SCRIPT LANGUAGE="JavaScript">
function popUp(URL) 
{
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=300,left = 362,top = 234');");
}

</script>

<!---- <link href="printer.css" rel="stylesheet" type="text/css" /> !-->
<STYLE TYPE="text/css">
     P.breakhere {page-break-before: always}
</STYLE>
</head>

<body>

<?php

include("functions.inc.php");
include("conf.inc.php");
$connection=db_connect($host,$port,$db,$username,$password);

$liste_depots = "a.liste = 0";

$bar = $_POST['bar'] ?? [];

// Requête pour récupérer les articles
$query = "SELECT a.id, a.designation, a.liste, a.numero, a.prix, t.taille 
          FROM pm_articles AS a
          JOIN pm_tailles AS t ON a.taille = t.id
          WHERE $liste_depots";

try {
    $stmt = $connection->prepare($query);
    $stmt->execute();
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

//---------------------- Styles et variables d'affichage
$hauteur_init = 96;
$hauteur_last = 92;
$hauteur = $hauteur_init;
$espace = 0;
$padding = 0;
$nombre_planche = 40;

$table_style = "<table border='0' width='100%' align='center' cellspacing='$espace' cellpadding='$padding' style='border-collapse: collapse'>";
echo "$table_style<tr>";

$counter = 0;
$saut_page = 0;

while ($counter < 40) {
    $reference_article = $bar[$counter] ?? '';

    // Séparation de la liste et du numéro
    $parts = preg_split('/[Aa\/\.-]/', $reference_article);
    $liste = $parts[0] ?? null;
    $numero = $parts[1] ?? null;

    if (!$liste || !$numero) {
        echo "<td align='center' width='25%' height='$hauteur' valign='top'><br></td>";
        $counter++;
        continue;
    }

    // Vérification de l'existence de l'article
    try {
        $stmt_check = $connection->prepare("SELECT COUNT(*) AS compte FROM pm_articles WHERE liste = :liste AND numero = :numero");
        $stmt_check->bindParam(':liste', $liste, PDO::PARAM_INT);
        $stmt_check->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt_check->execute();
        $row_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $ok = $row_check['compte'] ?? 0;
    } catch (PDOException $e) {
        die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    if ($counter % 4 == 0) {
        echo '</tr><tr>';
    }

    if ($ok == 1) {
        try {
            $stmt_article = $connection->prepare("
                SELECT a.id, a.designation, a.liste, a.numero, a.prix, t.taille,
                       ty.type AS type, l.vendeur AS vendeur, d.libelle AS designation_courte, 
                       c.libelle AS couleur, m.libelle AS marque  
                FROM pm_articles AS a
                JOIN pm_types AS ty ON a.type = ty.id
                JOIN pm_tailles AS t ON a.taille = t.id
                JOIN pm_designations_courtes AS d ON a.designation_courte = d.id
                JOIN pm_couleurs AS c ON a.couleur = c.id
                JOIN pm_marques AS m ON a.marque = m.id
                JOIN pm_liste_articles AS l ON l.id = a.liste
                WHERE a.liste = :liste AND a.numero = :numero
            ");
            $stmt_article->bindParam(':liste', $liste, PDO::PARAM_INT);
            $stmt_article->bindParam(':numero', $numero, PDO::PARAM_INT);
            $stmt_article->execute();

            if ($row = $stmt_article->fetch(PDO::FETCH_ASSOC)) {
                $designation = htmlspecialchars($row['designation']);
                $liste = htmlspecialchars($row['liste']);
                $vendeur = htmlspecialchars($row['vendeur']);
                $numero = htmlspecialchars($row['numero']);
                $prix = htmlspecialchars($row['prix']);
                $taille = htmlspecialchars($row['taille']);
                $designation_courte = htmlspecialchars($row['designation_courte']);
                $couleur = htmlspecialchars($row['couleur']);
                $marque = htmlspecialchars($row['marque']);

                echo "<td align='center' width='25%' height='$hauteur' valign='top'>
                        <table border='0' width='100%'>
                            <tr>
                                <td colspan='2' align='center'><font size='2'>$designation_courte $couleur<br>$marque</font></td>
                            </tr>
                            <tr>
                                <td colspan='2' align='center'><font size='3'>Taille : $taille</font></td>
                            </tr>
                            <tr>
                                <td align='center'><b><font size='3'>$liste - $numero</font></b></td>
                                <td align='center'><font size='5'><b>$prix €</b></font></td>
                            </tr>
                        </table>
                      </td>";
            }
        } catch (PDOException $e) {
            die("Erreur SQL : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    } else {
        echo "<td align='center' width='25%' height='$hauteur' valign='top'><br></td>";
    }

    $counter++;
    $saut_page++;

    if ($counter % 36 == 0) {
        echo '</tr><tr>';
    }

    if ($saut_page >= 36) {
        $hauteur = $hauteur_last;
    }

    if ($saut_page % $nombre_planche == 0) {
        echo "</tr></table>";
        echo "<p style=\"page-break-before: always\"></p>";
        echo "$table_style<tr>";
        $saut_page = 0;
        $hauteur = $hauteur_init;
    }
}

echo '</tr></table>';

//phpinfo();

?>
<SCRIPT Language="Javascript">

/*
This script is written by Eric (Webcrawl@usa.net)
For full source code, installation instructions,
100's more DHTML scripts, and Terms Of
Use, visit dynamicdrive.com
*/

function printit(){  
if (window.print) {
    window.print() ;  
} else {
    var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
document.body.insertAdjacentHTML('beforeEnd', WebBrowser);
    WebBrowser1.ExecWB(6, 2);//Use a 1 vs. a 2 for a prompting dialog box    WebBrowser1.outerHTML = "";  
}
}
</script>
<SCRIPT Language="Javascript">  
/*var NS = (navigator.appName == "Netscape");
var VERSION = parseInt(navigator.appVersion);
if (VERSION > 3) {
    document.write('<form><input type=button value="Print" name="Print" onClick="printit()"></form>');        
}
*/
</script>

</body>
</html> 