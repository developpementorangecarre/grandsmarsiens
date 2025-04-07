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

$connection=db_connect($host,$port,$db,$username,$password);

try {
    $query = "SELECT email, prenom, nom FROM pm_personnes WHERE email LIKE :email ORDER BY email";
    $stmt = $connection->prepare($query);
    $stmt->execute([':email' => '%@%']);

    echo "<table border=1>";
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $email = $ligne['email'];
        $prenom = $ligne['prenom'];
        $nom = $ligne['nom'];
        echo "<tr><td>" . htmlspecialchars($email) . "</td><td>" . htmlspecialchars($prenom) . "</td><td>" . htmlspecialchars($nom) . "</td></tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    error_log("Erreur SQL : " . $e->getMessage());
    echo "<p>Une erreur est survenue lors de l'exécution de la requête.</p>";
}

?>



</body>
</html>