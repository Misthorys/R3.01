<?php

// Démarrage de la session pour gérer les informations utilisateur
session_start();

// Inclusion des fichiers nécessaires pour la gestion de la base de données et des fonctions SQL
require_once 'database.php';
require_once 'librairie_sql.php';

// Vérification que l'utilisateur est connecté
// Si non connecté, redirection vers la page de connexion
if (!isset($_SESSION['utilisateur_connecte'])) {
    header('Location: login.php');
    exit;
}

// Récupération de l'ID du match depuis les paramètres GET
$id_match = $_GET['id_match'] ?? '';

// Vérification que l'ID du match est fourni
if (empty($id_match)) {
    die('ID du match manquant.');
}

// Création d'une instance pour gérer les notes des joueurs du match
$manager = new MatchNotesManager($linkpdo);

// Vérifier si le délai de modification des données est respecté
$match_date = $manager->obtenirDateMatch($id_match);
$editable = false; // Par défaut, les données ne sont pas éditables
if ($match_date) {
    $match_datetime = new DateTime($match_date); // Date et heure du match
    $now = new DateTime(); // Date et heure actuelles
    $interval = $now->diff($match_datetime);

    // Les données sont éditables si le délai depuis le match est inférieur ou égal à 7 jours
    if ($interval->days < 7 || ($interval->days == 7 && $interval->invert === 0)) {
        $editable = true;
    }
}

// Récupération des joueurs, leurs notes, commentaires et statistiques pour le match
$notesJoueurs = $manager->obtenirNotesJoueurs($id_match);

// Sauvegarder les données si le formulaire est soumis et que l'édition est autorisée
if ($editable && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = [];
    // Parcours des notes envoyées via le formulaire
    foreach ($_POST['notes'] as $numero_de_licence => $note) {
        $notes[$numero_de_licence] = [
            'note' => $note,
            'commentaire' => $_POST['commentaires'][$numero_de_licence] ?? '',
            'kills' => $_POST['kills'][$numero_de_licence] ?? 0,
            'deaths' => $_POST['deaths'][$numero_de_licence] ?? 0,
            'assists' => $_POST['assists'][$numero_de_licence] ?? 0,
            'statut' => $_POST['statut'][$numero_de_licence] ?? '',
        ];
    }
    // Mise à jour des notes et des statistiques dans la base de données
    $manager->mettreAJourNotesJoueurs($id_match, $notes);

    // Redirection pour éviter la resoumission des données
    header("Location: notesmatch.php?id_match=" . $id_match);
    exit;
}

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
    <title>Notes des Joueurs</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="../image/riot-games-logo.png" alt="Riot Games" class="logo">
        <img src="../image/valorant-logo.png" alt="Valorant" class="logo">
    </div>
    <nav class="navbar">
        <a href="matchs.php">Retour match</a>
        <a href="../index.php">Acceuil</a>
    </nav>
    <div class="header-right">
        <a href="page/logout.php"> <button class="play-button"> Se déconnecter</button></a>
    </div>
</header>
<main>
    <h2>Résultats des Joueurs</h2>
    <?php if ($editable): ?>
    <form method="POST">
        <?php endif; ?>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Nombre de Kill</th>
                <th>Nombre de Mort</th>
                <th>Nombre d'Assistance</th>
                <th>KD</th>
                <th>Poste</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($notesJoueurs as $joueur): ?>
                <?php
                $numero_de_licence = $joueur['Numéro_de_licence'];
                $kills = $joueur['Nombre_de_kill'] ?? 0;
                $deaths = $joueur['Nombre_de_mort'] ?? 0;
                $assists = $joueur['Nombre_d_assistance'] ?? 0;
                $kd = $deaths > 0 ? round($kills / $deaths, 2) : $kills;
                ?>
                <tr>
                    <td><?= htmlspecialchars($joueur['Nom']) ?></td>
                    <td><?= htmlspecialchars($joueur['Prénom']) ?></td>
                    <td>
                        <?php if ($editable): ?>
                            <input type="number" name="notes[<?= $numero_de_licence ?>]" value="<?= htmlspecialchars($joueur['Note'] ?? '') ?>" min="0" max="10">
                        <?php else: ?>
                            <?= htmlspecialchars($joueur['Note'] ?? 'Non défini') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($editable): ?>
                            <input type="text" name="commentaires[<?= $numero_de_licence ?>]" value="<?= htmlspecialchars($joueur['Commentaire'] ?? '') ?>">
                        <?php else: ?>
                            <?= htmlspecialchars($joueur['Commentaire'] ?? 'Aucun commentaire') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($editable): ?>
                            <input type="number" name="kills[<?= $numero_de_licence ?>]" value="<?= htmlspecialchars($kills) ?>" min="0">
                        <?php else: ?>
                            <?= htmlspecialchars($kills) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($editable): ?>
                            <input type="number" name="deaths[<?= $numero_de_licence ?>]" value="<?= htmlspecialchars($deaths) ?>" min="0">
                        <?php else: ?>
                            <?= htmlspecialchars($deaths) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($editable): ?>
                            <input type="number" name="assists[<?= $numero_de_licence ?>]" value="<?= htmlspecialchars($assists) ?>" min="0">
                        <?php else: ?>
                            <?= htmlspecialchars($assists) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($kd) ?></td>
                    <td><?= htmlspecialchars($joueur['Poste'] ?? 'Non défini') ?></td>
                    <td>
                        <?php if ($editable): ?>
                            <select name="statut[<?= $numero_de_licence ?>]">
                                <option value="Titulaire" <?= $joueur['Statut_titulaire_remplacant'] === 'Titulaire' ? 'selected' : '' ?>>Titulaire</option>
                                <option value="Remplaçant" <?= $joueur['Statut_titulaire_remplacant'] === 'Remplaçant' ? 'selected' : '' ?>>Remplaçant</option>
                                <option value="Entré pendant le match" <?= $joueur['Statut_titulaire_remplacant'] === 'Entré pendant le match' ? 'selected' : '' ?>>Entré pendant le match</option>
                            </select>
                        <?php else: ?>
                            <?= htmlspecialchars($joueur['Statut_titulaire_remplacant']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($editable): ?>
            <button type="submit">Enregistrer les Données</button>
        <?php endif; ?>
        <?php if ($editable): ?>
    </form>
<?php endif; ?>
</main>
<footer>
    <p>&copy; 2025 Valorant Match Manager</p>
</footer>
</body>
</html>
