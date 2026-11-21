<?php
require_once 'inc/fonctions.php';
$moi = exiger_connexion();

$dbh = connexion();
$erreurs = [];


$destinataire = trim($_POST['destinataire'] ?? $_GET['a'] ?? '');
$sujet        = trim($_POST['sujet'] ?? $_GET['sujet'] ?? '');
$contenu      = trim($_POST['contenu'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();

    if ($destinataire === '') {
        $erreurs[] = "Indiquez un destinataire.";
    }
    if ($sujet === '' || mb_strlen($sujet) > 100) {
        $erreurs[] = "Le sujet est obligatoire (100 caractères max).";
    }
    if ($contenu === '') {
        $erreurs[] = "Le message est vide.";
    }

    if (empty($erreurs)) {


        try {
            $dbh->beginTransaction();

            $sth = $dbh->prepare(
                "INSERT INTO messagerie._document(content, author)
                 VALUES (:content, :author) RETURNING iddoc"
            );
            $sth->bindParam(':content', $contenu);
            $sth->bindParam(':author', $moi);
            $sth->execute();
            $iddoc = $sth->fetch()['iddoc'];

            $sth2 = $dbh->prepare(
                "INSERT INTO messagerie._message(iddoc, subject, recipient)
                 VALUES (:iddoc, :subject, :recipient)"
            );
            $sth2->bindParam(':iddoc', $iddoc);
            $sth2->bindParam(':subject', $sujet);
            $sth2->bindParam(':recipient', $destinataire);
            $sth2->execute();

            $dbh->commit();

            flash("Message envoyé à $destinataire.");
            rediriger('boite.php?dossier=envoyes');
        } catch (PDOException $e) {
            if ($dbh->inTransaction()) {
                $dbh->rollBack();
            }
            switch ($e->getCode()) {
                case 'MS001': 

                    $erreurs[] = "Vous ne pouvez pas vous envoyer un message à vous-même.";
                    break;
                case '23503':

                    $erreurs[] = "L'utilisateur « $destinataire » n'existe pas.";
                    break;

                default:
                    $erreurs[] = "Erreur lors de l'envoi : " . $e->getMessage();
            }
        }
    }
}

$sth = $dbh->prepare("SELECT nickname FROM messagerie._user WHERE nickname <> :moi ORDER BY nickname");
$sth->execute([':moi' => $moi]);
$utilisateurs = $sth->fetchAll(PDO::FETCH_COLUMN);

$titre = 'Nouveau message';
require 'inc/header.php';
?>
<section class="carte">
    <h1>Nouveau message</h1>
    <?php foreach ($erreurs as $err): ?>
        <p class="flash erreur"><?= h($err) ?></p>
    <?php endforeach; ?>

    <form method="post" action="ecrire.php">
        <input type="hidden" name="csrf" value="<?= jeton_csrf() ?>">
        <label>À
            <input type="text" name="destinataire" list="liste-utilisateurs"
                   value="<?= h($destinataire) ?>" required autocomplete="off">
            <datalist id="liste-utilisateurs">
                <?php foreach ($utilisateurs as $u): ?>
                    <option value="<?= h($u) ?>">
                <?php endforeach; ?>
            </datalist>
        </label>
        <label>Sujet
            <input type="text" name="sujet" value="<?= h($sujet) ?>" required maxlength="100">
        </label>
        <label>Message
            <textarea name="contenu" rows="8" required><?= h($contenu) ?></textarea>
        </label>
        <button type="submit">Envoyer</button>
    </form>
</section>
<?php require 'inc/footer.php'; ?>
