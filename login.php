<?php
session_start();
require_once 'includes/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token CSRF invalide !');
    }
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user;
        
        // Redirection en fonction du rôle
        if($user['role'] === 'vendeur') {
            header("Location: ventes.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "🔐 Identifiants incorrecte !";
    }
}

if(isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if(isset($_GET['logout'])): ?>
<div class="logout-message">
    <span>👋 Ravis de vous revoir !</span>
</div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - Bibliothèque Cyberpunk</title>
    <link rel="stylesheet" href="styles/cyberpunk.css">
    <link rel="stylesheet" href="styles/login.css">
    <style></style>
</head>
<body>
    <div class="login-container">
        <div class="cyber-login-box">
            <div class="login-logo">
                <img src="images/logo.png" alt="Logo">
            </div>
            
            <div class="form-container">
                <?php if($error): ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="input-group">
                        <label class="cyber-label">📧 Email</label>
                        <input type="email" name="email" class="cyber-input" placeholder="example@gmail.com" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="cyber-label">🔑 Mot de passe</label>
                        <input type="password" name="password" class="cyber-input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="cyber-button-login">
                        <span class="btn-content">🔓 Connexion</span>
                    </button>
                    <br>
                    <br>
                    <div class="login-link">
                        Pas de compte? <a href="register.php">Créer un compte</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 