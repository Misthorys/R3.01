<?php
session_start();
require_once 'database.php'; // Connexion à la base de données
require_once 'UserManager.php'; // Inclusion de la classe UserManager

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et validation des données
    $nom_utilisateur = filter_var($_POST['nom_utilisateur'], FILTER_SANITIZE_STRING);
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'];

    // Gestion des erreurs
    $erreurs = [];
    if (empty($nom_utilisateur) || empty($mot_de_passe) || empty($mot_de_passe_confirm)) {
        $erreurs[] = "Tous les champs sont obligatoires.";
    } elseif ($mot_de_passe !== $mot_de_passe_confirm) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mot_de_passe) < 8) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match('/[\W]/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins un caractère spécial.";
    } else {
        // Vérification et insertion dans la base
        $userManager = new UserManager($linkpdo);

        if ($userManager->utilisateurExiste($nom_utilisateur)) {
            $erreurs[] = "Ce nom d'utilisateur est déjà utilisé.";
        } else {
            // Hashage du mot de passe et création de l'utilisateur
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $userManager->creerUtilisateur($nom_utilisateur, $mot_de_passe_hash);

            $_SESSION['success_message'] = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="../css/LoginValo.css">
</head>
<body class="login-page">
<div class="login-container">
    <div class="login-header">
        <h1 class="login-title">Créer un compte</h1>
    </div>

    <form class="login-form" action="register.php" method="POST">
        <div class="input-group">
            <label for="nom_utilisateur">Nom d'utilisateur:</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>
        </div>
        <div class="input-group">
            <label for="mot_de_passe">Mot de passe:</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>
        <div class="input-group">
            <label for="mot_de_passe_confirm">Confirmez le mot de passe:</label>
            <input type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required>
        </div>
        <button type="submit" class="login-button">Créer un compte</button>

        <?php if (!empty($erreurs)): ?>
            <div class="erreurs">
                <?php foreach ($erreurs as $erreur): ?>
                    <p class="erreur">&bull; <?= htmlspecialchars($erreur) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </form>

    <div class="login-footer">
        <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</div>
</body>
</html>
