<?php
require_once 'inc/fonctions.php';
$moi = exiger_connexion();

$dossier = $_GET['dossier'] ?? 'recus';
if ($dossier !== 'envoyes') {
    $dossier = 'recus';
}

$dbh = connexion();

if ($dossier === 'recus') {
    $sql = "SELECT iddoc, subject, author AS correspondant, create_date, is_read
              FROM messagerie.message
             WHERE recipient = :moi AND NOT deleted_by_recipient
             ORDER BY create_date DESC";
} else {
    $sql = "SELECT iddoc, subject, recipient AS correspondant, create_date, is_read
              FROM messagerie.message
             WHERE author = :moi AND NOT deleted_by_author
             ORDER BY create_date DESC";
}
$sth = $dbh->prepare($sql);
$sth->execute([':moi' => $moi]);
$messages = $sth->fetchAll();

$titre = $dossier === 'recus' ? 'Messages reçus' : 'Messages envoyés';
require 'inc/header.php';
?>
<section class="carte">
    <h1><?= h($titre) ?></h1>

    <?php if (empty($messages)): ?>
        <p class="discret">Aucun message pour l'instant.</p>
    <?php else: ?>
        <table class="liste">
            <thead>
                <tr>
                    <th><?= $dossier === 'recus' ? 'De' : 'À' ?></th>
                    <th>Sujet</th>
                    <th>Date</th>
                    <?php if ($dossier === 'envoyes'): ?><th>Statut</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $m): ?>
                <?php $non_lu = ($dossier === 'recus' && !$m['is_read']); ?>
                <tr class="<?= $non_lu ? 'non-lu' : '' ?>">
                    <td><?= h($m['correspondant']) ?></td>
                    <td><a href="lire.php?id=<?= (int) $m['iddoc'] ?>"><?= h($m['subject']) ?></a></td>
                    <td><?= date_fr($m['create_date']) ?></td>
                    <?php if ($dossier === 'envoyes'): ?>
                        <td><?= $m['is_read'] ? 'Lu' : 'Non lu' ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require 'inc/footer.php'; ?>
