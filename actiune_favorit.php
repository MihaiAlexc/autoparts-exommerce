<?php
session_start();
header('Content-Type: application/json');

// 1. Verificăm dacă clientul este logat
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['status' => 'neautentificat', 'mesaj' => 'Trebuie să fii logat pentru a adăuga la favorite!']);
    exit();
}

// 2. Conectare la baza de date
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) { 
    echo json_encode(['status' => 'eroare', 'mesaj' => 'Eroare conexiune.']);
    exit();
}

$id_client = $_SESSION['client_id'];
$id_produs = isset($_POST['id_produs']) ? (int)$_POST['id_produs'] : 0;

if ($id_produs > 0) {
    // Verificăm dacă piesa este deja în favoritele acestui client
    $stmt = $pdo->prepare("SELECT id FROM favorite WHERE id_client = ? AND id_produs = ?");
    $stmt->execute([$id_client, $id_produs]);
    $exista = $stmt->fetch();

    if ($exista) {
        // Dacă există deja, o ȘTERGEM (Apasă pe inimioară ca să scoată de la favorite)
        $stmt_delete = $pdo->prepare("DELETE FROM favorite WHERE id_client = ? AND id_produs = ?");
        $stmt_delete->execute([$id_client, $id_produs]);
        echo json_encode(['status' => 'success', 'actiune' => 'sters']);
    } else {
        // Dacă nu există, o ADĂUGĂM
        $stmt_insert = $pdo->prepare("INSERT INTO favorite (id_client, id_produs) VALUES (?, ?)");
        $stmt_insert->execute([$id_client, $id_produs]);
        echo json_encode(['status' => 'success', 'actiune' => 'adaugat']);
    }
} else {
    echo json_encode(['status' => 'eroare', 'mesaj' => 'Produs invalid.']);
}
?>