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

// Vérification que le numéro de licence est fourni via GET
if (!isset($_GET['numero_licence'])) {
    echo "Numéro de licence non spécifié.";
    exit;
}

// Récupération du numéro de licence depuis les paramètres GET
$numero_licence = $_GET['numero_licence'];

// Création d'une instance pour gérer les détails du joueur
$manager = new JoueurDetailsManager($linkpdo);

// Récupération des informations du joueur à partir de son numéro de licence
$joueur = $manager->obtenirInformationsJoueur($numero_licence);

// Vérification si le joueur existe, sinon affichage d'un message d'erreur
if (!$joueur) {
    echo "Joueur non trouvé.";
    exit;
}


// Vérification si le joueur a déjà participé à des matchs
$joueurAParticipe = $manager->verifierParticipation($numero_licence);

// Récupération des statistiques si le joueur a participé à des matchs
$varStatsGlobales = $joueurAParticipe ? $manager->obtenirStatsVDN($numero_licence) : null;
$varStatutMoyenneSelectPr100 = $joueurAParticipe ? $manager->obtenirStatsAvancees($numero_licence) : null;
$varTitularisations = $joueurAParticipe ? $manager->obtenirTitularisations($numero_licence) : 0;
$varTitularisationsConsecutives = $joueurAParticipe ? $manager->obtenirTitularisationsConsecutives($numero_licence) : 0;

// Gestion du formulaire d'ajout de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération du commentaire envoyé via le formulaire
    $nouveau_commentaire = $_POST['commentaire_coach'] ?? '';
    if (!empty($nouveau_commentaire)) {
        // Enregistrement du commentaire dans la base de données
        $id_note = $manager->enregistrerCommentaire($nouveau_commentaire);

        // Association du commentaire au joueur via son numéro de licence
        $manager->associerCommentaire($numero_licence, $id_note);

        // Notification visuelle de l'ajout réussi
        echo "<script>alert('Commentaire ajouté avec succès.');</script>";
    }
}

// Récupération des commentaires existants pour le joueur
$listeCommentaires = $manager->recupererCommentaires($numero_licence);

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques de <?= htmlspecialchars($joueur['Nom']) ?></title>
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
</head>
<body>
<header>
    <div class="header-left">
        <img src="../image/riot-games-logo.png" alt="Riot Games" class="logo">
        <img src="../image/valorant-logo.png" alt="Valorant" class="logo">
    </div>
    <nav class="navbar">
        <a href="joueurs.php">Gestion des joueurs</a>
        <a href="matchs.php">Gestion des matchs</a>
        <a href="statistiquesMatch.php">Statistiques des matchs</a>
        <a href="../index.php">Accueil</a>
    </nav>
    <div class="header-right">
        <a href="page/logout.php"> <button class="play-button">Se déconnecter</button></a>
    </div>
</header>
<main>
    <h2>Détails du Joueur</h2>
    <p><strong>Numéro de Licence :</strong> <?= htmlspecialchars($joueur['Numéro_de_licence']) ?></p>
    <p><strong>Nom :</strong> <?= htmlspecialchars($joueur['Nom']) ?></p>
    <p><strong>Prénom :</strong> <?= htmlspecialchars($joueur['Prénom']) ?></p>
    <p><strong>Statut :</strong> <?= htmlspecialchars($joueur['Statut']) ?></p>

    <?php if ($joueurAParticipe): ?>
        <h2>Statistiques VDN</h2>
        <p>Total Matchs : <?= $varStatsGlobales['total_matchs'] ?></p>
        <p>Victoires : <?= $varStatsGlobales['total_gagnes'] ?> (<?= $varStatsGlobales['pourcentage_gagnes'] ?>%)</p>
        <p>Défaites : <?= $varStatsGlobales['total_perdus'] ?> (<?= $varStatsGlobales['pourcentage_perdus'] ?>%)</p>
        <p>Nuls : <?= $varStatsGlobales['total_nuls'] ?> (<?= $varStatsGlobales['pourcentage_nuls'] ?>%)</p>

        <h2>Statistiques Moyennes</h2>
        <table>
            <thead>
            <tr>
                <th>Poste Préféré</th>
                <th>Nombre de Titularisations</th>
                <th>Nombre de Titularisations Consécutives</th>
                <th>Nombre de Remplacements</th>
                <th>Pourcentage de Matchs Gagnés</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= htmlspecialchars($varStatutMoyenneSelectPr100['poste_prefere'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($varTitularisations) ?></td>
                <td><?= htmlspecialchars($varTitularisationsConsecutives) ?></td>
                <td><?= htmlspecialchars($varStatutMoyenneSelectPr100['total_remplacements'] ?? 0) ?></td>
                <td><?= htmlspecialchars($varStatutMoyenneSelectPr100['pourcentage_gagnes'] ?? 0) ?>%</td>
            </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>Ce joueur n'a pas encore joué de match. Les statistiques ne sont pas disponibles.</p>
    <?php endif; ?>

    <h2>Commentaires du Coach</h2>
    <form method="POST">
        <textarea name="commentaire_coach" rows="4" cols="50"></textarea>
        <br>
        <button type="submit">Sauvegarder</button>
    </form>

    <h2>Commentaires Existant(s)</h2>
    <?php if (!empty($listeCommentaires)): ?>
        <ul>
            <?php foreach ($listeCommentaires as $commentaire): ?>
                <li>
                    <strong>Commentaire ID <?= htmlspecialchars($commentaire['ID_Note']) ?> :</strong>
                    <?= htmlspecialchars($commentaire['Commentaire']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun commentaire pour ce joueur.</p>
    <?php endif; ?>

</main>
</body>
</html>