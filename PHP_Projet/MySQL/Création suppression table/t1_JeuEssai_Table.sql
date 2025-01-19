-- Insertion de données dans la table Joueur
INSERT INTO Joueur (Numéro_de_licence, Nom, Prénom, Date_de_naissance,Taille__en_cm_, Poids, Statut) VALUES
                                                                                                        (1001, 'Dupont', 'Alex', '2000-05-12', 180, 75.5, 'Actif'),
                                                                                                        (1002, 'Martin', 'Sophie', '1999-07-21', 165, 60.2, 'Blesse'),
                                                                                                        (1003, 'Bernard', 'Thomas', '2001-03-15', 175, 68.0, 'Suspendu'),
                                                                                                        (1004, 'Petit', 'Emma', '2002-08-30', 160, 55.3, 'Actif'),
                                                                                                        (1005, 'Robert', 'Lucas', '1998-11-25', 185, 82.1, 'Absent'),
                                                                                                        (1006, 'Durand', 'Clara', '2000-02-11', 170, 62.7, 'Actif'),
                                                                                                        (1007, 'Moreau', 'Nathan', '1999-10-07', 172, 70.4, 'Actif'),
                                                                                                        (1008, 'Laurent', 'Marie', '2001-06-19', 158, 50.9, 'Blesse'),
                                                                                                        (1009, 'Simon', 'David', '2003-01-02', 178, 73.0, 'Actif'),
                                                                                                        (1010, 'Lemoine', 'Camille', '2002-04-23', 167, 58.6, 'Suspendu');

-- Insertion de données dans la table Matchs
INSERT INTO Matchs (ID_Match, Dateheure, Nom_équipe_ennemi, Lieu_de_bataille, Terrain, Resultat) VALUES
                                                                                                     ('M001', '2024-07-15 18:00:00', 'Team Phoenix', 'Domicile', 'Map 1', 'Victoire'),
                                                                                                     ('M002', '2024-07-22 20:00:00', 'Shadow Elite', 'Exterieur', 'Map 2', 'Defaite'),
                                                                                                     ('M003', '2024-07-29 17:30:00', 'Night Hunters', 'Domicile', 'Map 3', 'Victoire'),
                                                                                                     ('M004', '2024-08-05 19:00:00', 'Steel Wolves', 'Exterieur', 'Map 4', 'Match nul'),
                                                                                                     ('M005', '2024-08-12 18:45:00', 'Inferno Squad', 'Domicile', 'Map 1', 'Victoire'),
                                                                                                     ('M006', '2024-08-19 20:15:00', 'Ghost Warriors', 'Exterieur', 'Map 2', 'Defaite'),
                                                                                                     ('M007', '2024-08-26 18:00:00', 'Cyber Force', 'Domicile', 'Map 3', 'Victoire'),
                                                                                                     ('M008', '2024-09-02 19:30:00', 'Titan Legion', 'Exterieur', 'Map 4', 'Defaite'),
                                                                                                     ('M009', '2024-09-09 17:00:00', 'Eclipse Squad', 'Domicile', 'Map 1', 'Match nul'),
                                                                                                     ('M010', '2024-09-16 18:00:00', 'Vortex Blades', 'Exterieur', 'Map 2', 'Victoire');

-- Insertion de données dans la table Note
INSERT INTO Note (Commentaire) VALUES
                                            ('Excellent jeu d equipe'),
                                            ('Besoin d ameliorer la communication'),
                                            ('Performance moyenne'),
                                            ('Strategie bien appliquee'),
                                            ('Manque de reactivite'),
                                            ('Bonne prise de decision'),
                                            ('Precision des tirs a ameliorer'),
                                            ('Role de soutien bien tenu'),
                                            ('Mauvaise position sur la carte'),
                                            ('Effort constant tout au long du match');

-- Insertion de données dans la table Correspondre
INSERT INTO Correspondre (Numéro_de_licence, ID_Note) VALUES
                                                          (1001, 1),
                                                          (1002, 2),
                                                          (1003, 3),
                                                          (1004, 4),
                                                          (1006, 5),
                                                          (1007, 6),
                                                          (1008, 7),
                                                          (1009, 8),
                                                          (1010, 1),
                                                          (1001, 9);

-- Insertion de données dans la table Participe
INSERT INTO Participe (Numéro_de_licence, ID_Match, Evaluation_Apres_match_commentaire, Note_Apres_Match, Nombre_de_kill, Nombre_de_mort, Nombre_d_assistance, Statut_titulaire_remplacant, Poste) VALUES
                                                                                                                                                                                                       (1001, 'M001', 'Très bon dueliste', 9, 15, 5, 3, 'Titulaire', 'Duelist'),
                                                                                                                                                                                                       (1004, 'M001', 'Bon travail d equipe', 8, 5, 3, 6, 'Titulaire', 'Initiator'),
                                                                                                                                                                                                       (1007, 'M002', 'Role de soutien modere', 6, 4, 2, 2, 'Remplacant', 'Sentinel'),
                                                                                                                                                                                                       (1009, 'M002', 'Strategie mal appliquee', 5, 3, 7, 1, 'Titulaire', 'Controller'),
                                                                                                                                                                                                       (1001, 'M003', 'Excellent leader', 10, 20, 4, 5, 'Titulaire', 'Duelist'),
                                                                                                                                                                                                       (1006, 'M003', 'Bonne initiative', 7, 8, 5, 4, 'Remplacant', 'Initiator'),
                                                                                                                                                                                                       (1010, 'M004', 'Defense moyenne', 6, 6, 4, 3, 'Titulaire', 'Sentinel'),
                                                                                                                                                                                                       (1003, 'M005', 'Participation faible', 4, 2, 8, 0, 'Remplacant', 'Controller'),
                                                                                                                                                                                                       (1007, 'M006', 'Bonne performance', 8, 12, 3, 6, 'Titulaire', 'Duelist'),
                                                                                                                                                                                                       (1009, 'M007', 'Excellente organisation', 9, 10, 3, 7, 'Titulaire', 'Initiator');
