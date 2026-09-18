Use gsbV2;


SELECT COUNT(*) AS nb FROM FraisForfait;
SELECT COUNT(*) AS nb FROM Etat;
SELECT COUNT(*) AS nb FROM Visiteur;

SELECT f.idVisiteur, f.mois, e.libelle
FROM FicheFrais f
Join Etat e ON f.idEtat = e.id
LIMIT 5;



SELECT id, nom, prenom FROM Visiteur LIMIT 5;
