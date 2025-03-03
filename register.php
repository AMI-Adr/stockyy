<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $password, $role]);
        header("Location: login.php?registered=1");
    } catch(PDOException $e) {
        $error = "Erreur d'inscription : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>👥 Inscription</title>
    <link rel="stylesheet" href="styles/cyberpunk.css">
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="login-container">
        <div class="cyber-login-box">
            
            <div class="form-container">
                <?php if(isset($error)): ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label class="cyber-label">👤 Nom</label>
                        <input type="text" name="nom" class="cyber-input" placeholder="John Doe" required>
                    </div>

                    <div class="input-group">
                        <label class="cyber-label">📧 Email</label>
                        <input type="email" name="email" class="cyber-input" placeholder="example@gmail.com" required>
                    </div>

                    <div class="input-group">
                        <label class="cyber-label">🔑 Mot de passe</label>
                        <input type="password" name="password" class="cyber-input" placeholder="••••••••" required>
                    </div>

                    <div class="input-group">
                        <label class="cyber-label">🎚️ Rôle</label>
                        <select name="role" class="cyber-input" required>
                            <option value="admin">Administrateur</option>
                            <option value="vendeur">Vendeur</option>
                        </select>
                    </div>

                    <button type="submit" class="cyber-button-login">
                        <span class="btn-content">🚀 S'inscrire</span>
                    </button>
                    <br><br>
                    <div class="login-link">
                        Déjà inscrit? <a href="login.php">Se connecter</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 