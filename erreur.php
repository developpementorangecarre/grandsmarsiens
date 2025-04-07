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
			
<h1 class="title2">Bienvenue chez les p'tits marsiens !</h1>
			
<p><strong><br /></strong><em></em></p>



<?php
include("functions.inc.php");
include("conf.inc.php");

$msg = filter_input(INPUT_GET, 'message', FILTER_DEFAULT) ?? '';
$page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT) ?? '';

$connection = db_connect($host, $port, $db, $username, $password);

$query = "SELECT p.id, p.nom FROM pm_personnes AS p ORDER BY id DESC";
try {
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur SQL : " . htmlspecialchars($e->getMessage()));
}

echo "<h3>" . htmlspecialchars($msg) . "</h3>";

?>
</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>