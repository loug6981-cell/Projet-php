<?php
require_once 'inc/fonctions.php';

if (utilisateur_connecte() !== null) {
    rediriger('boite.php');
}

$erreur   = null;
$nickname = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifier_csrf();
    $nickname = trim($_POST['nickname'] ?? '');
    
    $mdp      = $_POST['mdp'] ?? '';

    $dbh = connexion();
    $sth = $dbh->prepare("SELECT nickname, pass FROM messagerie._user WHERE nickname = ?");

    $sth->execute([$nickname]);
    $user = $sth->fetch(); 

    if ($user !== false && password_verify($mdp, $user['pass'])) {

        session_regenerate_id(true); 

        $_SESSION['nickname'] = $user['nickname'];

        rediriger('boite.php');
    }
    
    $erreur = "Pseudo ou mot de passe incorrect.";
}

$titre = 'Connexion';
require 'inc/header.php';
?>
<section class="carte etroite">
    <h1>Connexion</h1>
    <?php if ($erreur !== null): ?>
        <p class="flash erreur"><?= h($erreur) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= jeton_csrf() ?>">
        <label>Pseudo
            <input type="text" name="nickname" value="<?= h($nickname) ?>" required autofocus>
        </label>
        <label>Mot de passe
            <input type="password" name="mdp" required>
        </label>
        <button type="submit">Se connecter</button>
    </form>
    <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
    <p class="discret">Comptes de test : Laurent, Gilbert, Benoit (mot de passe : azerty)</p>
</section>
<?php require 'inc/footer.php'; ?>
