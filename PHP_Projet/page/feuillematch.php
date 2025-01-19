<?php

// Démarrage de la session pour gérer les informations de l'utilisateur connecté
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

// Vérification que l'ID du match est présent, sinon on arrête le script avec un message d'erreur
if (empty($id_match)) {
    die('ID du match manquant.');
}

// Création d'une instance de gestion des matchs
$manager = new MatchManager($linkpdo);

// Vérification si une feuille de match existe déjà pour le match en question
$feuilleExiste = $manager->feuilleExiste($id_match);

// Récupération des joueurs déjà enregistrés pour ce match
$joueursSelectionnes = $manager->obtenirJoueursParMatch($id_match);

// Récupération de tous les joueurs actifs (disponibles pour la sélection)
$joueursActifs = $manager->obtenirJoueursActifs();

// Initialisation d'une variable pour stocker les messages d'erreur
$erreur = '';

// Traitement du formulaire soumis pour modifier la feuille de match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_feuille'])) {
    // Récupération des données envoyées par le formulaire
    $joueursSelectionnesPOST = $_POST['joueurs'] ?? [];
    $statuts = $_POST['statut'] ?? [];
    $roles = $_POST['role'] ?? [];

    // Initialisation des compteurs pour vérifier les quotas de titulaires et remplaçants
    $countTitulaire = 0;
    $countRemplacant = 0;

    // Parcours des joueurs sélectionnés pour compter les statuts
    foreach ($joueursSelectionnesPOST as $index => $numLicence) {
        if ($statuts[$index] === 'titulaire') {
            $countTitulaire++;
        } elseif ($statuts[$index] === 'remplaçant') {
            $countRemplacant++;
        }
    }

    // Vérification des règles de sélection (exactement 5 titulaires et 2 remplaçants)
    if ($countTitulaire !== 5) {
        $erreur = 'Vous devez sélectionner exactement 5 joueurs titulaires.';
    } elseif ($countRemplacant !== 2) {
        $erreur = 'Vous devez sélectionner exactement 2 joueurs remplaçants.';
    } else {
        // Mise à jour de la feuille de match avec les joueurs sélectionnés
        $manager->mettreAJourFeuilleDeMatch($id_match, $joueursSelectionnesPOST, $statuts, $roles);

        // Redirection vers la page de la feuille de match une fois la mise à jour effectuée
        header("Location: feuillematch.php?id_match=$id_match");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Définition de l'encodage des caractères et de la mise en page responsive -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Script JavaScript pour activer/désactiver les champs en fonction des sélections -->
    <script>
        // Fonction qui active/désactive les champs "statut" et "rôle" en fonction de la case à cocher
        function toggleFields(checkbox) {
            const row = checkbox.closest('tr'); // Récupère la ligne associée à la case cochée
            const statutSelect = row.querySelector("select[name='statut[]']"); // Champ statut
            const roleSelect = row.querySelector("select[name='role[]']"); // Champ rôle

            if (checkbox.checked) {
                statutSelect.disabled = false; // Active le champ statut
                if (statutSelect.value === 'titulaire') {
                    roleSelect.disabled = false; // Active le champ rôle si le joueur est titulaire
                }
            } else {
                statutSelect.disabled = true; // Désactive le champ statut
                roleSelect.disabled = true; // Désactive le champ rôle
                roleSelect.value = ''; // Réinitialise le champ rôle
            }
        }

        // Fonction qui active/désactive le champ "rôle" en fonction de la sélection du statut
        function toggleRole(select) {
            const row = select.closest('tr'); // Récupère la ligne associée au champ
            const roleSelect = row.querySelector("select[name='role[]']"); // Champ rôle

            if (select.value === 'titulaire') {
                roleSelect.disabled = false; // Active le champ rôle si le statut est "titulaire"
            } else {
                roleSelect.disabled = true; // Désactive le champ rôle dans les autres cas
                roleSelect.value = ''; // Réinitialise le champ rôle
            }
        }
    </script>

    <!-- Inclusion de la feuille de style CSS pour le thème Valorant -->
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
    <title>Feuille de Match</title>
</head>
<body>
<header>
    <div class="header-left">
        <img src="../image/riot-games-logo.png" alt="Riot Games" class="img">
        <img src="../image/valorant-logo.png" alt="Valorant" class="img">
    </div>
    <h1 class="header-title">Feuille de Match</h1>
</header>

<main>
    <!-- Affiche l'identifiant du match en cours -->
    <h2>Match ID : <?= htmlspecialchars($id_match) ?></h2>

    <!-- Vérifie si la feuille de match est validée et affiche un tableau récapitulatif -->
    <?php if ($feuilleExiste && !isset($_POST['modifier'])): ?>
        <p>La feuille de match a déjà été validée.</p>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Statut</th>
                <th>Rôle</th>
            </tr>
            </thead>
            <tbody>
            <!-- Parcours et affichage des joueurs déjà sélectionnés pour ce match -->
            <?php foreach ($joueursSelectionnes as $joueur): ?>
                <tr>
                    <td><?= htmlspecialchars($joueur['Nom']) ?></td>
                    <td><?= htmlspecialchars($joueur['Prénom']) ?></td>
                    <td><?= htmlspecialchars($joueur['Statut_titulaire_remplacant']) ?></td>
                    <td><?= htmlspecialchars($joueur['Poste'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Boutons pour modifier la feuille ou revenir à la liste des matchs -->
        <form method="POST" style="display: flex; gap: 10px;">
            <button type="submit" name="modifier" value="1">Modifier la feuille de match</button>
            <button type="button" onclick="location.href='matchs.php';">Retour aux matchs</button>
        </form>
    <?php else: ?>
        <!-- Formulaire pour créer ou modifier la feuille de match -->
        <form method="POST" onsubmit="return validateSelection();">
            <input type="hidden" name="modifier_feuille" value="1">
            <table>
                <thead>
                <tr>
                    <th>Sélection</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Statut</th>
                    <th>Rôle</th>
                </tr>
                </thead>
                <tbody>
                <!-- Parcours des joueurs actifs pour les afficher dans le formulaire -->
                <?php foreach ($joueursActifs as $joueur): ?>
                    <?php
                    $estSelectionne = false;
                    $statutJoueur = '';
                    $roleJoueur = '';
                    // Vérifie si le joueur est déjà sélectionné
                    foreach ($joueursSelectionnes as $joueurSelectionne) {
                        if ($joueurSelectionne['Numéro_de_licence'] === $joueur['Numéro_de_licence']) {
                            $estSelectionne = true;
                            $statutJoueur = $joueurSelectionne['Statut_titulaire_remplacant'];
                            $roleJoueur = $joueurSelectionne['Poste'];
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <!-- Case à cocher pour sélectionner ou désélectionner un joueur -->
                        <td>
                            <input type="checkbox" name="joueurs[]" value="<?= htmlspecialchars($joueur['Numéro_de_licence']) ?>"
                                <?= $estSelectionne ? 'checked' : '' ?>
                                   onchange="toggleFields(this)">
                        </td>
                        <!-- Affiche les informations du joueur -->
                        <td><?= htmlspecialchars($joueur['Nom']) ?></td>
                        <td><?= htmlspecialchars($joueur['Prénom']) ?></td>
                        <!-- Menu déroulant pour choisir le statut -->
                        <td>
                            <select name="statut[]" onchange="toggleRole(this)" <?= !$estSelectionne ? 'disabled' : '' ?>>
                                <option value="" <?= $statutJoueur === '' ? 'selected' : '' ?>>-- Choisissez --</option>
                                <option value="titulaire" <?= $statutJoueur === 'titulaire' ? 'selected' : '' ?>>Titulaire</option>
                                <option value="remplaçant" <?= $statutJoueur === 'remplaçant' ? 'selected' : '' ?>>Remplaçant</option>
                            </select>
                        </td>
                        <!-- Menu déroulant pour choisir le rôle (si titulaire) -->
                        <td>
                            <select name="role[]" <?= $statutJoueur !== 'titulaire' ? 'disabled' : '' ?>>
                                <option value="" <?= $roleJoueur === '' ? 'selected' : '' ?>>-- Choisissez --</option>
                                <option value="Duelliste" <?= $roleJoueur === 'Duelliste' ? 'selected' : '' ?>>Duelliste</option>
                                <option value="Initiateur" <?= $roleJoueur === 'Initiateur' ? 'selected' : '' ?>>Initiateur</option>
                                <option value="Sentinelle" <?= $roleJoueur === 'Sentinelle' ? 'selected' : '' ?>>Sentinelle</option>
                                <option value="Contrôleur" <?= $roleJoueur === 'Contrôleur' ? 'selected' : '' ?>>Contrôleur</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Affichage des erreurs éventuelles -->
            <?php if ($erreur): ?>
                <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>
            <!-- Bouton pour valider les modifications -->
            <button type="submit">Valider la Feuille</button>
        </form>
    <?php endif; ?>
</main>
<footer>
    <!-- Pied de page avec copyright -->
    <p>&copy; 2025 Valorant Match Manager</p>
</footer>
</body>
</html>
