WITH Selection_Ordre AS (
    SELECT
        p.Numéro_de_licence AS joueur_id,
        p.ID_Match,
        m.Dateheure,
        ROW_NUMBER() OVER (PARTITION BY p.Numéro_de_licence ORDER BY m.Dateheure) AS rang
    FROM
        Participe p
            JOIN
        Matchs m ON p.ID_Match = m.ID_Match
),
     Selection_Consecutive AS (
         SELECT
             joueur_id,
             COUNT(*) AS nb_selections_consecutives
         FROM (
                  SELECT
                      joueur_id,
                      (ROW_NUMBER() OVER (PARTITION BY joueur_id ORDER BY Dateheure)
                          - rang) AS difference
                  FROM
                      Selection_Ordre
              ) AS Diff
         GROUP BY
             joueur_id, difference
         ORDER BY
             joueur_id, nb_selections_consecutives DESC
     )
SELECT
    joueur_id,
    MAX(nb_selections_consecutives) AS max_selections_consecutives
FROM
    Selection_Consecutive
GROUP BY
    joueur_id;
