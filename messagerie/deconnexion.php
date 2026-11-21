<?php
require_once 'inc/fonctions.php';


session_unset();
session_destroy();


session_start();
flash("Vous êtes déconnecté. À bientôt !", 'info');
rediriger('connexion.php');
