-- Suppression des tables dans l'ordre pour respecter les contraintes de clé étrangère

-- Suppression des tables avec des dépendances
DROP TABLE IF EXISTS Correspondre;
DROP TABLE IF EXISTS Participe;

-- Suppression des tables principales
DROP TABLE IF EXISTS Note;
DROP TABLE IF EXISTS Matchs;
DROP TABLE IF EXISTS Joueur;
DROP TABLE IF EXISTS Utilisateur;

-- Suppression de la base de données\DROP DATABASE IF EXISTS t1;
