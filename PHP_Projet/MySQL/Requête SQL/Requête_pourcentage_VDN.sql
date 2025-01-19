use t1;
SELECT 
    COUNT(*) AS total_matchs,
    SUM(CASE WHEN Resultat = 'Victoire' THEN 1 ELSE 0 END) AS total_gagnés,
    ROUND((SUM(CASE WHEN Resultat = 'Victoire' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_gagnés,
    SUM(CASE WHEN Resultat = 'Défaite' THEN 1 ELSE 0 END) AS total_perdus,
    ROUND((SUM(CASE WHEN Resultat = 'Défaite' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_perdus,
    SUM(CASE WHEN Resultat = 'Match nul' THEN 1 ELSE 0 END) AS total_nuls,
    ROUND((SUM(CASE WHEN Resultat = 'Match nul' THEN 1 ELSE 0 END) * 100.0) / COUNT(*), 2) AS pourcentage_nuls
FROM Matchs;