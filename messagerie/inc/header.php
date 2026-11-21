<?php
$moi = utilisateur_connecte();
$nb_non_lus = 0;
if ($moi !== null) {
    $dbh = $dbh ?? connexion();
    $sth = $dbh->prepare("SELECT messagerie.nb_non_lus(:nick) AS nb");
    $sth->execute([':nick' => $moi]);
    $nb_non_lus = $sth->fetch()['nb'];
}
$flash = lire_flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titre ?? 'Messagerie') ?> - Messagerie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a class="logo" href="index.php">Messagerie</a>
    <nav>
    <?php if ($moi !== null): ?>
        <a href="boite.php?dossier=recus">Reçus<?php if ($nb_non_lus > 0): ?> <span class="badge"><?= $nb_non_lus ?></span><?php endif; ?></a>
        <a href="boite.php?dossier=envoyes">Envoyés</a>
        <a class="bouton" href="ecrire.php">Écrire</a>
        <span class="moi"><?= h($moi) ?></span>
        <a href="deconnexion.php">Déconnexion</a>
    <?php else: ?>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php">Inscription</a>
    <?php endif; ?>
    </nav>
</header>
<main>
<?php if ($flash !== null): ?>
    <p class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></p>
<?php endif; ?>
