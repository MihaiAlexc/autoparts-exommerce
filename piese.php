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

// --- PRELUARE DATE DIN URL ---
$masina_id = $_GET['masina_id'] ?? null;
$categorie_id = $_GET['categorie'] ?? null;

// Dacă lipsesc datele, trimitem înapoi la index
if (!$masina_id || !$categorie_id) {
    header("Location: index.php");
    exit();
}

// --- 1. AFLĂM DETALIILE MAȘINII ---
$stmt_m = $pdo->prepare("SELECT * FROM masini WHERE id = ?");
$stmt_m->execute([$masina_id]);
$masina = $stmt_m->fetch();
if(!$masina) { header("Location: index.php"); exit(); }
$nume_masina_complet = $masina['marca'] . ' ' . $masina['model'] . ' ' . $masina['motorizare'] . ' (' . $masina['an_fabricatie'] . ')';

// --- Numele frumos al categoriilor ---
$categorii_nume = [
    'motor' => 'Piese Motor', 'filtre' => 'Filtre Auto', 'frane' => 'Sistem Frânare',
    'suspensie' => 'Suspensie & Direcție', 'uleiuri' => 'Uleiuri & Lichide',
    'anvelope' => 'Anvelope & Jante', 'ambreiaj' => 'Ambreiaj & Transmisie',
    'esapament' => 'Sistem Eșapament', 'caroserie' => 'Piese Caroserie', 'alte' => 'Accesorii & Altele'
];
$nume_categorie_curenta = $categorii_nume[$categorie_id] ?? 'Piese Auto';

// --- 2. EXTRAGEM PIESELE ---
$sql_piese = "SELECT p.* FROM produse p 
              JOIN compatibilitati c ON p.id = c.id_produs 
              WHERE p.categorie = ? AND c.id_masina = ? 
              ORDER BY p.id DESC";
$stmt_p = $pdo->prepare($sql_piese);
$stmt_p->execute([$categorie_id, $masina_id]);
$produse_gasite = $stmt_p->fetchAll();

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nume_categorie_curenta ?> pentru <?= htmlspecialchars($masina['marca'] . ' ' . $masina['model']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* HEADER & FOOTER */
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .features-section { background: white; padding: 40px 0; border-top: 1px solid #eee; margin-top: 50px; }
        .feature-box { text-align: center; padding: 15px; }
        .feature-icon { font-size: 2rem; color: #6c757d; margin-bottom: 10px; }
        footer { background: #212529; color: #adb5bd; padding-top: 50px; }
        footer a { color: #adb5bd; text-decoration: none; }
        footer a:hover { color: white; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
        
        /* SPECIFIC PIESE.PHP */
        .breadcrumb-custom { background: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); margin-top: 20px; margin-bottom: 30px; }
        .sidebar-filtre { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .lista-categorii a { display: block; padding: 10px 15px; color: #495057; text-decoration: none; border-radius: 6px; transition: 0.2s; margin-bottom: 5px; }
        .lista-categorii a:hover { background: #f8f9fa; color: #0d6efd; }
        .lista-categorii a.active { background: #0d6efd; color: white; font-weight: bold; }
        
        .card-produs { border: none; transition: 0.3s; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card-produs:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-img-top { height: 180px; object-fit: contain; padding: 20px; border-bottom: 1px solid #f8f9fa; }
        .price { font-size: 1.2rem; font-weight: 700; color: #0d6efd; }
        .scale-up { transform: scale(1.3) translate(-35%, -35%) !important; transition: transform 0.2s; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'includes/header.php'; ?>

<div class="container flex-grow-1 mb-5">
    
    <div class="breadcrumb-custom d-flex align-items-center text-muted small">
        <a href="index.php" class="text-decoration-none text-primary"><i class="fas fa-home"></i> Acasă</a>
        <span class="mx-2">/</span>
        <a href="catalog.php?masina_id=<?= $masina_id ?>" class="text-decoration-none text-primary"><?= htmlspecialchars($masina['marca'] . ' ' . $masina['model']) ?></a>
        <span class="mx-2">/</span>
        <span class="text-dark fw-bold"><?= $nume_categorie_curenta ?></span>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold text-dark m-0"><?= $nume_categorie_curenta ?></h2>
            <p class="text-muted m-0 mt-1"><i class="fas fa-car-side me-1"></i> Compatibile cu <strong><?= htmlspecialchars($nume_masina_complet) ?></strong></p>
        </div>
        <span class="badge bg-secondary rounded-pill px-3 py-2"><?= count($produse_gasite) ?> produse găsite</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="sidebar-filtre">
                <h6 class="fw-bold text-uppercase mb-3 text-secondary" style="font-size: 0.85rem;"><i class="fas fa-list me-2"></i> Alte Categorii</h6>
                <div class="lista-categorii">
                    <?php foreach ($categorii_nume as $slug => $nume_cat): ?>
                        <a href="piese.php?masina_id=<?= $masina_id ?>&categorie=<?= $slug ?>" class="<?= $slug === $categorie_id ? 'active' : '' ?>">
                            <?= $nume_cat ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4 text-muted">
                
                <h6 class="fw-bold text-uppercase mb-3 text-secondary" style="font-size: 0.85rem;"><i class="fas fa-filter me-2"></i> Filtrează</h6>
                <p class="text-muted small"><em>Modulul de filtre avansate va fi disponibil curând.</em></p>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if (count($produse_gasite) > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($produse_gasite as $p): ?>
                        <?php $poza = !empty($p['imagine']) ? "uploads/" . $p['imagine'] : "https://via.placeholder.com/300x200?text=Fara+Poza"; ?>
                        <div class="col">
                            <div class="card card-produs h-100" style="cursor: pointer;" onclick="mergiLaProdus(event, 'produs.php?id=<?= $p['id'] ?>')">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <?php if($p['stoc'] > 0): ?>
                                        <span class="badge bg-success bg-opacity-75 text-white shadow-sm" style="font-size:0.7rem;">ÎN STOC</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger shadow-sm">Epuizat</span>
                                    <?php endif; ?>
                                </div>
                                
                                <img src="<?= $poza ?>" class="card-img-top" alt="<?= htmlspecialchars($p['nume_piesa']) ?>">
                                <div class="card-body d-flex flex-column pt-2">
                                    <h6 class="card-title text-dark mb-1 text-truncate" title="<?= htmlspecialchars($p['nume_piesa']) ?>"><?= htmlspecialchars($p['nume_piesa']) ?></h6>
                                    <small class="text-muted mb-3" style="font-size: 0.8rem;">Cod OE: <span class="fw-bold"><?= htmlspecialchars($p['cod_piesa']) ?></span></small>
                                    
                                    <div class="mt-auto pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="price"><?= number_format($p['pret'], 2) ?> <small style="font-size:0.8rem; color:#666">RON</small></span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="produs.php?id=<?= $p['id'] ?>" class="btn btn-outline-secondary w-50 fw-bold">Detalii</a>
                                            
                                            <form action="adauga_cos.php" method="POST" class="w-50 form-adauga-cos" data-nume="<?= htmlspecialchars($p['nume_piesa'], ENT_QUOTES) ?>" data-poza="<?= $poza ?>">
                                                <input type="hidden" name="id_produs" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="cantitate" value="1">
                                                <button type="submit" class="btn btn-primary w-100 fw-bold" <?= $p['stoc'] <= 0 ? 'disabled' : '' ?>>
                                                    <i class="fas fa-cart-plus"></i> Coș
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light text-center p-5 border shadow-sm rounded-4 h-100 d-flex flex-column justify-content-center align-items-center">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4>Nu am găsit nicio piesă în această categorie.</h4>
                    <p class="text-muted mb-0">Ne pare rău, dar momentan nu avem pe stoc piese din secțiunea <strong><?= $nume_categorie_curenta ?></strong> pentru mașina selectată.</p>
                </div>
            <?php endif; ?>
        </div>
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

<div class="modal fade" id="modalCos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
      <div class="modal-header bg-success text-white border-0" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i> Produs adăugat!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <img id="modal-poza-piesa" src="" alt="Piesa" style="max-height: 120px; object-fit: contain; margin-bottom: 15px; border-radius: 8px; border: 1px solid #eee; padding: 10px;">
        <h5 id="modal-nume-piesa" class="fw-bold text-dark mb-2"></h5>
        <p class="fs-6 text-muted mb-0">A fost adăugat cu succes în coșul tău.</p>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4 bg-light" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
        <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-bold rounded-pill" data-bs-dismiss="modal">Înapoi la magazin</button>
        <a href="cos.php" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm">Vezi coșul tău <i class="fas fa-arrow-right ms-2"></i></a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // =======================================================
    // --- 1. SCRIPT ADAUGARE ÎN COȘ ---
    // =======================================================
    const formsAdaugaCos = document.querySelectorAll('.form-adauga-cos');
    const modalCos = new bootstrap.Modal(document.getElementById('modalCos'));
    const cartBadge = document.getElementById('cart-badge');
    const modalNumePiesa = document.getElementById('modal-nume-piesa');
    const modalPozaPiesa = document.getElementById('modal-poza-piesa');

    formsAdaugaCos.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const formData = new FormData(this);
            const numePiesa = this.getAttribute('data-nume');
            const pozaPiesa = this.getAttribute('data-poza');

            fetch('adauga_cos.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    if(cartBadge) {
                        cartBadge.textContent = data.total_produse;
                        cartBadge.classList.add('scale-up');
                        setTimeout(() => cartBadge.classList.remove('scale-up'), 200);
                    }
                    modalNumePiesa.textContent = numePiesa;
                    modalPozaPiesa.src = pozaPiesa;
                    modalCos.show();
                }
            })
            .catch(error => console.error('Eroare la adăugarea în coș:', error));
        });
    });

    // =======================================================
    // --- 2. SCRIPT TRANZIȚIE LINĂ (SCROLL ÎN SUS) ---
    // =======================================================
    const linkuriCategorii = document.querySelectorAll('.lista-categorii a');

    linkuriCategorii.forEach(link => {
        link.addEventListener('click', function(e) {
            // Dacă dă click pe categoria activă în care e deja, nu facem nimic
            if (this.classList.contains('active')) return;

            // Oprim încărcarea bruscă a noii pagini
            e.preventDefault(); 
            const urlDestinatie = this.href;

            // Facem scroll fin și rapid până sus
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            // Așteptăm 0.3 secunde (300 milisecunde) să vedem mișcarea, apoi schimbăm pagina
            setTimeout(() => {
                window.location.href = urlDestinatie;
            }, 300); 
        });
    });

}); // <-- AICI SE ÎNCHIDE BLOCUL PRINCIPAL

// =======================================================
// --- 3. FUNCȚIA PENTRU CARDURI CLICKABILE (GLOBALĂ) ---
// =======================================================
function mergiLaProdus(event, url) {
    // Verificăm dacă click-ul a fost dat pe formularul de coș sau pe butonul Detalii
    if (event.target.closest('.form-adauga-cos') || event.target.closest('.btn-outline-secondary')) {
        return; // Oprim scriptul aici, lăsăm AJAX-ul sau butonul să funcționeze normal!
    }
    
    // Dacă a dat click oriunde altundeva pe card, îl trimitem la produs
    window.location.href = url;
}
</script>
</body>
</html>
