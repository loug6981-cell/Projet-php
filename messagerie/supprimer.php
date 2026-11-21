<?php
require_once 'inc/fonctions.php';
$moi = exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rediriger('boite.php');
}
verifier_csrf();

$id  = (int) ($_POST['id'] ?? 0);
$dbh = connexion();


$sth = $dbh->prepare(
    "UPDATE messagerie._message SET deleted_by_recipient = true
      WHERE iddoc = :id AND recipient = :moi"
);
$sth->execute([':id' => $id, ':moi' => $moi]);
$nb = $sth->rowCount();
$dossier = 'recus';

if ($nb === 0) {

$sth = $dbh->prepare(
        "UPDATE messagerie._message SET deleted_by_author = true
          WHERE iddoc = :id
            AND iddoc IN (SELECT iddoc FROM messagerie._document WHERE author = :moi)"
    );
    $sth->execute([':id' => $id, ':moi' => $moi]);
    $nb = $sth->rowCount();
    $dossier = 'envoyes';
}

if ($nb > 0) {
    flash("Message supprimé.");
} else {
    flash("Impossible de supprimer ce message.", 'erreur');
}
rediriger("boite.php?dossier=$dossier");
