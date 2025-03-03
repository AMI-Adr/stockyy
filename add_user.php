<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom   = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $role  = $_POST['role'] ?? '';
    // Définit le mot de passe par défaut "123456" en le hachant
    $mot_de_passe = password_hash('123456', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, email, role, mot_de_passe) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nom, $email, $role, $mot_de_passe])) {
        $id = $pdo->lastInsertId();

        // Retourne le code HTML de la nouvelle ligne pour l'injecter dans le tableau
        echo '<tr data-id="' . $id . '">';
        echo '<td>' . htmlspecialchars($nom) . '</td>';
        echo '<td>' . htmlspecialchars($email) . '</td>';
        echo '<td>' . htmlspecialchars($role) . '</td>';
        echo '<td>';
        echo '<button class="edit-btn" data-id="' . $id . '">✏️</button> ';
        echo '<button class="delete-btn" data-id="' . $id . '">🗑️</button>';
        echo '</td>';
        echo '</tr>';
    } else {
        http_response_code(500);
        echo "Erreur lors de l'ajout de l'utilisateur.";
    }
}
?> 