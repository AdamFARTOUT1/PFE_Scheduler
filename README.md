# 🎓 PFE Scheduler - ENSA Al Hoceima

**PFE Scheduler** est une application web développée sous Laravel permettant la gestion complète, la planification et l'exportation des soutenances de Projets de Fin d'Études (PFE) pour l'École Nationale des Sciences Appliquées (ENSA) d'Al Hoceima.

L'objectif principal est d'automatiser l'attribution des jurys, la répartition des salles et la planification horaire tout en respectant de multiples contraintes pédagogiques et logistiques.

---

## 🚀 Fonctionnalités Principales

### 1. 📥 Importation des données
* Importation centralisée via un fichier **Excel unifié**.
* Extraction automatique des listes :
    * **Étudiants** (Nom, Prénom, Filière, Langue de soutenance).
    * **Professeurs** (Nom, Prénom, Spécialité, quotas d'encadrement).
    * **Salles** (Locaux disponibles pour les soutenances).

### 2. 📊 Tableau de Bord (Dashboard)
* Vue d'ensemble interactive et visuelle de l'état du système.
* Statistiques en temps réel (nombre total d'étudiants, professeurs, soutenances, etc.).
* Répartition graphique des étudiants par filière et charge de soutenance par professeur.

### 3. 🗓️ Génération du Planning
* Algorithme de génération automatique des soutenances.
* **Assignation intelligente des jurys** : 
  * Attribution d'un président de jury et d'examinateurs.
  * Respect de la langue (ex: jurys anglophones pour les soutenances en anglais).
  * Prise en compte de la spécialité du sujet.
* Planification sur plusieurs jours avec des créneaux horaires fixes.
* Filtrage dynamique des plannings (par date, filière, professeur ou salle) directement sur l'interface.

### 4. 🔍 Vérification des Contraintes
* Moteur de contrôle pour s'assurer de la validité du planning généré :
  * **Erreurs critiques (Bloquantes)** : Conflits de salles (double réservation au même moment), conflits de professeurs (un prof dans deux jurys en même temps).
  * **Avertissements (Warnings)** : Dépassement de la charge maximale pour un professeur, pauses insuffisantes entre deux soutenances, etc.

### 5. 📤 Exportation Documentaire Automatisée
Génération de documents officiels au format souhaité :
* **Planning Global** : Exportation du calendrier complet (Format **PDF** ou **Excel XLSX** générant une feuille par jour).
* **Affectations** : Liste des étudiants attribués à chaque encadrant (Format **PDF** ou **Word DOCX**).
* **PVs de Soutenance** : Fiches d'évaluation (notes, critères, signatures) personnalisées pour chaque étudiant. Générées au format **Word DOCX** et regroupées dans une archive **ZIP**.

---

## 🛠️ Stack Technique

* **Framework Backend** : Laravel 11 (PHP)
* **Base de données** : MySQL / SQLite (via Eloquent ORM)
* **Frontend** : Blade Templates, HTML5, CSS3, JavaScript Vanilla
* **Librairies d'export** :
  * `phpoffice/phpspreadsheet` : Manipulation et génération de fichiers Excel.
  * `phpoffice/phpword` : Création de documents Microsoft Word (DOCX).
  * `barryvdh/laravel-dompdf` : Génération de documents PDF à partir de vues HTML.

---

## ⚙️ Installation & Configuration

### Prérequis
* PHP >= 8.2
* Composer
* Node.js & NPM (pour le build des assets si nécessaire)
* Base de données (MySQL)
* Extension PHP `zip` activée (pour l'export des PVs).

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/AdamFARTOUT1/PFE_Scheduler.git
   cd PFE_Scheduler
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration de l'environnement**
   Copiez le fichier d'exemple et configurez votre base de données :
   ```bash
   cp .env.example .env
   # Éditez le fichier .env pour y ajouter vos identifiants de base de données
   ```

4. **Générer la clé d'application**
   ```bash
   php artisan key:generate
   ```

5. **Exécuter les migrations et les seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Lancer le serveur de développement**
   ```bash
   php artisan serve
   ```
   *L'application sera accessible sur `http://127.0.0.1:8000`*

---

## 📂 Format du Fichier d'Importation Excel

Pour que l'importation fonctionne correctement, le fichier Excel unifié doit obligatoirement contenir **3 feuilles** nommées comme suit :

1. **Salles**
   * Colonne A : Nom (ex: Salle 1, Amphi A)
   * Colonne B : Type (Salle / Amphi)
2. **Professeurs**
   * Colonne B : Nom
   * Colonne C : Prénom
   * Colonne D : Spécialité
3. **Étudiants**
   * Colonne A : Nom
   * Colonne B : Prénom
   * Colonne C : Filière (ex: TDIA, ID)
   * Colonne D : Langue (FR / AN)

---

## 👨‍💻 Contribution
Développé dans le cadre universitaire par Adam FARTOUT,Mohamed BOUHASFOUR, Hiba KHARRATA, Khadija SAYOUKH sous la supervision de Monsieur Mohamed CHERRADI.

