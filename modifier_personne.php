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
			

<?php
include("functions.inc.php");
include("conf.inc.php");

$connection=db_connect($host,$port,$db,$username,$password);

// On récupère les valeurs en toute sécurité
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$posted = filter_input(INPUT_POST, 'posted', FILTER_VALIDATE_INT);

// Si le formulaire a été soumis
if (isset($posted) && $posted == 1) {
    // Sécurisation des données
    $nom = text2sql($_POST['nom']);
    $prenom = text2sql($_POST['prenom']);
    $adresse = text2sql($_POST['adresse']);
    $ville = text2sql($_POST['ville']);
    $tel = text2sql($_POST['tel']);
    $email = text2sql($_POST['email']);
    $categorie = filter_input(INPUT_POST, 'categorie', FILTER_VALIDATE_INT);

    // Vérification des doublons
    $query = "
        SELECT COUNT(*) FROM pm_personnes 
        WHERE nom = :nom AND prenom = :prenom AND adresse = :adresse AND ville = :ville 
        AND email = :email AND tel = :tel
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':adresse' => $adresse,
        ':ville' => $ville,
        ':email' => $email,
        ':tel' => $tel
    ]);
    $compte = $stmt->fetchColumn();

    if ($compte == 0) {
        // Mise à jour des données de la personne
        $query = "
            UPDATE pm_personnes 
            SET nom = :nom, prenom = :prenom, adresse = :adresse, ville = :ville, tel = :tel, email = :email, categorie = :categorie
            WHERE id = :id
        ";
        $stmt = $connection->prepare($query);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':adresse' => $adresse,
            ':ville' => $ville,
            ':tel' => $tel,
            ':email' => $email,
            ':categorie' => $categorie,
            ':id' => $id
        ]);

        // Affichage des données mises à jour
        $query = "
            SELECT p.id, p.nom, p.prenom, p.adresse, p.ville, p.tel, p.email, c.categorie 
            FROM pm_personnes AS p
            JOIN pm_categories AS c ON p.categorie = c.id
            WHERE p.id = :id
        ";
        $stmt = $connection->prepare($query);
        $stmt->execute([':id' => $id]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<h3>Numéro {$ligne['id']}</h3>";
        echo "<h4><table class='sample'>
            <tr><td>NOM : {$ligne['nom']}</td><td>PRENOM : {$ligne['prenom']}</td></tr>
            <tr><td colspan='2'>ADRESSE : {$ligne['adresse']}</td></tr>
            <tr><td colspan='2'>VILLE : {$ligne['ville']}</td></tr>
            <tr><td colspan='2'>EMAIL : {$ligne['email']}</td></tr>
            <tr><td colspan='2'>TEL : {$ligne['tel']}</td></tr>
            <tr><td colspan='2'>CATEGORIE : {$ligne['categorie']}</td></tr>
        </table></h4>";

        echo "
        <form method='post' action='nouvelle_liste.php'>
            <input type='hidden' name='vendeur' value='{$ligne['id']}'>
            <center><input type='submit' name='envoyer' value='VENDEUR : DEPOT d\'ARTICLES'></center>
            <input type='hidden' name='posted' value='1'>
        </form>
        <form method='post' action='nouvelle_vente.php'>
            <input type='hidden' name='client' value='{$ligne['id']}'>
            <center><input type='submit' name='envoyer' value='CLIENT : NOUVELLE VENTE'></center>
            <input type='hidden' name='posted' value='1'>
        </form>
        <form method='get' action='modifier_personne.php'>
            <input type='hidden' name='id' value='{$ligne['id']}'>
            <center><input type='submit' name='envoyer' value='MODIFIER LES DONNEES'></center>
        </form>
        ";
    } else {
        echo "La personne existe déjà dans la base de données.";
    }
} else {
    // Récupération des données existantes de la personne
    $query = "
        SELECT id, nom, prenom, adresse, ville, tel, email, categorie 
        FROM pm_personnes 
        WHERE id = :id
    ";
    $stmt = $connection->prepare($query);
    $stmt->execute([':id' => $id]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

    // Formulaire de modification des données
    echo "
    <h1 class='title2'>Modifier les données d'une personne</h1>
    <form method='post' action='modifier_personne.php?id={$ligne['id']}'>
    <table>
        <tr><td>Nom : </td><td><input type='text' size='80' name='nom' value='{$ligne['nom']}'></td></tr>
        <tr><td>Prenom : </td><td><input type='text' size='80' name='prenom' value='{$ligne['prenom']}'></td></tr>
        <tr><td>Adresse : </td><td><textarea cols='63' rows='2' name='adresse'>{$ligne['adresse']}</textarea></td></tr>
        <tr><td>Ville : </td><td><input type='text' size='80' name='ville' value='{$ligne['ville']}'></td></tr>
        <tr><td>Téléphone : </td><td><input type='text' size='80' name='tel' value='{$ligne['tel']}'></td></tr>
        <tr><td>Email : </td><td><input type='text' size='80' name='email' value='{$ligne['email']}'></td></tr>
        <tr><td>Catégorie : </td><td><select name='categorie'>";

    // Récupération des catégories
    $query = "SELECT id, categorie FROM pm_categories ORDER BY id ASC";
    $stmt = $connection->query($query);
    while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($ligne['categorie'] == $cat['id']) ? "selected" : "";
        echo "<option value='{$cat['id']}' $selected>{$cat['categorie']}</option>";
    }

    echo "</select></td></tr>
    </table>
    <center><input type='submit' name='envoyer' value='ENVOYER'></center><br>
    <input type='hidden' name='posted' value='1'><br>
    </form>";
}

echo "<HR>";
?>

</div>
</div>
	
<div style="clear: both;">&nbsp;</div>
</div>

<?php include("footer.php"); ?>


</body>
</html>