document.addEventListener('DOMContentLoaded', function() {

    var filtrJour    = document.getElementById('filtre-jour');
    var filtreSalle  = document.getElementById('filtre-salle');
    var filtreFiliere = document.getElementById('filtre-filiere');

    if (filtrJour)     filtrJour.addEventListener('change', filtrerPlanning);
    if (filtreSalle)   filtreSalle.addEventListener('change', filtrerPlanning);
    if (filtreFiliere) filtreFiliere.addEventListener('change', filtrerPlanning);

    var alertes = document.querySelectorAll('.alert-dismissible');
    alertes.forEach(function(alerte) {
        setTimeout(function() {
            alerte.style.opacity = '0';
            alerte.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alerte.style.display = 'none';
            }, 500);
        }, 3000);
    });

});

function filtrerPlanning() {
    var jour    = document.getElementById('filtre-jour')     ? document.getElementById('filtre-jour').value     : 'tous';
    var salle   = document.getElementById('filtre-salle')    ? document.getElementById('filtre-salle').value    : 'tous';
    var filiere = document.getElementById('filtre-filiere')  ? document.getElementById('filtre-filiere').value  : 'tous';

    var lignes = document.querySelectorAll('#tableau-planning tbody tr');

    lignes.forEach(function(ligne) {
        var afficher = true;

        if (jour    !== 'tous' && ligne.getAttribute('data-jour')    !== jour)    afficher = false;
        if (salle   !== 'tous' && ligne.getAttribute('data-salle')   !== salle)   afficher = false;
        if (filiere !== 'tous' && ligne.getAttribute('data-filiere') !== filiere) afficher = false;

        ligne.style.display = afficher ? '' : 'none';
    });

    var nb = document.querySelectorAll('#tableau-planning tbody tr:not([style*="none"])').length;
    var compteur = document.getElementById('compteur');
    if (compteur) compteur.textContent = nb + ' soutenance(s)';
}