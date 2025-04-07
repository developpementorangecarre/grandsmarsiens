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


$query="select a.id,a.designation,a.liste,a.numero,a.prix,t.taille from pm_articles as a,pm_tailles as t where a.taille=t.id AND($liste_depots)";
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
while($counter<40)
{
  $reference_article=$bar[$counter];
  list($liste, $numero)=split('[Aa/.-]', $reference_article);
  $query="select count(*) as compte from pm_articles where liste=$liste and numero=$numero";
  $result=exec_sql($query,$connection);	
  $ok=0;
  while ($ligne=fetch_row($result))
  {
    $ok=$ligne['compte'];
  }
  
  

	 if($counter%4==0)
	 {
	   	echo '</tr><tr>';
    }

  if ($ok == 1):
  {
    $query2="
select a.id,a.designation,a.liste,a.numero,a.prix,t.taille,
  ty.type as type, 
  l.vendeur as vendeur,
	d.libelle as designation_courte, 
  c.libelle as couleur,
  m.libelle as marque  
from 
  pm_articles as a, pm_types as ty, pm_tailles as t, pm_designations_courtes as d, pm_couleurs as c, pm_marques as m, pm_liste_articles as l
 	where a.liste=$liste and a.numero=$numero and t.id=a.taille
	 and a.type=ty.id and a.designation_courte=d.id and a.couleur=c.id and a.marque=m.id
and l.id=a.liste"
;
	
	
	//echo $query2;
    $result2=exec_sql($query2,$connection);	
    while ($row=fetch_row($result2))
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


	echo "<td align='center' width=25% height=$hauteur valign=top>
      <table border=0 width=100%>
	  <tr>
	  <td colspan=2 align=center><font size=2>$designation_courte $couleur<br>$marque<td></font>
	  </tr>
	  <tr>
      <td colspan=2 align=center><font size=3>Taille : $taille </td>
	  </tr>
	  <tr>
	  <td align=center>
	  <b><font size=3 >$liste - $numero</font></b>
	  </td>
	  <td align=center>
      </font><font size=5><b> $prix €  </b></font>
	  </td>
	  </tr>
	  </table>
      </td>";
    }
  

  }
  else:
  {
  	echo "<td align='center' width=25% height=$hauteur valign=top>
      <br>
      </td>";
  }
  endif;
	
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