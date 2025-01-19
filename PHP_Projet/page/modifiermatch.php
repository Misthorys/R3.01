<?php

session_start();
require_once 'database.php';
require_once 'librairie_sql.php';

if (!isset($_SESSION['utilisateur_connecte'])) {
    header('Location: login.php');
    exit;
}

$id_match = $_GET['id_match'] ?? '';

if (empty($id_match)) {
    die('ID du match manquant.');
}

$manager = new MatchModifier($linkpdo);

// Récupérer les informations du match
$match = $manager->obtenirMatchParId($id_match);

if (!$match) {
    die('Match introuvable.');
}

// Si le formulaire est soumis, mettre à jour les informations du match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_match'])) {
    $dateheure = $_POST['dateheure'] ?? '';
    $nom_equipe = $_POST['nom_equipe'] ?? '';
    $lieu = $_POST['lieu'] ?? '';
    $terrain = $_POST['terrain'] ?? '';

    // Vérifier que tous les champs sont remplis
    if (empty($dateheure) || empty($nom_equipe) || empty($lieu) || empty($terrain)) {
        $erreur = 'Tous les champs sont obligatoires.';
    }
    // Vérifier que la date n'est pas dans le passé
    elseif (strtotime($dateheure) < time()) {
        $erreur = 'La date et l’heure du match ne peuvent pas être dans le passé.';
    } else {
        $manager->mettreAJourMatch($id_match, $dateheure, $nom_equipe, $lieu, $terrain);
        header('Location: matchs.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
    <title>Modifier le Match</title>
</head>
<body>
<header>
    <h1>Modifier le Match</h1>
</header>
<main>
    <h2>Modifier les informations du Match ID : <?= htmlspecialchars($id_match) ?></h2>
    <form method="POST">
        <div>
            <label for="dateheure">Date et Heure :</label>
            <input type="datetime-local" id="dateheure" name="dateheure" value="<?= htmlspecialchars($match['Dateheure']) ?>" required>
        </div>
        <div>
            <label for="nom_equipe">Nom de l'équipe adverse :</label>
            <input type="text" id="nom_equipe" name="nom_equipe" value="<?= htmlspecialchars($match['Nom_équipe_ennemi']) ?>" required>
        </div>
        <div>
            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($match['Lieu_de_bataille']) ?>" required>
        </div>
        <div>
            <label for="terrain">Terrain :</label>
            <input type="text" id="terrain" name="terrain" value="<?= htmlspecialchars($match['Terrain']) ?>" required>
        </div>

        <?php if (isset($erreur)): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <button type="submit" name="modifier_match">Modifier le Match</button>
        <button type="button" onclick="location.href='matchs.php';">Annuler</button>
    </form>
</main>
<footer>
    <p>&copy; 2025 Valorant Match Manager</p>
</footer>
</body>
</html>
