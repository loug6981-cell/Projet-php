<?php
session_start();

require_once __DIR__ . '/db.php';

function h($texte) {
    return htmlspecialchars((string) $texte, ENT_QUOTES, 'UTF-8');
}

function rediriger($url) {
    header("Location: $url");
    exit;
}

function utilisateur_connecte() {
    return $_SESSION['nickname'] ?? null;
}

function exiger_connexion() {
    if (utilisateur_connecte() === null) {
        flash("Connectez-vous pour accéder à votre messagerie.", 'info');
        rediriger('connexion.php');
    }
    return utilisateur_connecte();
}

function flash($message, $type = 'ok') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function lire_flash() {
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}


function jeton_csrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifier_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('Requête invalide (jeton CSRF).');
    }
}

function date_fr($timestamp) {
    return date('d/m/Y H:i', strtotime($timestamp));
}
