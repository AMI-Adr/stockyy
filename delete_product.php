<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    if ($id != '') {
        $stmt = $pdo->prepare("DELETE FROM Produit WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "Produit supprimé";
        } else {
            http_response_code(500);
            echo "Erreur lors de la suppression";
        }
    } else {
        http_response_code(400);
        echo "ID manquant";
    }
}
?> 