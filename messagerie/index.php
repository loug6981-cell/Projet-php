<?php
require_once 'inc/fonctions.php';

if (utilisateur_connecte() !== null) {
    rediriger('boite.php');
}
rediriger('connexion.php');
