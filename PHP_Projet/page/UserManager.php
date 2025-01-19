<?php

class UserManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Vérifie si un nom d'utilisateur existe déjà
    public function utilisateurExiste($nom_utilisateur) {
        $sql = "SELECT 1 FROM Utilisateur WHERE nom_utilisateur = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_utilisateur]);
        return $stmt->fetch() ? true : false;
    }

    // Insère un nouvel utilisateur dans la base de données
    public function creerUtilisateur($nom_utilisateur, $mot_de_passe_hash) {
        $sql = "INSERT INTO Utilisateur (nom_utilisateur, Mot_de_passe) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nom_utilisateur, $mot_de_passe_hash]);
    }
}

?>
