<?php

function ouvre_page($page)
{

	printf("		<script language=\"JavaScript\">
			document.location.replace(\"%s\");
			</script>",$page);

}

function echo_toto()
{
	echo "TOTO include fonction <br>";
}

function db_connect($host, $port, $db, $username, $password)
{
    try {
        // Connexion avec PDO, en ajoutant le port
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8";
        $pdo = new PDO($dsn, $username, $password);

        // Configurer PDO pour lancer des exceptions en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        die("Échec de la connexion à la base de données : " . $e->getMessage());
    }
}


function exec_sql($sql, $connection)
{
    try {
        // Exécuter la requête
        $res = $connection->query($sql);
        return $res;
    } catch (PDOException $e) {
        die("Erreur lors de l'exécution de la requête : " . $e->getMessage());
    }
}

function fetch_row($stmt)
{
    if (!$stmt) {
        return false;
    }

    // Récupérer une ligne de résultat sous forme de tableau associatif
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retourner la ligne ou false si aucune ligne n'est trouvée
    return $row ?: false;
}

function text2sql($texte,$slash=1)
{
	if ($slash==1):
		$format_text=str_replace("<","&lt;",$texte);
		$format_text=str_replace(">","&gt;",$format_text);

		return addslashes($format_text);
	else:
		$format_text=str_replace("<","&lt;",$texte);
		$format_text=str_replace(">","&gt;",$format_text);
		return $format_text;
	endif;
}

function text2js($texte,$slash=1)
{
	if ($slash==1):
		$format_text=str_replace("<","&lt;",$texte);
		$format_text=str_replace(">","&gt;",$format_text);
		$format_text=str_replace("'"," ",$format_text);

		return addslashes($format_text);
	else:
		$format_text=str_replace("<","&lt;",$texte);
		$format_text=str_replace(">","&gt;",$format_text);
		return $format_text;
	endif;
}


function tab_vente($pdo) 
{
    $bgcolor = "#FFFFBB"; 

    // Calcul des dates pour le mois précédent et suivant
    $lastmonth = new DateTime('first day of last month');
    $nextmonth = new DateTime('first day of next month');
    $mysqllastmonth = $lastmonth->format('Y-m-d H:i:s');
    $mysqlnextmonth = $nextmonth->format('Y-m-d H:i:s');

    // Affichage de l'en-tête du tableau
    echo "<table class='sample'> 
            <tr bgcolor='#FFFFFF'> 
                <td><b>État</b></td> 
                <td><b>Nombre</b></td> 
                <td><b>Prix</b></td> 
                <td><b>Net Vendeur</b></td> 
            </tr>"; 

    // Requête principale
    $query = "SELECT 
                    COUNT(a.id) AS compte, 
                    e.etat AS etat, 
                    SUM(a.prix) AS prix, 
                    SUM(a.prix_vendeur) AS prix_vendeur 
              FROM pm_articles AS a 
              JOIN pm_etat_articles AS e ON e.id = a.etat 
              JOIN pm_ventes AS v ON v.id = a.vente 
              WHERE a.etat = :etat 
                AND v.date > :start_date 
                AND v.date < :end_date 
              GROUP BY a.etat"; 

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'etat' => 2, 
        'start_date' => $mysqllastmonth, 
        'end_date' => $mysqlnextmonth
    ]);

    $counter = 0; // Initialisation du compteur

    // Boucle sur les résultats
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) 
    { 
		// Protection XSS : échappement des données avant affichage
        $compte = htmlspecialchars($ligne['compte'], ENT_QUOTES, 'UTF-8'); 
    	$etat = htmlspecialchars($ligne['etat'], ENT_QUOTES, 'UTF-8'); 
    	$prix = htmlspecialchars(number_format($ligne['prix'], 2, ',', ' '), ENT_QUOTES, 'UTF-8'); 
    	$prix_vendeur = htmlspecialchars(number_format($ligne['prix_vendeur'], 2, ',', ' '), ENT_QUOTES, 'UTF-8');

        echo "<tr bgcolor='$bgcolor'> 
                <td>$etat</td> 
                <td>$compte</td> 
                <td>$prix €</td> 
                <td>$prix_vendeur €</td> 
              </tr>"; 

        $counter++; 
        $bgcolor = ($counter % 2 == 0) ? "#FFFFBB" : "#FFFFFF"; 
    }

    echo "</table>"; 
}



function tab_stock($pdo)
{
    $bgcolor = "#FFFFBB"; 
    echo "<table class='sample'> 
            <tr bgcolor='#FFFFFF'> 
                <td><b>État</b></td> 
                <td><b>Nombre</b></td> 
                <td><b>Prix</b></td> 
                <td><b>Net Vendeur</b></td> 
            </tr>"; 
    
    // Première requête
    $query = "SELECT 
                    COUNT(a.id) AS compte, 
                    e.etat AS etat, 
                    SUM(a.prix) AS prix, 
                    SUM(a.prix_vendeur) AS prix_vendeur 
              FROM pm_articles AS a 
              JOIN pm_etat_articles AS e 
              ON e.id = a.etat 
              WHERE a.etat = :etat 
              GROUP BY a.etat"; 
    
    $stmt = $pdo->prepare($query); 
    $stmt->execute(['etat' => 1]); 

    $counter = 0;
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) 
    { 
        // Protection XSS : échappement des données avant affichage
        $etat = htmlspecialchars($ligne['etat'], ENT_QUOTES, 'UTF-8');
        $compte = htmlspecialchars($ligne['compte'], ENT_QUOTES, 'UTF-8');
        $prix = htmlspecialchars($ligne['prix'], ENT_QUOTES, 'UTF-8');
        $prix_vendeur = htmlspecialchars($ligne['prix_vendeur'], ENT_QUOTES, 'UTF-8');

        
        echo "<tr bgcolor='$bgcolor'> 
                <td>$etat</td> 
                <td>$compte</td> 
                <td>$prix €</td> 
                <td>$prix_vendeur €</td> 
              </tr>"; 
        
        $counter++; 
        $bgcolor = ($counter % 2 == 0) ? "#FFFFBB" : "#FFFFFF"; 
    } 

    echo "</table>"; 
}





function tab_stock_par_taille($connection)
{
$bgcolor="#FFFFBB"; 
echo "<table class=sample> <tr bgcolor=#FFFFFF> <td><b> Taille </b></td> <td><b> Nombre </b></td> </tr>"; 
$query="select count(*) as compte ,pm_tailles.taille  as taille
		from pm_articles,pm_tailles 
		where pm_articles.etat=1 
				and pm_articles.type=1 
				and pm_articles.taille=pm_tailles.id 
				group by pm_tailles.id;"; 
$result=exec_sql($query,$connection); 
while ($ligne=fetch_row($result)) 
	{ 
	$compte=$ligne['compte']; 
	$taille=$ligne['taille']; 

	echo "<tr bgcolor=$bgcolor> <td> $taille </td> <td> $compte </td>  </tr>"; 
	$counter++; 
	if (($counter%2) == 0): 
		$bgcolor="#FFFFBB"; 
		else: $bgcolor="#FFFFFF"; 
	endif; } 
	
	echo "</table>"; 
}


echo "
<script language=\"Javascript\">
<!--
function toggleDiv(id,flagit) {
if (flagit==\"1\"){
if (document.layers) document.layers[''+id+''].visibility = \"show\"
else if (document.all) document.all[''+id+''].style.visibility = \"visible\"
else if (document.getElementById) document.getElementById(''+id+'').style.visibility = \"visible\"
}
else
if (flagit==\"0\"){
if (document.layers) document.layers[''+id+''].visibility = \"hide\"
else if (document.all) document.all[''+id+''].style.visibility = \"hidden\"
else if (document.getElementById) document.getElementById(''+id+'').style.visibility = \"hidden\"
}
}
//-->
</script>
";


function tab_stock_par_taille_type($pdo)
{
    $bgcolor = "#FFFFBB"; 
    echo "<table class='sample'> 
            <tr bgcolor='#FFFFFF'> 
                <td><b>Taille</b></td>";

    // Récupération des images des types
    $query = "SELECT image FROM pm_types"; 
    $stmt = $pdo->query($query); 

    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) 
    { 
        $image = htmlspecialchars($ligne['image'], ENT_QUOTES, 'UTF-8'); // Protection XSS
        echo "<td><img src='$image'></td>";
    }
    
    echo "<td>Total</td></tr>"; 

    // Récupération des tailles et comptes associés
    $query = "SELECT 
                COUNT(*) AS compte, 
                pm_tailles.taille AS taille, 
                pm_articles.taille AS tailleid 
              FROM pm_articles 
              JOIN pm_tailles 
              ON pm_articles.taille = pm_tailles.id 
              WHERE pm_articles.etat = :etat 
              GROUP BY pm_tailles.id"; 
    $stmt = $pdo->prepare($query); 
    $stmt->execute(['etat' => 1]); 

    $counter = 0; // Initialisation du compteur
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) 
    { 
        $compte = $ligne['compte']; 
        $taille = htmlspecialchars($ligne['taille'], ENT_QUOTES, 'UTF-8'); 
        $tailleid = (int)$ligne['tailleid']; // Conversion sécurisée en entier

        echo "<tr bgcolor='$bgcolor'> 
                <td>$taille</td>";
        
        // Récupération des types
        $querytype = "SELECT id AS type FROM pm_types"; 
        $stmtType = $pdo->query($querytype); 
        
        while ($lignetype = $stmtType->fetch(PDO::FETCH_ASSOC)) 
        { 
            $type = (int)$lignetype['type']; // Conversion sécurisée en entier

            // Comptage par type et taille
            $querysubtotal = "SELECT 
                                COUNT(*) AS compte 
                              FROM pm_articles 
                              WHERE pm_articles.etat = :etat 
                                AND pm_articles.type = :type 
                                AND pm_articles.taille = :taille"; 
            $stmtSubtotal = $pdo->prepare($querysubtotal); 
            $stmtSubtotal->execute([
                'etat' => 1, 
                'type' => $type, 
                'taille' => $tailleid
            ]);

            $lignesubtotal = $stmtSubtotal->fetch(PDO::FETCH_ASSOC);
            $subtotal = $lignesubtotal['compte'] ?? 0; // Valeur par défaut si NULL
            echo "<td>$subtotal</td>";
        }
        
        echo "<td>$compte</td></tr>"; 

        $counter++; 
        $bgcolor = ($counter % 2 == 0) ? "#FFFFBB" : "#FFFFFF"; 
    }

    echo "</table>"; 
}




function creer_liste($vendeur, $bourse, $connection)
{
    try {
        // Récupération du code maximum pour la bourse donnée
        $stmt = $connection->prepare("SELECT MAX(code) AS max_code FROM pm_liste_articles WHERE bourse = :bourse");
        $stmt->execute([':bourse' => $bourse]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $code = ($row && $row['max_code'] !== null) ? (int)$row['max_code'] + 1 : 1;

        // Insertion de la nouvelle liste dans la table
        // Correction : utilisation de "etat" au lieu de "statut"
        $stmt = $connection->prepare("
            INSERT INTO pm_liste_articles (vendeur, etat, bourse, code)
            VALUES (:vendeur, 0, :bourse, :code)
        ");
        $stmt->execute([
            ':vendeur' => $vendeur,
            ':bourse'  => $bourse,
            ':code'    => $code
        ]);

        // Retourner l'ID de la liste nouvellement créée
        return (int)$connection->lastInsertId();
    } catch (PDOException $e) {
        throw new Exception("Erreur dans creer_liste : " . $e->getMessage());
    }
}



function codeetbourse2liste($code, $bourse, $connection)
{
    $query = "SELECT id FROM pm_liste_articles WHERE code = :code AND bourse = :bourse";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':bourse', $bourse, PDO::PARAM_STR);
    $stmt->execute();
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ligne ? $ligne['id'] : null;
}

function code2liste($code, $connection)
{
    $query_bourse = "SELECT id FROM pm_bourses WHERE statut = 1";
    $stmt_bourse = $connection->prepare($query_bourse);
    $stmt_bourse->execute();
    $ligne_bourse = $stmt_bourse->fetch(PDO::FETCH_ASSOC);
    $bourse = $ligne_bourse ? $ligne_bourse['id'] : null;

    $query = "SELECT id FROM pm_liste_articles WHERE code = :code AND bourse = :bourse";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':bourse', $bourse, PDO::PARAM_INT);
    $stmt->execute();
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ligne ? $ligne['id'] : null;
}

function liste2code($liste, $connection)
{
    $query = "SELECT bourse, code FROM pm_liste_articles WHERE id = :liste";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
    return $ligne ? $ligne['code'] : null;
}


function liste2bourse($liste, $connection)
{
    $bourse = null; 
    $query = "SELECT bourse, code FROM pm_liste_articles WHERE id = :liste";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':liste', $liste, PDO::PARAM_INT);
    $stmt->execute();

    if ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bourse = $ligne['bourse'];
    }

    return $bourse;
}



function getbourse($connection)
{
    try {
        $query = "SELECT id FROM pm_bourses WHERE statut = 1 LIMIT 1";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['id'] : null;
    } catch (PDOException $e) {
        die("Erreur SQL dans getbourse : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

function getval($parametre,$connection)
{
    try {
        $query = "SELECT valeur FROM pm_parametres WHERE parametre = :parametre LIMIT 1";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':parametre', $parametre, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['valeur'] : null;
    } catch (PDOException $e) {
        die("Erreur SQL dans getval : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}


?>