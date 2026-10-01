<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Espace Comptabilité</title>
    <link rel="stylesheet" href="compta.css">
    <favicon href="images/logo.ico" />
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="images/logo.png" alt="Logo GSB" class="logo">
            <h1>GSB - Comptabilité</h1>
        </div>
        <div class="header-actions">
            <a href="index.php" class="deconnexion">Déconnexion</a>
        </div>
    </div>
    <div class="compta-content">
        <div class="selection">
            <label for="nom-select">Nom :</label>
            <select id="nom-select" aria-label="Sélectionner un nom">
                <option value="">-- Sélectionner un nom --</option>
                <option value="Dupont">Dupont</option>
                <option value="Martin">Martin</option>
                <option value="Leroy">Leroy</option>
                <option value="Durand">Durand</option>
            </select>

            <label for="mois-select">Période :</label>
            <select id="mois-select" aria-label="Sélectionner une période">
                <option value="">-- Sélectionner mois/année --</option>
                <option value="Janv 2026">Janv 2026</option>
                <option value="Fév 2026">Fév 2026</option>
                <option value="Mars 2026">Mars 2026</option>
                <option value="Avr 2026">Avr 2026</option>
                <option value="Mai 2026">Mai 2026</option>
                <option value="Juin 2026">Juin 2026</option>
                <option value="Juil 2026">Juil 2026</option>
                <option value="Août 2026">Août 2026</option>
                <option value="Sept 2026">Sept 2026</option>
                <option value="Oct 2026">Oct 2026</option>
                <option value="Nov 2026">Nov 2026</option>
                <option value="Déc 2026">Déc 2026</option>
            </select>

            <div class="selection-actions">
                <button id="btn-saisie" type="button" onclick="goTo('saisie')">Nouvelle saisie</button>
                <button id="btn-consult" type="button" onclick="goTo('consult')">Consulter</button>
            </div>
        </div>
        <div class="info">
            <table>
                <thead>
                    <tr>
                        <th>Frais</th>
                        <th>Montant</th>
                        <th>Etat</th>
                    </tr>
                </thead>
                <tbody id="frais-tbody">
                    <tr class="empty-row">
                        <td colspan="3" class="empty">Aucune sélection — sélectionnez un nom et une période.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="commentaire">
            <h2>Commentaires</h2>
            <textarea rows="4" cols="50" placeholder="Ajouter un commentaire..."></textarea>
        </div>
        <div class="actions">
            <button>Valider</button>
        </div>
    </div>
    <div class="footer">
        <p>&copy; 2026 GSB. Tous droits réservés.</p>
        <a href="mentions.php">Mentions légales</a>
    </div>



    <script>
    // Redirige vers la page choisie en ajoutant nom et période comme paramètres
    function goTo(action) {
        const name = document.getElementById('nom-select').value;
        const period = document.getElementById('mois-select').value;
        if (!name) { alert('Veuillez sélectionner un nom.'); return; }
        if (!period) { alert('Veuillez sélectionner une période.'); return; }
        let url = (action === 'saisie') ? 'saisie_frais.html' : 'consultation_frais.html';
        url += `?nom=${encodeURIComponent(name)}&periode=${encodeURIComponent(period)}`;
        location.href = url;
    }

    // Exemple de données factices (sera remplacé par la BDD plus tard)
    const sampleData = {
        'Dupont|Janv 2026': [
            { frais: 'Transport', montant: '45,20 €', etat: 'Validé' },
            { frais: 'Repas', montant: '12,50 €', etat: 'En attente' }
        ],
        'Martin|Janv 2026': [
            { frais: 'Hôtel', montant: '120,00 €', etat: 'Validé' }
        ],
        'Leroy|Fév 2026': [
            { frais: 'Transport', montant: '30,00 €', etat: 'Refusé' }
        ]
    };

    function clearTable() {
        const tbody = document.getElementById('frais-tbody');
        tbody.innerHTML = '<tr class="empty-row"><td colspan="3" class="empty">Aucune sélection — sélectionnez un nom et une période.</td></tr>';
    }

    function updateTable() {
        const name = document.getElementById('nom-select').value;
        const period = document.getElementById('mois-select').value;
        const tbody = document.getElementById('frais-tbody');

        if (!name || !period) {
            clearTable();
            return;
        }

        const key = `${name}|${period}`;
        const rows = sampleData[key] || [];
        if (rows.length === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="3" class="empty">Aucun frais trouvé pour cette sélection.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        for (const r of rows) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.frais}</td><td>${r.montant}</td><td>${r.etat}</td>`;
            tbody.appendChild(tr);
        }
    }

    // Liaison des événements
    document.getElementById('nom-select').addEventListener('change', updateTable);
    document.getElementById('mois-select').addEventListener('change', updateTable);

    // Changer le comportement du bouton Consulter pour mettre à jour le tableau localement
    document.getElementById('btn-consult').addEventListener('click', (e) => { e.preventDefault(); updateTable(); });
    </script>

</body>
</html>