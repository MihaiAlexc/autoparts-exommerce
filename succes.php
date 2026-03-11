<?php
session_start();

// Preluăm ID-ul comenzii din URL (dacă există)
$id_comanda = isset($_GET['id_comanda']) ? $_GET['id_comanda'] : null;

// Dacă cineva intră pe pagina asta direct, fără să fi dat o comandă, îl trimitem pe prima pagină
if (!$id_comanda) {
    header("Location: index.php");
    exit();
}

// Coșul ar trebui să fie gol acum, dar păstrăm logica pentru header
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandă plasată cu succes! - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Success Box */
        .success-container { max-width: 600px; margin: 60px auto; text-align: center; }
        .success-card { background: white; border-radius: 15px; padding: 50px 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
        
        .check-icon-wrap { width: 100px; height: 100px; background-color: #e8f5e9; color: #27ae60; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 25px auto; }
        
        .order-number { background: #f8f9fa; border: 1px dashed #ced4da; padding: 10px 20px; border-radius: 8px; display: inline-block; font-size: 1.2rem; margin: 20px 0; color: #495057; }
        
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>


<div class="container flex-grow-1">
    <div class="success-container">
        <div class="success-card">
            
            <div class="check-icon-wrap">
                <i class="fas fa-check"></i>
            </div>
            
            <h2 class="fw-bold text-dark mb-3">Mulțumim pentru comandă!</h2>
            <p class="text-muted fs-5 mb-0">Comanda ta a fost înregistrată cu succes și se află în procesare.</p>
            
            <div class="order-number">
                Număr comandă: <strong class="text-dark">#<?= htmlspecialchars($id_comanda) ?></strong>
            </div>
            
            <p class="text-muted mb-4 small">Vei fi contactat în scurt timp de un reprezentant pentru confirmarea datelor de livrare. Un email cu detaliile comenzii va fi trimis în scurt timp.</p>
            
            <div class="d-flex gap-3 justify-content-center mt-4">
                <?php if(isset($_SESSION['client_id'])): ?>
                    <a href="istoric_comenzi.php" class="btn btn-outline-primary px-4 py-2 fw-bold">Istoric Comenzi</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">Înapoi la Magazin</a>
            </div>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>