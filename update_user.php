<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données envoyées depuis le formulaire
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Vérification minimale des données
    if (empty($id) || empty($nom) || empty($email) || empty($role)) {
        http_response_code(400);
        echo "Requête invalide : Des champs sont manquants.";
        exit;
    }

    try {
        // Mise à jour de l'utilisateur dans la table Utilisateur
        $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$nom, $email, $role, $id]);

        // Récupération de l'utilisateur mis à jour
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Génération dynamique du code HTML pour la ligne utilisateur
            ob_start();
            ?>
            <tr data-id="<?= htmlspecialchars($user['id']) ?>">
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <button class="edit-btn" data-id="<?= htmlspecialchars($user['id']) ?>">✏️</button>
                    <button class="delete-btn" data-id="<?= htmlspecialchars($user['id']) ?>">🗑️</button>
                </td>
            </tr>
            <?php
            $row = ob_get_clean();
            echo $row;
        } else {
            http_response_code(404);
            echo "Utilisateur non trouvé.";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Erreur de base de données : " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Méthode non autorisée.";
} 