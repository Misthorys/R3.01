<?php
session_start();
require_once 'database.php'; // Connexion & configuration de la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_utilisateur = $_POST['nom_utilisateur'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // Requête pour vérifier les informations de connexion
    $stmt = $linkpdo->prepare('SELECT * FROM Utilisateur WHERE nom_utilisateur = ?');
    $stmt->execute([$nom_utilisateur]);
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify( $utilisateur['Mot_de_passe'], password_hash($utilisateur['Mot_de_passe'], PASSWORD_DEFAULT))) {
        $_SESSION['utilisateur_connecte'] = $nom_utilisateur;
        header('Location: ../index.php');
        exit;
    } else {
        $erreur = "Nom d'utilisateur ou mot de passe incorrect";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/LoginValo.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-header">
        <h1 class="login-title">Connexion &agrave; l'application de gestion sportive</h1>
    </div>

    <form class="login-form" action="login.php" method="POST">
        <div class="input-group">
            <label for="nom_utilisateur">Nom d'utilisateur:</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>
        </div>
        <div class="input-group">
            <label for="mot_de_passe">Mot de passe:</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <button type="submit" class="login-button">Se connecter</button>
        <?php if (!empty($erreur)): ?>
            <p class="erreur">&bull; <?=$erreur?></p>
        <?php endif; ?>
    </form>

    <div class="login-footer">
        <p>Pas encore inscrit ? <a href="register.php">Créez un compte</a></p>
    </div>
</div>
</body>
</html>
