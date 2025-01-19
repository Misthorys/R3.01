<?php
session_start();
// Vérifiez si l'utilisateur est connecté, sinon redirigez vers la page de connexion
if (!isset($_SESSION['utilisateur_connecte'])) {
    header('Location: ./page/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Application de gestion sportive</title>
    <link rel="stylesheet" href="./css/Valorant_Theme.css">
</head>
<body>
<header>
    <div class="header-left">
        <img src="image/riot-games-logo.png" alt="Riot Games" class="logo">
        <img src="image/valorant-logo.png" alt="Valorant" class="logo">
    </div>
    <nav class="navbar">
        <a href="page/joueurs.php">Gestion des joueurs</a>
        <a href="page/matchs.php">Gestion des matchs</a>
        <a href="page/statistiquesMatch.php">Statistique des matchs</a>
    </nav>
    <div class="header-right">
        <a href="page/logout.php"> <button class="play-button"> Se déconnecter</button></a>
    </div>
</header>
    <main>
        <h2>Bienvenue sur l'application de gestion de l'&eacute;quipe</h2>
        <p>Utilisez le menu ci-dessus pour naviguer &agrave; travers les diff&eacute;rentes fonctionnalit&eacute;s de l'application.</p>
    </main>
    
</body>
</html>
