<?php
// --- CONFIGURARE DB ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    die(json_encode(['eroare' => 'Eroare conexiune: ' . $e->getMessage()])); 
}

// Setăm formatul de răspuns ca fiind JSON (pentru a fi citit ușor de JavaScript)
header('Content-Type: application/json');

$actiune = $_GET['actiune'] ?? '';

// PASUL 1: Aducem toate Mărcile
if ($actiune == 'get_marci') {
    $stmt = $pdo->query("SELECT DISTINCT marca FROM masini ORDER BY marca ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
}
// PASUL 2: Aducem Modelele (în funcție de Marcă)
elseif ($actiune == 'get_modele' && isset($_GET['marca'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT model FROM masini WHERE marca = ? ORDER BY model ASC");
    $stmt->execute([$_GET['marca']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
}
// PASUL 3: Aducem Anii de fabricație (în funcție de Marcă și Model)
elseif ($actiune == 'get_ani' && isset($_GET['marca']) && isset($_GET['model'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT an_fabricatie FROM masini WHERE marca = ? AND model = ? ORDER BY an_fabricatie DESC");
    $stmt->execute([$_GET['marca'], $_GET['model']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
}
// PASUL 4: Aducem Combustibilul (în funcție de primele 3)
elseif ($actiune == 'get_combustibil' && isset($_GET['marca']) && isset($_GET['model']) && isset($_GET['an'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT combustibil FROM masini WHERE marca = ? AND model = ? AND an_fabricatie = ? ORDER BY combustibil ASC");
    $stmt->execute([$_GET['marca'], $_GET['model'], $_GET['an']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
}
// PASUL 5: Aducem Motorizările / Caii Putere (Aici luăm și ID-ul mașinii)
elseif ($actiune == 'get_motoare' && isset($_GET['marca']) && isset($_GET['model']) && isset($_GET['an']) && isset($_GET['combustibil'])) {
    $stmt = $pdo->prepare("SELECT id, motorizare FROM masini WHERE marca = ? AND model = ? AND an_fabricatie = ? AND combustibil = ? ORDER BY motorizare ASC");
    $stmt->execute([$_GET['marca'], $_GET['model'], $_GET['an'], $_GET['combustibil']]);
    echo json_encode($stmt->fetchAll());
}
else {
    // Dacă nu recunoaște comanda
    echo json_encode(['eroare' => 'Actiune invalida sau parametri lipsa']);
}
?>