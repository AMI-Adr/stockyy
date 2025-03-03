<?php
session_start();
require_once 'includes/db.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    try{
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user){
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Utilisateur non trouvé"]);
        }
    } catch(PDOException $e){
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "ID manquant"]);
}
?> 