<?php

// Librairie SQL: librairie_sql.php
class MatchManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function ajouterMatch($data) {
        // Validation des champs
        if (empty($data['id_match']) || empty($data['dateheure']) || empty($data['nom_equipe']) || empty($data['lieu']) || empty($data['terrain'])) {
            return 'Tous les champs sont obligatoires.';
        }

        // Vérification de l'unicité de l'ID
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM matchs WHERE ID_Match = :id_match");
        $stmt->execute(['id_match' => $data['id_match']]);
        if ($stmt->fetchColumn() > 0) {
            return 'Cet ID de match existe déjà. Veuillez en choisir un autre.';
        }

        // Validation de la date
        if (strtotime($data['dateheure']) < time()) {
            return 'La date du match ne peut pas être antérieure à la date actuelle.';
        }

        // Ajout du match dans la base de données
        $stmt = $this->db->prepare(
            "INSERT INTO matchs (ID_Match, Dateheure, Nom_équipe_ennemi, Lieu_de_bataille, Terrain) 
             VALUES (:id_match, :dateheure, :nom_equipe, :lieu, :terrain)"
        );
        $stmt->execute([
            'id_match' => $data['id_match'],
            'dateheure' => $data['dateheure'],
            'nom_equipe' => $data['nom_equipe'],
            'lieu' => $data['lieu'],
            'terrain' => $data['terrain']
        ]);
        return '';
    }

    public function supprimerMatch($id_match) {
        // Vérification que le match est futur
        $stmt = $this->db->prepare("SELECT Dateheure FROM matchs WHERE ID_Match = :id_match");
        $stmt->execute(['id_match' => $id_match]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || strtotime($result['Dateheure']) < time()) {
            return "Seuls les matchs à venir peuvent être supprimés.";
        }

        // Suppression du match
        $stmt = $this->db->prepare("DELETE FROM matchs WHERE ID_Match = :id_match");
        $stmt->execute(['id_match' => $id_match]);
        return '';
    }

    /**
     * Modifie le résultat d'un match dans un délai de 7 jours après le match.
     */
    public function modifierResultat($id_match, $resultat) {
        // Vérification du délai de modification (7 jours après le match)
        $stmt = $this->db->prepare("SELECT Dateheure FROM matchs WHERE ID_Match = :id_match");
        $stmt->execute(['id_match' => $id_match]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return "Match introuvable.";
        }

        $date_match = strtotime($result['Dateheure']);
        $current_time = time();

        if ($current_time > $date_match && $current_time <= ($date_match + 7 * 24 * 60 * 60)) {
            $stmt = $this->db->prepare("UPDATE matchs SET Resultat = :resultat WHERE ID_Match = :id_match");
            $stmt->execute(['resultat' => $resultat, 'id_match' => $id_match]);
            return '';
        } else {
            return "Le délai de modification du résultat a expiré.";
        }
    }

    /**
     * Récupère tous les matchs de la base de données.
     */
    public function obtenirMatchs() {
        $sql = "SELECT ID_Match, Dateheure, Nom_équipe_ennemi, Lieu_de_bataille, Terrain, Resultat FROM matchs";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si une feuille de match existe pour un match donné.
     */
    public function verifierFeuilleExiste($id_match) {
        $sql = "SELECT COUNT(*) FROM participe WHERE ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetchColumn() > 0;
    }

    public function feuilleExiste($id_match)
    {
        $sql = "SELECT COUNT(*) FROM participe WHERE ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetchColumn() > 0;
    }

    public function obtenirJoueursParMatch($id_match)
    {
        $sql = "SELECT p.Numéro_de_licence, j.Nom, j.Prénom, p.Statut_titulaire_remplacant, p.Poste 
                FROM participe p
                INNER JOIN joueur j ON p.Numéro_de_licence = j.Numéro_de_licence
                WHERE p.ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenirJoueursActifs()
    {
        $sql = "SELECT Numéro_de_licence, Nom, Prénom FROM joueur WHERE Statut = 'actif'";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mettreAJourFeuilleDeMatch($id_match, $joueurs, $statuts, $roles)
    {
        // Supprimer les données existantes
        $deleteSQL = "DELETE FROM participe WHERE ID_Match = :id_match";
        $stmt = $this->db->prepare($deleteSQL);
        $stmt->execute(['id_match' => $id_match]);

        // Insérer les nouvelles données
        foreach ($joueurs as $index => $numLicence) {
            $statut = $statuts[$index];
            $role = $statut === 'titulaire' ? $roles[$index] : null; // Rôle uniquement pour les titulaires
            $insertSQL = "INSERT INTO participe (Numéro_de_licence, ID_Match, Statut_titulaire_remplacant, Poste) 
                          VALUES (:numLicence, :id_match, :statut, :role)";
            $stmt = $this->db->prepare($insertSQL);
            $stmt->execute([
                'numLicence' => $numLicence,
                'id_match' => $id_match,
                'statut' => $statut,
                'role' => $role
            ]);
        }
    }
}


class MatchNotesManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function obtenirDateMatch($id_match)
    {
        $sql = "SELECT Dateheure FROM matchs WHERE ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetchColumn();
    }

    public function obtenirNotesJoueurs($id_match)
    {
        $sql = "SELECT 
                    j.Numéro_de_licence,
                    j.Nom, 
                    j.Prénom, 
                    p.Evaluation_Apres_match_commentaire AS Commentaire, 
                    p.Note_Apres_Match AS Note, 
                    p.Nombre_de_kill, 
                    p.Nombre_de_mort, 
                    p.Nombre_d_assistance, 
                    p.Poste, 
                    p.Statut_titulaire_remplacant 
                FROM joueur j
                INNER JOIN participe p ON j.Numéro_de_licence = p.Numéro_de_licence
                WHERE p.ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mettreAJourNotesJoueurs($id_match, $notes)
    {
        foreach ($notes as $numero_de_licence => $data) {
            $sql = "UPDATE participe SET 
                        Note_Apres_Match = :note, 
                        Evaluation_Apres_match_commentaire = :commentaire, 
                        Nombre_de_kill = :kills, 
                        Nombre_de_mort = :deaths, 
                        Nombre_d_assistance = :assists, 
                        Statut_titulaire_remplacant = :statut 
                    WHERE ID_Match = :id_match AND Numéro_de_licence = :numero_de_licence";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'note' => $data['note'],
                'commentaire' => $data['commentaire'],
                'kills' => $data['kills'],
                'deaths' => $data['deaths'],
                'assists' => $data['assists'],
                'statut' => $data['statut'],
                'id_match' => $id_match,
                'numero_de_licence' => $numero_de_licence,
            ]);
        }
    }


}

class JoueurManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function ajouterJoueur($data) {
        $sql = "INSERT INTO Joueur (Numéro_de_licence, Nom, Prénom, Date_de_naissance, Taille__en_cm_, Poids, Statut) 
                VALUES (:numero_licence, :nom, :prenom, :date_naissance, :taille, :poids, :statut)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    public function modifierJoueur($data) {
        $sql = "UPDATE Joueur 
                SET Nom = :nom, Prénom = :prenom, Date_de_naissance = :date_naissance, 
                    Taille__en_cm_ = :taille, Poids = :poids, Statut = :statut 
                WHERE Numéro_de_licence = :numero_licence";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    public function supprimerJoueur($numero_licence) {
        $sql = "DELETE FROM Joueur 
                WHERE Numéro_de_licence = :numero_licence 
                AND Numéro_de_licence NOT IN (SELECT DISTINCT Numéro_de_licence FROM participe)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero_licence' => $numero_licence]);
    }

    public function obtenirTousLesJoueurs($sort, $order) {
        $sql = "SELECT * FROM Joueur ORDER BY $sort $order";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenirJoueursAyantParticipe() {
        $sql = "SELECT DISTINCT Numéro_de_licence FROM participe";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}


class StatistiquesJoueurManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function obtenirMoyennesParJoueurAvecTri($sort = 'Nom_Prénom', $order = 'ASC')
    {
        $columns = ['Nom_Prénom', 'Moyenne_Note', 'Matchs_Joues'];
        if (!in_array($sort, $columns)) {
            $sort = 'Nom_Prénom';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT 
                j.Numéro_de_licence AS Numero_Licence, 
                CONCAT(j.Nom, '_', j.Prénom) AS Nom_Prénom, 
                ROUND(AVG(p.Note_apres_match), 2) AS Moyenne_Note, 
                COUNT(p.ID_Match) AS Matchs_Joues 
            FROM Participe p 
            INNER JOIN Joueur j ON p.Numéro_de_licence = j.Numéro_de_licence 
            INNER JOIN Matchs m ON p.ID_Match = m.ID_Match 
            WHERE m.Dateheure < NOW() 
            GROUP BY j.Numéro_de_licence, j.Nom, j.Prénom 
            ORDER BY $sort $order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class JoueurDetailsManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function verifierParticipation($numero_licence) {
        $sql = "SELECT 1 FROM participe WHERE Numéro_de_licence = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetch() !== false;
    }

    public function obtenirInformationsJoueur($numero_licence) {
        $sql = "SELECT * FROM Joueur WHERE Numéro_de_licence = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenirStatsVDN($numero_licence) {
        $sql = "
            SELECT 
                COUNT(*) AS total_matchs,
                SUM(CASE WHEN m.Resultat = 'Victoire' THEN 1 ELSE 0 END) AS total_gagnes,
                ROUND((SUM(CASE WHEN m.Resultat = 'Victoire' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_gagnes,
                SUM(CASE WHEN m.Resultat = 'Défaite' THEN 1 ELSE 0 END) AS total_perdus,
                ROUND((SUM(CASE WHEN m.Resultat = 'Défaite' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_perdus,
                SUM(CASE WHEN m.Resultat = 'Match nul' THEN 1 ELSE 0 END) AS total_nuls,
                ROUND((SUM(CASE WHEN m.Resultat = 'Match nul' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_nuls
            FROM Matchs m
            INNER JOIN participe p ON m.ID_Match = p.ID_Match
            WHERE p.Numéro_de_licence = ? AND m.Dateheure < NOW() AND m.Resultat IS NOT NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenirStatsAvancees($numero_licence) {
        $sql = "
            SELECT
                p.Poste AS poste_prefere,
                SUM(CASE WHEN p.Statut_titulaire_remplacant = 'Remplaçant' THEN 1 ELSE 0 END) AS total_remplacements,
                ROUND((SUM(CASE WHEN m.Resultat = 'Victoire' THEN 1 ELSE 0 END) * 100.0) / COUNT(p.ID_Match), 2) AS pourcentage_gagnes
            FROM Participe p
            INNER JOIN Matchs m ON p.ID_Match = m.ID_Match
            WHERE p.Numéro_de_licence = ? AND m.Dateheure < NOW() AND m.Resultat IS NOT NULL
            GROUP BY p.Poste
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenirTitularisations($numero_licence) {
        $sql = "
            SELECT COUNT(*) AS total_titularisations
            FROM Matchs m
            INNER JOIN participe p ON m.ID_Match = p.ID_Match
            WHERE p.Numéro_de_licence = ? AND p.Statut_titulaire_remplacant = 'Titulaire' AND m.Dateheure < NOW() AND m.Resultat IS NOT NULL
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetchColumn();
    }

    public function obtenirTitularisationsConsecutives($numero_licence) {
        $sql = "
        WITH Titularisations AS (
            SELECT
                p.Numéro_de_licence,
                m.Dateheure,
                ROW_NUMBER() OVER (PARTITION BY p.Numéro_de_licence ORDER BY m.Dateheure) AS Rang,
                DATE(m.Dateheure) - INTERVAL ROW_NUMBER() OVER (PARTITION BY p.Numéro_de_licence ORDER BY m.Dateheure) DAY AS Groupe
            FROM Participe p
            INNER JOIN Matchs m ON p.ID_Match = m.ID_Match
            WHERE p.Numéro_de_licence = ? AND p.Statut_titulaire_remplacant = 'Titulaire' AND m.Dateheure < NOW() AND m.Resultat IS NOT NULL
        ),
        GroupesTitularisations AS (
            SELECT
                Numéro_de_licence,
                Groupe,
                COUNT(*) AS Nombre_Titularisations_Consecutives
            FROM Titularisations
            GROUP BY Numéro_de_licence, Groupe
        )
        SELECT MAX(Nombre_Titularisations_Consecutives) AS max_titularisations_consecutives
        FROM GroupesTitularisations
        WHERE Numéro_de_licence = ?
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence, $numero_licence]);
        return $stmt->fetchColumn();
    }

    public function enregistrerCommentaire($commentaire) {
        $sql = "INSERT INTO note (Commentaire) VALUES (:commentaire)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['commentaire' => $commentaire]);
        return $this->db->lastInsertId();
    }

    public function associerCommentaire($numero_licence, $id_note) {
        $sql = "INSERT INTO correspondre (Numéro_de_licence, ID_Note) VALUES (:numero_licence, :id_note)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero_licence' => $numero_licence, 'id_note' => $id_note]);
    }

    public function recupererCommentaires($numero_licence) {
        $sql = "
            SELECT n.Commentaire, n.ID_Note 
            FROM note n
            INNER JOIN correspondre c ON n.ID_Note = c.ID_Note
            WHERE c.Numéro_de_licence = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numero_licence]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class MatchModifier {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupérer les informations d'un match par son ID
     *
     * @param string $id_match
     * @return array|null
     */
    public function obtenirMatchParId($id_match) {
        $sql = "SELECT Dateheure, Nom_équipe_ennemi, Lieu_de_bataille, Terrain 
                FROM matchs 
                WHERE ID_Match = :id_match";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_match' => $id_match]);
        return $stmt->fetch(PDO::FETCH_ASSOC);;
    }

    /**
     * Mettre à jour les informations d'un match
     *
     * @param string $id_match
     * @param string $dateheure
     * @param string $nom_equipe
     * @param string $lieu
     * @param string $terrain
     * @return void
     */
    public function mettreAJourMatch($id_match, $dateheure, $nom_equipe, $lieu, $terrain) {
        $sql = "
            UPDATE matchs 
            SET Dateheure = :dateheure, 
                Nom_équipe_ennemi = :nom_equipe, 
                Lieu_de_bataille = :lieu, 
                Terrain = :terrain 
            WHERE ID_Match = :id_match
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'dateheure' => $dateheure,
            'nom_equipe' => $nom_equipe,
            'lieu' => $lieu,
            'terrain' => $terrain,
            'id_match' => $id_match
        ]);
    }
}

?>
