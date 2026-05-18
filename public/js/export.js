// Document sélectionné par l'utilisateur
let docSelectionne = '';

/**
 * Appelé quand l'utilisateur clique sur une carte
 * @param {string} doc - 'affectation', 'planning', ou 'pv'
 */
function selectDoc(doc) {

    docSelectionne = doc;

    // ── Réinitialiser toutes les cartes ──────────────────
    const cartes = ['affectation', 'planning', 'pv'];
    cartes.forEach(function (c) {
        const carte = document.getElementById('card-' + c);
        const badge = document.getElementById('badge-' + c);

        // Retirer le style sélectionné
        carte.style.border = '2px solid transparent';
        carte.style.backgroundColor = '#ffffff';
        badge.style.background = '#999';
        badge.style.color = '#fff';
        badge.textContent = 'Non sélectionné';
    });

    // ── Surligner la carte sélectionnée ──────────────────
    const carteSelectionnee = document.getElementById('card-' + doc);
    const badgeSelectionne = document.getElementById('badge-' + doc);

    carteSelectionnee.style.border = '2px solid #2d6abf';
    carteSelectionnee.style.backgroundColor = '#f0f6ff';
    badgeSelectionne.style.background = '#2d6abf';
    badgeSelectionne.style.color = '#fff';
    badgeSelectionne.textContent = 'Sélectionné';

    // ── Afficher la section format ────────────────────────
    document.getElementById('format-section').style.display = 'block';

    // ── Activer le bouton ────────────────────────────────
    var btn = document.getElementById('btn-generer');
    btn.style.opacity = '1';

    // ── Logique selon le document choisi ─────────────────
    if (doc === 'pv') {
        // PVs → DOCX seulement, cacher les boutons radio
        document.getElementById('format-choices').style.display = 'none';
        document.getElementById('pv-info').style.display = 'block';
        document.getElementById('format-docx').checked = true;

        // Activer le bouton directement
        btn.disabled = false;
        btn.textContent = ' Générer & Télécharger ZIP';

    } else {
        // Affectation ou Planning → montrer le choix PDF/DOCX
        document.getElementById('format-choices').style.display = 'flex';
        document.getElementById('pv-info').style.display = 'none';

        // Décocher les radios
        document.getElementById('format-pdf').checked = false;
        document.getElementById('format-docx').checked = false;

        // Désactiver le bouton jusqu'au choix du format
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.textContent = ' Générer & Télécharger';
    }

    // Scroller vers la section format
    document.getElementById('format-section').scrollIntoView({
        behavior: 'smooth'
    });
}

/**
 * Écouter le changement de format pour activer le bouton
 */
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('input[name="format"]');
    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            var btn = document.getElementById('btn-generer');
            btn.disabled = false;
            btn.style.opacity = '1';

            // Adapter le texte du bouton selon le format
            var format = this.value.toUpperCase();
            btn.textContent = ' Générer & Télécharger ' + format;
        });
    });
});

/**
 * Appelé quand l'utilisateur clique sur "Générer & Télécharger"
 */
function generer() {

    // Vérifier qu'un document est sélectionné
    if (!docSelectionne) {
        alert('Veuillez choisir un document.');
        return;
    }

    // Récupérer le format sélectionné
    var formatRadio = document.querySelector('input[name="format"]:checked');
    if (!formatRadio) {
        alert('Veuillez choisir un format.');
        return;
    }
    var format = formatRadio.value;
    var docRoute = (docSelectionne === 'pv') ? 'pvs' : docSelectionne;

    // Afficher le spinner
    document.getElementById('spinner').style.display = 'block';
    document.getElementById('btn-generer').disabled = true;
    document.getElementById('btn-generer').textContent = ' Génération...';

    // Rediriger vers la route Laravel avec un timestamp pour éviter le cache du navigateur
    window.location.href = BASE_URL + '/export/generate/' + docRoute + '/' + format + '?t=' + new Date().getTime();
}