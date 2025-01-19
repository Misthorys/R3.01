SELECT
    j.Numéro_de_licence AS joueur_id,
    j.Statut AS statut_actuel,
    p.Poste AS poste_préféré,
    SUM(CASE WHEN p.Statut_titulaire_remplacant = 'Titulaire' THEN 1 ELSE 0 END) AS total_selections_titulaires,
    SUM(CASE WHEN p.Statut_titulaire_remplacant = 'Remplaçant' THEN 1 ELSE 0 END) AS total_selections_remplacants,
    ROUND(AVG(p.Evaluation_Après_match), 2) AS moyenne_evaluations,
    ROUND((SUM(CASE WHEN m.Resultat = 'Gagné' THEN 1 ELSE 0 END) * 100.0) / COUNT(p.ID_Match), 2) AS pourcentage_matchs_gagnes
FROM
    Joueur j
        JOIN
    Participe p ON j.Numéro_de_licence = p.Numéro_de_licence
        JOIN
    Matchs m ON p.ID_Match = m.ID_Match
GROUP BY
    j.Numéro_de_licence, j.Statut, p.Poste;
