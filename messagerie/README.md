messagerie/
├── connect_params.php    identifiants PostgreSQL
├── index.php             redirige vers la boîte ou la connexion
├── inscription.php       INSERT avec bindParam, gestion du code 23505
├── connexion.php         SELECT préparé (marqueur ?), password_verify, session
├── deconnexion.php       session_unset() + session_destroy()
├── boite.php             ?dossier=recus|envoyes, lecture de la VUE, fetchAll
├── lire.php              affichage + UPDATE (marquer comme lu)
├── ecrire.php            TRANSACTION + RETURNING + codes d'erreur
├── supprimer.php         suppression logique (UPDATE), rowCount()
├── style.css
├── inc/
│   ├── db.php            fonction connexion() : PDO, ERRMODE_EXCEPTION, FETCH_ASSOC
│   ├── fonctions.php     session_start
│   ├── header.php        appelle la fonction PL/pgSQL nb_non_lus()
│   └── footer.php
└── sql/
    └── messagerie.sql    tables, triggers, fonction, vue, jeu d'essai

<!-- voila la structure des fichiers :D -->