<?php
require_once 'includes/db.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';

$labels = [];
$sales = [];

if($filter === 'daily'){
    // Récupérer les ventes pour chaque jour du mois courant
    $stmt = $pdo->prepare("SELECT DAY(created_at) as day, SUM(prix_total) as total FROM Ventes WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE()) GROUP BY DAY(created_at) ORDER BY DAY(created_at)");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($result as $row){
        $labels[] = $row['day'];
        $sales[] = $row['total'] ? (float)$row['total'] : 0;
    }
} else {
    // Récupérer les ventes pour chaque mois du courant (année)
    $stmt = $pdo->prepare("SELECT MONTH(created_at) as month, SUM(prix_total) as total FROM Ventes WHERE YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Tableau de correspondance pour les mois en abrégé en français
    $months = [1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'];
    foreach($result as $row){
        $monthNum = intval($row['month']);
        $labels[] = $months[$monthNum];
        $sales[] = $row['total'] ? (float)$row['total'] : 0;
    }
}

echo json_encode(['labels' => $labels, 'sales' => $sales]);
?> 