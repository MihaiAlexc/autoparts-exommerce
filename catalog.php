<?php
session_start();
// --- CONECTARE DB ---
ini_set('display_errors', 1); error_reporting(E_ALL);
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// --- PRELUARE DATE MAȘINĂ ---
$masina_id = $_GET['masina_id'] ?? null;
$masina = null;

if ($masina_id) {
    $stmt = $pdo->prepare("SELECT * FROM masini WHERE id = ?");
    $stmt->execute([$masina_id]);
    $masina = $stmt->fetch();
}

// Dacă cineva intră pe catalog.php fără să aleagă o mașină, îl trimitem pe prima pagină
if (!$masina) {
    header("Location: index.php");
    exit();
}

// Numele complet al mașinii pentru afișare
$nume_masina_complet = $masina['marca'] . ' ' . $masina['model'] . ' ' . $masina['motorizare'] . ' (' . $masina['an_fabricatie'] . ')';

// --- DEFINIRE CATEGORII (PENTRU CATALOG) ---
$categorii = [
    ['id' => 'motor', 'nume' => 'Piese Motor', 'icon' => 'fa-cogs', 'culoare' => '#e74c3c'],
    ['id' => 'filtre', 'nume' => 'Filtre Auto', 'icon' => 'fa-filter', 'culoare' => '#f39c12'],
    ['id' => 'frane', 'nume' => 'Sistem Frânare', 'icon' => 'fa-compact-disc', 'culoare' => '#e67e22'],
    ['id' => 'suspensie', 'nume' => 'Suspensie & Direcție', 'icon' => 'fa-compress-arrows-alt', 'culoare' => '#3498db'],
    ['id' => 'uleiuri', 'nume' => 'Uleiuri & Lichide', 'icon' => 'fa-oil-can', 'culoare' => '#f1c40f'],
    ['id' => 'anvelope', 'nume' => 'Anvelope & Jante', 'icon' => 'fa-dharmachakra', 'culoare' => '#2c3e50'],
    ['id' => 'ambreiaj', 'nume' => 'Ambreiaj & Transmisie', 'icon' => 'fa-cogs', 'culoare' => '#8e44ad'],
    ['id' => 'esapament', 'nume' => 'Sistem Eșapament', 'icon' => 'fa-wind', 'culoare' => '#7f8c8d'],
    ['id' => 'caroserie', 'nume' => 'Piese Caroserie', 'icon' => 'fa-car-side', 'culoare' => '#16a085'],
    ['id' => 'alte', 'nume' => 'Accesorii & Altele', 'icon' => 'fa-box-open', 'culoare' => '#95a5a6']
];

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piese pentru <?= htmlspecialchars($masina['marca'] . ' ' . $masina['model']) ?> - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* --- STILURI PRELUATE DIN INDEX.PHP --- */
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .features-section { background: white; padding: 40px 0; border-top: 1px solid #eee; margin-top: 50px; }
        .feature-box { text-align: center; padding: 15px; }
        .feature-icon { font-size: 2rem; color: #6c757d; margin-bottom: 10px; }
        footer { background: #212529; color: #adb5bd; padding-top: 50px; }
        footer a { color: #adb5bd; text-decoration: none; }
        footer a:hover { color: white; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }

        /* --- STILURI SPECIFICE CATALOGULUI --- */
        .hero-catalog { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; padding: 40px 0; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .category-card-catalog { transition: all 0.3s ease; border: none; border-radius: 12px; cursor: pointer; text-decoration: none; display: block; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .category-card-catalog:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .icon-wrapper { width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 15px; font-size: 2rem; color: white; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'includes/header.php'; ?>

<div class="hero-catalog text-center">
    <div class="container">
        <h5 class="text-uppercase text-white-50 mb-2"><i class="fas fa-check-circle text-success"></i> Mașina ta a fost selectată</h5>
        <h1 class="fw-bold mb-3"><?= htmlspecialchars($nume_masina_complet) ?></h1>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-4"><i class="fas fa-car"></i> Schimbă mașina</a>
    </div>
</div>

<div class="container mb-5 flex-grow-1">
    <div class="row g-4">
        <?php foreach ($categorii as $cat): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="piese.php?masina_id=<?= $masina_id ?>&categorie=<?= $cat['id'] ?>" class="card category-card-catalog h-100 p-4 text-center">
                    <div class="icon-wrapper" style="background-color: <?= $cat['culoare'] ?>;">
                        <i class="fas <?= $cat['icon'] ?>"></i>
                    </div>
                    <h5 class="text-dark fw-bold mb-0 fs-6"><?= $cat['nume'] ?></h5>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- <div class="features-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 feature-box"><i class="fas fa-shipping-fast feature-icon"></i><h6 class="fw-bold">Livrare Rapidă</h6><p class="text-muted small mb-0">Expediere în 24h oriunde.</p></div>
            <div class="col-md-4 feature-box border-start-md"><i class="fas fa-sync-alt feature-icon"></i><h6 class="fw-bold">Retur Garantat</h6><p class="text-muted small mb-0">30 de zile drept de retur.</p></div>
            <div class="col-md-4 feature-box border-start-md"><i class="fas fa-medal feature-icon"></i><h6 class="fw-bold">Produse Originale</h6><p class="text-muted small mb-0">Garanție de producător.</p></div>
        </div>
    </div>
</div> -->

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>