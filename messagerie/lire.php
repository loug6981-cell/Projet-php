<?php
require_once 'inc/fonctions.php';
$moi = exiger_connexion();

$id = (int) ($_GET['id'] ?? 0);

$dbh = connexion();

$sth = $dbh->prepare(
    "SELECT * FROM messagerie.message
      WHERE iddoc = :id
        AND (   (recipient = :moi AND NOT deleted_by_recipient)
             OR (author    = :moi AND NOT deleted_by_author))"
);
$sth->execute([':id' => $id, ':moi' => $moi]);
$message = $sth->fetch();

if ($message === false) {
    flash("Ce message n'existe pas ou ne vous est pas destiné.", 'erreur');
    rediriger('boite.php');
}

$je_suis_destinataire = ($message['recipient'] === $moi);


if ($je_suis_destinataire && !$message['is_read']) {
    $upd = $dbh->prepare("UPDATE messagerie._message SET is_read = true WHERE iddoc = :id");
    $upd->execute([':id' => $id]);
}

$sujet_reponse = $message['subject'];

if (stripos($sujet_reponse, 'Re:') !== 0) {

    $sujet_reponse = 'Re: ' . $sujet_reponse;
}
$lien_reponse = 'ecrire.php?' . http_build_query([

    'a'     => $message['author'],
    'sujet' => mb_substr($sujet_reponse, 0, 100),
]);

$titre = $message['subject'];
require 'inc/header.php';
?>
<section class="carte">
    <p><a href="boite.php?dossier=<?= $je_suis_destinataire ? 'recus' : 'envoyes' ?>">← Retour</a></p>
    <h1><?= h($message['subject']) ?></h1>
    <p class="meta">
        De <strong><?= h($message['author']) ?></strong>
        à <strong><?= h($message['recipient']) ?></strong>
        - <?= date_fr($message['create_date']) ?>
    </p>
    <div class="contenu"><?= nl2br(h($message['content'])) ?></div>

    <div class="actions">

        <?php if ($je_suis_destinataire): ?>
            <a class="bouton" href="<?= h($lien_reponse) ?>">Répondre</a>
        <?php endif; ?>

        <form method="post" action="supprimer.php" onsubmit="return confirm('Supprimer ce message ?');">
            
            <input type="hidden" name="csrf" value="<?= jeton_csrf() ?>">
            <input type="hidden" name="id" value="<?= (int) $message['iddoc'] ?>">
            <button type="submit" class="danger">Supprimer</button>
        </form>
    </div>
</section>
<?php require 'inc/footer.php'; ?>
