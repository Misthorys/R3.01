CREATE DATABASE t1;
USE t1;

-- Créer les tables avec les modifications nécessaires
CREATE TABLE Joueur(
   Numéro_de_licence INT,
   Nom VARCHAR(50),
   Prénom VARCHAR(50),
   Date_de_naissance DATE,
   Taille__en_cm_ DECIMAL(5,2),
   Poids DECIMAL(5,2),
   Statut VARCHAR(50),
   PRIMARY KEY(Numéro_de_licence)
);

CREATE TABLE Matchs(
   ID_Match VARCHAR(50),
   Dateheure DATETIME,
   Nom_équipe_ennemi VARCHAR(30),
   Lieu_de_bataille VARCHAR(50),
   Terrain VARCHAR(50),
   Resultat VARCHAR(50),
   PRIMARY KEY(ID_Match)
);

CREATE TABLE Note(
   ID_Note INT AUTO_INCREMENT,
   Commentaire VARCHAR(100),
   PRIMARY KEY(ID_Note)
);

CREATE TABLE Utilisateur(
   ID_utilisateur INT AUTO_INCREMENT,
   Mot_de_passe VARCHAR(255),
   nom_utilisateur VARCHAR(50),
   PRIMARY KEY(ID_utilisateur)
);

CREATE TABLE Correspondre (
   Numéro_de_licence INT,
   ID_Note INT,
   PRIMARY KEY (Numéro_de_licence, ID_Note),
   FOREIGN KEY (Numéro_de_licence) REFERENCES Joueur(Numéro_de_licence) ON DELETE CASCADE,
   FOREIGN KEY (ID_Note) REFERENCES Note(ID_Note)
);

CREATE TABLE participe (
    Numéro_de_licence INT NOT NULL,
    ID_Match VARCHAR(50) NOT NULL,
    Statut_titulaire_remplacant VARCHAR(255) NOT NULL,
    Evaluation_Apres_match_commentaire TEXT,
    Note_Apres_Match INT,
    Nombre_de_kill INT DEFAULT 0,
    Nombre_de_mort INT DEFAULT 0,
    Nombre_d_assistance INT DEFAULT 0,
    Poste VARCHAR(50),
    PRIMARY KEY (Numéro_de_licence, ID_Match),
    CONSTRAINT fk_participe_match FOREIGN KEY (ID_Match) REFERENCES Matchs(ID_Match) ON DELETE CASCADE
);

-- Ajouter un utilisateur avec les privilèges nécessaires
CREATE USER 'puissantsy'@'localhost' IDENTIFIED BY '$iutinfo';
GRANT ALL PRIVILEGES ON t1.* TO 'puissantsy'@'localhost';
FLUSH PRIVILEGES;

-- Identifiant/mot de passe à utiliser dans le fichier database.php
