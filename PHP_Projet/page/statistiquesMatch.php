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

// Création d'une instance pour gérer les statistiques des joueurs
$manager = new StatistiquesJoueurManager($linkpdo);

// Définition des colonnes disponibles pour le tri
$columns = ['Nom_Prénom', 'Moyenne_Note', 'Matchs_Joues'];

// Récupération des paramètres de tri depuis la requête GET
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $columns) ? $_GET['sort'] : 'Nom_Prénom';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

// Récupération des données des joueurs triées
$player_data = $manager->obtenirMoyennesParJoueurAvecTri($sort, $order);

// Inversion de l'ordre pour la prochaine requête (ASC ou DESC)
$nextOrder = $order === 'ASC' ? 'desc' : 'asc';

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Joueurs</title>
    <link rel="stylesheet" href="../css/Valorant_Theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Conteneur principal contenant le tableau et le graphique */
        #stats-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin: 20px;
        }

        #playerAverageChart {
            flex: 1;
            max-width: 50%;
            max-height: 400px;
        }

        table {
            flex: 1;
            font-size: 14px;
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px 12px;
            text-align: center;
        }

        th {
            background-color: #ff4655;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #1b2735;
        }

        tr:hover {
            background-color: #2c3848;
        }
    </style>
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
        <a href="../index.php">Accueil</a>
    </nav>
    <div class="header-right">
        <a href="logout.php"> <button class="play-button">Se déconnecter</button></a>
    </div>
</header>
<main>
    <h2>Moyenne des Notes Globales par Joueur</h2>
    <p style="font-style: italic; color: gray;">Cliquez sur une barre du graphique pour accéder aux détails des statistiques par joueur qui ont déjà joué un match.</p>
    <div id="stats-container">
        <canvas id="playerAverageChart"></canvas>
        <table>
            <thead>
            <tr>
                <th><a href="?sort=Nom_Prénom&order=<?= $nextOrder ?>">Joueur</a></th>
                <th><a href="?sort=Moyenne_Note&order=<?= $nextOrder ?>">Moyenne des Notes</a></th>
                <th><a href="?sort=Matchs_Joues&order=<?= $nextOrder ?>">Matchs Joués</a></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($player_data as $data): ?>
                <tr>
                    <td>
                        <a href="statistiques.php?numero_licence=<?= htmlspecialchars($data['Numero_Licence']) ?>">
                            <?= htmlspecialchars($data['Nom_Prénom']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($data['Moyenne_Note']) ?></td>
                    <td><?= htmlspecialchars($data['Matchs_Joues']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Script pour générer le graphique avec Chart.js -->
    <script>
        // Préparation des données pour le graphique
        const playerData = <?= json_encode($player_data) ?>;
        const playerNames = playerData.map(data => data.Nom_Prénom);
        const playerAverages = playerData.map(data => data.Moyenne_Note);
        // Initialisation du graphique
        const ctx = document.getElementById('playerAverageChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // Type de graphique : barre
            data: {
                labels: playerNames, // Noms des joueurs
                datasets: [{
                    label: 'Moyenne des notes de tous les matchs par joueur',
                    data: playerAverages,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Permet au graphique de s'adapter au conteneur
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Joueurs'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Moyenne des notes'
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                // Ajout d'informations supplémentaires au survol
                                const playerIndex = context.dataIndex;
                                const player = playerData[playerIndex];
                                return `Moyenne: ${player.Moyenne_Note}, Matchs Joués: ${player.Matchs_Joues}`;
                            }
                        }
                    }
                },
                // Redirection lors du clic sur une barre
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const player = playerData[index];
                        window.location.href = `statistiques.php?numero_licence=${player.Numero_Licence}`;
                    }
                }
            }
        });
    </script>
</main>
</body>
</html>
