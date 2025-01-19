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

// Création d'une instance pour gérer les matchs
$manager = new MatchManager($linkpdo);

// Gestion des actions sur les matchs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_match'])) {
        // Ajout d'un nouveau match
        $data = [
            'id_match' => $_POST['id_match'] ?? '',
            'dateheure' => $_POST['dateheure'] ?? '',
            'nom_equipe' => $_POST['nom_equipe'] ?? '',
            'lieu' => $_POST['lieu'] ?? '',
            'terrain' => $_POST['terrain'] ?? ''
        ];

        // Appel à la méthode du gestionnaire pour valider et ajouter le match
        $erreur = $manager->ajouterMatch($data);
        if (empty($erreur)) {
            header('Location: matchs.php');
            exit;
        }
    } elseif (isset($_POST['delete_match'])) {
        // Suppression d'un match
        $id_match = $_POST['id_match_to_delete'] ?? '';
        if ($id_match) {
            $erreur = $manager->supprimerMatch($id_match);
            if (empty($erreur)) {
                header('Location: matchs.php');
                exit;
            }
        } else {
            $erreur = 'Veuillez sélectionner un match à supprimer.';
        }
    } elseif (isset($_POST['update_result'])) {
        // Mise à jour du résultat d'un match
        $id_match = $_POST['id_match'] ?? '';
        $resultat = $_POST['resultat'] ?? '';
        if (!empty($id_match) && !empty($resultat)) {
            $erreur = $manager->modifierResultat($id_match, $resultat);
        } else {
            $erreur = "Veuillez sélectionner un match et un résultat.";
        }
    }
}

// Récupération de tous les matchs via le gestionnaire
$matchs = $manager->obtenirMatchs();

// Séparation des matchs futurs et passés
$matchsFuturs = [];
$matchsPasses = [];
$currentTime = time();

foreach ($matchs as $match) {
    $match['feuilleExiste'] = $manager->verifierFeuilleExiste($match['ID_Match']);
    if (strtotime($match['Dateheure']) >= $currentTime) {
        $matchsFuturs[] = $match;
    } else {
        $matchsPasses[] = $match;
    }
}

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <script>
        // Fonction pour afficher le formulaire d'ajout d'un match
        function toggleAddMatchForm() {
            const form = document.getElementById('addMatchForm');
            const button = document.getElementById('toggleButton');
            if (form.style.display === 'none') {
                form.style.display = 'block';
                button.style.display = 'none';
            }
        }
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
    <title>Gestion des Matchs</title>
</head>
<body>
<header>
    <!-- En-tête avec logos et navigation -->
    <div class="header-left">
        <img src="../image/riot-games-logo.png" alt="Riot Games" class="img">
        <img src="../image/valorant-logo.png" alt="Valorant" class="img">
    </div>
    <nav class="navbar">
        <a href="joueurs.php">Gestion des joueurs</a>
        <a href="statistiquesMatch.php">Statistique des matchs</a>
        <a href="../index.php">Acceuil</a>
    </nav>
    <div class="header-right">
        <a href="logout.php"> <button class="play-button"> Se déconnecter</button></a>
    </div>
</header>
<main>
    <!-- Bouton pour afficher le formulaire d'ajout de match -->
    <button onclick="toggleAddMatchForm()" class="play-button">Ajouter un match</button>

    <!-- Formulaire d'ajout de match -->
    <div id="addMatchForm" style="display: none; margin-top: 20px;">
        <!-- Champs pour saisir les informations du match -->
        <form action="matchs.php" method="POST">
            <input type="hidden" name="add_match" value="1">
            <div>
                <label for="id_match">ID Match :</label>
                <input type="text" id="id_match" name="id_match" required>
            </div>
            <div>
                <label for="dateheure">Date et Heure :</label>
                <input type="datetime-local" id="dateheure" name="dateheure" required>
            </div>
            <div>
                <label for="nom_equipe">Nom de l'équipe adverse :</label>
                <input type="text" id="nom_equipe" name="nom_equipe" required>
            </div>
            <div>
                <label for="lieu">Lieu :</label>
                <input type="text" id="lieu" name="lieu" required>
            </div>
            <div>
                <label for="terrain">Terrain :</label>
                <input type="text" id="terrain" name="terrain" required>
            </div>
            <?php if (isset($erreur)): ?>
                <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>
            <button type="submit">Ajouter le Match</button>
        </form>
    </div>

    <!-- Section pour supprimer un match futur -->
    <h2>Supprimer un match à venir</h2>
    <form action="matchs.php" method="POST">
        <input type="hidden" name="delete_match" value="1">
        <div>
            <label for="id_match_to_delete">Sélectionnez un match :</label>
            <select id="id_match_to_delete" name="id_match_to_delete" required>
                <option value="">-- Choisissez un match --</option>
                <?php foreach ($matchsFuturs as $match): ?>
                    <option value="<?= htmlspecialchars($match['ID_Match']) ?>">
                        <?= htmlspecialchars($match['ID_Match'] . ' - ' . $match['Nom_équipe_ennemi'] . ' (' . $match['Dateheure'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Supprimer le match</button>
    </form>

    <!-- Tableau des matchs futurs -->

    <h2>Match à venir</h2>
    <table>
        <thead>
        <tr>
            <th>ID Match</th>
            <th>Date et Heure</th>
            <th>Équipe Adverse</th>
            <th>Lieu</th>
            <th>Terrain</th>
            <th>Résultat</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($matchsFuturs as $match): ?>
            <tr>
                <td><?= htmlspecialchars($match['ID_Match']) ?></td>
                <td><?= htmlspecialchars($match['Dateheure']) ?></td>
                <td><?= htmlspecialchars($match['Nom_équipe_ennemi']) ?></td>
                <td><?= htmlspecialchars($match['Lieu_de_bataille']) ?></td>
                <td><?= htmlspecialchars($match['Terrain']) ?></td>
                <td><?= htmlspecialchars($match['Resultat']) ?></td>
                <td style="display: flex; gap: 10px;">
                    <?php if (strtotime($match['Dateheure']) >= $currentTime): ?>
                        <button onclick="location.href='modifiermatch.php?id_match=<?= $match['ID_Match'] ?>';">Modifier match</button>
                    <?php endif; ?>
                    <?php if ($match['feuilleExiste']): ?>
                        <button onclick="location.href='feuillematch.php?id_match=<?= $match['ID_Match'] ?>';">Voir la Feuille</button>
                    <?php else: ?>
                        <button onclick="location.href='feuillematch.php?id_match=<?= $match['ID_Match'] ?>';">Faire la Feuille</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Affichage similaire pour les matchs passés -->
    <h2>Matchs déjà faits</h2>
    <table>
        <thead>
        <tr>
            <th>ID Match</th>
            <th>Date et Heure</th>
            <th>Équipe Adverse</th>
            <th>Lieu</th>
            <th>Terrain</th>
            <th>Résultat</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($matchsPasses as $match): ?>
            <tr>
                <td><?= htmlspecialchars($match['ID_Match']) ?></td>
                <td><?= htmlspecialchars($match['Dateheure']) ?></td>
                <td><?= htmlspecialchars($match['Nom_équipe_ennemi']) ?></td>
                <td><?= htmlspecialchars($match['Lieu_de_bataille']) ?></td>
                <td><?= htmlspecialchars($match['Terrain']) ?></td>
                <td>
                    <?php
                    $date_match = strtotime($match['Dateheure']);
                    if (($currentTime - $date_match) <= (7 * 24 * 60 * 60)): ?>
                        <form action="matchs.php" method="POST" style="display:inline;">
                            <input type="hidden" name="update_result" value="1">
                            <input type="hidden" name="id_match" value="<?= htmlspecialchars($match['ID_Match']) ?>">
                            <select name="resultat" onchange="this.form.submit()">
                                <option value="">-- Sélectionnez le résultat --</option>
                                <option value="Victoire" <?= $match['Resultat'] === 'Victoire' ? 'selected' : '' ?>>Victoire</option>
                                <option value="Défaite" <?= $match['Resultat'] === 'Défaite' ? 'selected' : '' ?>>Défaite</option>
                                <option value="Match nul" <?= $match['Resultat'] === 'Match nul' ? 'selected' : '' ?>>Match nul</option>
                            </select>
                        </form>
                    <?php else: ?>
                        <?= htmlspecialchars($match['Resultat'] ?? 'Non défini') ?>
                    <?php endif; ?>
                </td>
                <td style="display: flex; gap: 10px;">
                    <?php if ($match['feuilleExiste']): ?>
                        <button onclick="location.href='feuillematch.php?id_match=<?= $match['ID_Match'] ?>';">Voir la Feuille</button>
                        <button onclick="location.href='notesmatch.php?id_match=<?= $match['ID_Match'] ?>';">Voir les Notes</button>
                    <?php else: ?>
                        <span>Feuille non faite</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<footer>
    <p>&copy; 2025 Valorant Match Manager</p>
</footer>
</body>
</html>
