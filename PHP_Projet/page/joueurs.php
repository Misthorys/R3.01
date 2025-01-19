<?php

// Démarrage de la session pour gérer les informations utilisateur
session_start();

// Inclusion des fichiers nécessaires pour la base de données et les fonctions SQL
require_once 'database.php';
require_once 'librairie_sql.php';

// Vérification que l'utilisateur est connecté
// Redirection vers la page de connexion si ce n'est pas le cas
if (!isset($_SESSION['utilisateur_connecte'])) {
    header('Location: login.php');
    exit;
}

// Création d'une instance de gestion des joueurs
$manager = new JoueurManager($linkpdo);

// Gestion des actions sur les joueurs (ajout, modification, suppression)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ajouter'])) {
        // Ajout d'un nouveau joueur avec les données envoyées par le formulaire
        $data = [
            'numero_licence' => $_POST['numero_licence'],
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'date_naissance' => $_POST['date_naissance'],
            'taille' => $_POST['taille'],
            'poids' => $_POST['poids'],
            'statut' => $_POST['statut']
        ];
        $manager->ajouterJoueur($data);

        // Redirection vers la liste des joueurs après ajout
        header("Location: joueurs.php");
        exit;
    }

    if (isset($_POST['modifier'])) {
        // Modification des informations d'un joueur existant
        $data = [
            'numero_licence' => $_POST['numero_licence'],
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'date_naissance' => $_POST['date_naissance'],
            'taille' => $_POST['taille'],
            'poids' => $_POST['poids'],
            'statut' => $_POST['statut']
        ];
        $manager->modifierJoueur($data);

        // Redirection vers la liste des joueurs après modification
        header("Location: joueurs.php");
        exit;
    }

    if (isset($_POST['supprimer'])) {
        // Suppression d'un joueur avec le numéro de licence spécifié
        $manager->supprimerJoueur($_POST['numero_licence']);

        // Redirection vers la liste des joueurs après suppression
        header("Location: joueurs.php");
        exit;
    }
}

// Définition des colonnes disponibles pour le tri
$columns = ['Numéro_de_licence', 'Nom', 'Prénom', 'Date_de_naissance', 'Taille__en_cm_', 'Poids', 'Statut'];

// Validation des paramètres GET pour le tri
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $columns) ? $_GET['sort'] : 'Numéro_de_licence';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

// Inverse l'ordre pour le prochain tri
$nextOrder = $order === 'ASC' ? 'desc' : 'asc';

// Récupération de la liste des joueurs avec tri
$joueurs = $manager->obtenirTousLesJoueurs($sort, $order);

// Récupération des joueurs ayant participé à des matchs
$joueursAyantParticipe = $manager->obtenirJoueursAyantParticipe();

// Filtrage des joueurs qui n'ont pas participé à des matchs
$joueursSansMatch = array_filter($joueurs, function ($joueur) use ($joueursAyantParticipe) {
    return !in_array($joueur['Numéro_de_licence'], $joueursAyantParticipe);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Joueurs</title>
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
</head>
<body>
<header>
    <div class="header-left">
        <img src="../image/riot-games-logo.png" alt="Riot Games" class="logo">
        <img src="../image/valorant-logo.png" alt="Valorant" class="logo">
    </div>
    <nav class="navbar">
        <a href="matchs.php">Gestion des matchs</a>
        <a href="statistiquesMatch.php">Statistique des matchs</a>
        <a href="../index.php">Acceuil</a>
    </nav>
    <div class="header-right">
        <a href="logout.php"> <button class="play-button"> Se déconnecter</button></a>
    </div>
</header>
<main>
    <h2>Liste des Joueurs</h2>
    <!-- Tableau affichant la liste des joueurs -->
    <table>
        <thead>
        <tr>
            <!-- Liens pour trier les colonnes -->
            <th><a href="?sort=Numéro_de_licence&order=<?= $nextOrder ?>">Numéro de Licence</a></th>
            <th><a href="?sort=Nom&order=<?= $nextOrder ?>">Nom</a></th>
            <th><a href="?sort=Prénom&order=<?= $nextOrder ?>">Prénom</a></th>
            <th><a href="?sort=Date_de_naissance&order=<?= $nextOrder ?>">Date de Naissance</a></th>
            <th><a href="?sort=Taille__en_cm_&order=<?= $nextOrder ?>">Taille (cm)</a></th>
            <th><a href="?sort=Poids&order=<?= $nextOrder ?>">Poids (kg)</a></th>
            <th><a href="?sort=Statut&order=<?= $nextOrder ?>">Statut</a></th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($joueurs as $joueur): ?>
            <tr>
                <!-- Affichage des données du joueur -->
                <td><?= htmlspecialchars($joueur['Numéro_de_licence']) ?></td>
                <td><?= htmlspecialchars($joueur['Nom']) ?></td>
                <td><?= htmlspecialchars($joueur['Prénom']) ?></td>
                <td><?= htmlspecialchars($joueur['Date_de_naissance']) ?></td>
                <td><?= htmlspecialchars($joueur['Taille__en_cm_']) ?></td>
                <td><?= htmlspecialchars($joueur['Poids']) ?></td>
                <td><?= htmlspecialchars($joueur['Statut']) ?></td>
                <td>
                    <!-- Boutons pour accéder aux commentaires et modifier les informations -->
                    <button type="button" onclick="location.href='statistiques.php?numero_licence=<?= $joueur['Numéro_de_licence'] ?>';">Commentaire</button>
                    <button onclick="document.getElementById('edit-<?= $joueur['Numéro_de_licence'] ?>').style.display='block';">Modifier</button>
                </td>

            </tr>

            <!-- Formulaire de modification pour chaque joueur -->
            <tr id="edit-<?= $joueur['Numéro_de_licence'] ?>" style="display:none;">
                <td colspan="8">
                    <form action="joueurs.php" method="POST">
                        <input type="hidden" name="numero_licence" value="<?= $joueur['Numéro_de_licence'] ?>">
                        <div>
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($joueur['Nom']) ?>" required>
                        </div>
                        <div>
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($joueur['Prénom']) ?>" required>
                        </div>
                        <div>
                            <label for="date_naissance">Date de Naissance :</label>
                            <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($joueur['Date_de_naissance']) ?>" required>
                        </div>
                        <div>
                            <label for="taille">Taille (cm) :</label>
                            <input type="number" id="taille" name="taille" value="<?= htmlspecialchars($joueur['Taille__en_cm_']) ?>" required>
                        </div>
                        <div>
                            <label for="poids">Poids (kg) :</label>
                            <input type="number" step="0.1" id="poids" name="poids" value="<?= htmlspecialchars($joueur['Poids']) ?>" required>
                        </div>
                        <div>
                            <label for="statut">Statut :</label>
                            <select id="statut" name="statut">
                                <option value="Actif" <?= $joueur['Statut'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="Blessé" <?= $joueur['Statut'] === 'Blessé' ? 'selected' : '' ?>>Blessé</option>
                                <option value="Suspendu" <?= $joueur['Statut'] === 'Suspendu' ? 'selected' : '' ?>>Suspendu</option>
                                <option value="Absent" <?= $joueur['Statut'] === 'Absent' ? 'selected' : '' ?>>Absent</option>
                            </select>
                        </div>
                        <button type="submit" name="modifier">Mettre à jour</button>
                        <button type="button" onclick="document.getElementById('edit-<?= $joueur['Numéro_de_licence'] ?>').style.display='none';">Annuler</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Section pour supprimer un joueur sans match -->
    <h2>Supprimer un Joueur (Sans match)</h2>
    <form action="joueurs.php" method="POST">
        <div>
            <label for="numero_licence">Sélectionnez un joueur :</label>
            <select id="numero_licence" name="numero_licence" required>
                <option value="">-- Choisir un joueur --</option>
                <?php foreach ($joueursSansMatch as $joueur): ?>
                    <option value="<?= $joueur['Numéro_de_licence'] ?>">
                        <?= htmlspecialchars($joueur['Nom'] . ' ' . $joueur['Prénom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="supprimer">Supprimer</button>
    </form>

    <!-- Section pour ajouter un nouveau joueur -->
    <h2>Ajouter un Joueur</h2>
    <form action="joueurs.php" method="POST">
        <div>
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div>
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>
        <div>
            <label for="numero_licence">Numéro de Licence :</label>
            <input type="text" id="numero_licence" name="numero_licence" required>
        </div>
        <div>
            <label for="date_naissance">Date de Naissance :</label>
            <input type="date" id="date_naissance" name="date_naissance" required>
        </div>
        <div>
            <label for="taille">Taille (cm) :</label>
            <input type="number" id="taille" name="taille" required>
        </div>
        <div>
            <label for="poids">Poids (kg) :</label>
            <input type="number" step="0.1" id="poids" name="poids" required>
        </div>
        <div>
            <label for="statut">Statut :</label>
            <select id="statut" name="statut" required>
                <option value="Actif">Actif</option>
                <option value="Blessé">Blessé</option>
                <option value="Suspendu">Suspendu</option>
                <option value="Absent">Absent</option>
            </select>
        </div>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>
</main>
</body>
</html>
