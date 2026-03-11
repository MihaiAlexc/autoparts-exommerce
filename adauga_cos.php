<?php
session_start();

// Dacă am primit o cerere prin POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_produs = isset($_POST['id_produs']) ? (int)$_POST['id_produs'] : 0;
    $cantitate = isset($_POST['cantitate']) ? (int)$_POST['cantitate'] : 1;

    if ($id_produs > 0 && $cantitate > 0) {
        
        if (!isset($_SESSION['cos'])) {
            $_SESSION['cos'] = [];
        }

        if (isset($_SESSION['cos'][$id_produs])) {
            $_SESSION['cos'][$id_produs] += $cantitate;
        } else {
            $_SESSION['cos'][$id_produs] = $cantitate;
        }
    }

    // Calculăm câte produse sunt în total în coș acum
    $total_produse = array_sum($_SESSION['cos']);

    // Răspundem în format JSON (limbajul pe care îl înțelege AJAX-ul)
    echo json_encode(['status' => 'success', 'total_produse' => $total_produse]);
    exit();
}
?>