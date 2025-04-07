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

$query="select email from pm_personnes 
		where email like '%@%' and id > 1320 order by id
		" ;

$result=exec_sql($query,$connection);	
while ($ligne=fetch_row($result))
{
		$email="$ligne[0]";
		echo "$email<br>
	";
	
	}





?>



</body>
</html>