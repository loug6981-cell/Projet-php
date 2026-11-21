<?php
require_once 'inc/fonctions.php';

if (utilisateur_connecte() !== null) {
    rediriger('boite.php');
}

$erreurs  = [];
$nickname = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $nickname = trim($_POST['nickname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $mdp      = $_POST['mdp'] ?? '';
    $mdp2     = $_POST['mdp2'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $nickname)) {
        $erreurs[] = "Le pseudo doit faire 3 à 30 caractères (lettres, chiffres, - ou _).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse e-mail n'est pas valide.";
    }
    if (strlen($mdp) < 6) {
        $erreurs[] = "Le mot de passe doit faire au moins 6 caractères.";
    }
    if ($mdp !== $mdp2) {
        $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    }

    if (empty($erreurs)) {
        try {
            $dbh = connexion();

            $sth = $dbh->prepare(
                "INSERT INTO messagerie._user(nickname, email, pass)
                 VALUES(:nickname, :email, :pass)"
            );
            $hash = password_hash($mdp, PASSWORD_DEFAULT); 
            $sth->bindParam(':nickname', $nickname);
            $sth->bindParam(':email', $email);
            $sth->bindParam(':pass', $hash);
            $sth->execute();

            session_regenerate_id(true);
            
            $_SESSION['nickname'] = $nickname;
            flash("Bienvenue $nickname, votre compte est créé !");
            rediriger('boite.php');
        } catch (PDOException $e) {

        if ($e->getCode() === '23505') {
                $erreurs[] = "Ce pseudo ou cette adresse e-mail est déjà utilisé.";
            } else {
                $erreurs[] = "Erreur de la base : " . $e->getMessage();
            }
        }
    }
}

$titre = 'Inscription';
require 'inc/header.php';
?>
<section class="carte etroite">
    <h1>Créer un compte</h1>
    <?php foreach ($erreurs as $err): ?>
        <p class="flash erreur"><?= h($err) ?></p>
    <?php endforeach; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= jeton_csrf() ?>">
        <label>Pseudo
            <input type="text" name="nickname" value="<?= h($nickname) ?>" required maxlength="30">
        </label>
        <label>E-mail
            <input type="email" name="email" value="<?= h($email) ?>" required maxlength="100">
        </label>
        <label>Mot de passe
            <input type="password" name="mdp" required minlength="6">
        </label>
        <label>Confirmer le mot de passe
            <input type="password" name="mdp2" required minlength="6">
        </label>
        <button type="submit">S'inscrire</button>
    </form>
    <p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
</section>
<?php require 'inc/footer.php'; ?>
