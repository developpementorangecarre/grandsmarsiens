
<div id="sidebar">

<div id="logo">
			
<h1><a href="#"></a></h1>
			
<h2><a href="#"> </a></h2>

</div>

<div id="menu">
			
<ul>

  <li class="first"><a href="index.php">Accueil&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>

  <li><a href="personnes.php">Personnes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>

  <li><a href="ventes.php">Ventes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>

  <li><a href="articles.php">Dépot&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>

  <li><a href="retraits.php">Retrait&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>

</ul>

</div>

		
<div id="login">


<form method=GET action=liste_personne.php>
<h2 class="title1">Nom d'une personne</h2> 
<input size=20 type=text name=nom></td>
</tr>
<tr><td colspan=2>
</tr></td>
</table>
</form>
			
<h2 class="title1">
<table width=100%><tr><td width=80% align=right>Personne n° </td><td> <form action=liste_personne.php method=get><input type=text size=3 name=id></form></td></tr></table>
</h2>
<h2 class="title1">
<table width=100%><tr><td width=80% align=right>Dépot n° </td><td> <form action=depot_articles.php method=get><input type=hidden name=bourse_active value=OUI><input type=text size=3 name=reference_article></form></td></tr></table>
</h2>
<h2 class="title1">
<table width=100%><tr><td width=80% align=right>Retrait du dépôt n° </td><td> <form action=retrait_cocher_articles.php method=get><input type=hidden name=bourse_active value=OUI><input type=text size=3 name=reference_article></form></td></tr></table>
</h2>
</div>

</div>
