<?php

function connexion() {
    require __DIR__ . '/../connect_params.php';
    try {
        $dbh = new PDO("$driver:host=$server;dbname=$dbname", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $dbh;
    } catch (PDOException $e) {
        die("Impossible de se connecter à la base : " . htmlspecialchars($e->getMessage()));
    }
}
