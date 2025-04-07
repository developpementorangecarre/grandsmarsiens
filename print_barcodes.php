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
$connection=db_connect($host,$db,$username,$password);

$liste_depots="a.liste=0";

$bar=$_POST['bar'];
foreach ($bar as $value)
{
  $liste_depots="$liste_depots OR a.liste=$value";
}




$query="
select a.id,a.designation,a.liste,a.numero,a.prix,t.taille,
  ty.type as type, 
  l.vendeur as vendeur,
	d.libelle as designation_courte, 
  c.libelle as couleur,
  m.libelle as marque  
from 
  pm_articles as a, pm_types as ty, pm_tailles as t, pm_designations_courtes as d, pm_couleurs as c, pm_marques as m, pm_liste_articles as l
 
where a.taille=t.id AND($liste_depots) and a.type=ty.id and a.designation_courte=d.id and a.couleur=c.id and a.marque=m.id
and l.id=a.liste

order by a.liste, a.numero

";
//echo $query;
$result=exec_sql($query,$connection);	

//----------------------


$hauteur_init=96;
$hauteur_last=92;
$hauteur=$hauteur_init;
$espace=0;
$padding=0;
$nombre_planche=40;

$table_style="<table border=0 width=100% align=center cellspacing=$espace cellpadding=$padding style=\"border-collapse: collapse\">";
//-------------
echo "$table_style
<tr>";

$counter=0;
$page_saut=0;
$separateur='a';
while($row=fetch_row($result))
{
  $designation=$row['designation'];
  $liste=$row['liste'];
  $vendeur=$row['vendeur'];
  $numero=$row['numero'];
  $prix=$row['prix'];
  $taille=$row['taille'];
  $designation_courte=$row['designation_courte'];
	$couleur=$row['couleur'];
	$marque=$row['marque'];
	
  if($counter%4==0)
	{
		echo '</tr><tr>';
	}

	//echo "<td align='center'><img src='../classes/barcode.php?barcode=$row[$generateWith]&width=206&height=75&text=*$row[id]* ( $row[supplier_id])'><br> $row[item_name] <br> Taille : $taille <b>Prix : $row[total_cost] €</b></td>";
	$bourse=liste2bourse($liste,$connection);
	$code=liste2code($liste,$connection);

	echo "<td align='center' width=25% height=$hauteur valign=top>
      <table border=0 width=100%>
	  <tr>
	  <td colspan=2 align=center><font size=2>$designation_courte $couleur /$taille <br>$marque<td></font>
	  </tr>
	  <tr>
      <td colspan=2 align=center><font size=3>$code $bourse $numero</font></td>
	  </tr>
	  <tr>
	  <td align=center>
	  <b></b>
	  </td>
	  <td align=center>
      </font><font size=5><b> $prix €  </b></font>
	  </td>
	  </tr>
	  </table>
      </td>";

	
	$counter++;
	$saut_page++;
	if($counter%36==0)
	{
		echo '</tr><tr>';
	}

  if($saut_page>=36)
  {
    $hauteur=$hauteur_last;
  }

  if($saut_page%$nombre_planche==0)
	{
		echo "</tr></table>";
		
		echo "<P style=\"page-break-before: always\">";
		echo "$table_style<tr>";
		$saut_page=0;
		$hauteur=$hauteur_init;
	}
	
}

echo '</tr></table>';


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