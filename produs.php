<?php
session_start();
// --- 1. CONECTARE DB ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare: " . $e->getMessage()); }

// --- 2. LOGICA: Căutăm produsul după ID ---
$produs = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM produse WHERE id = ?");
    $stmt->execute([$id]);
    $produs = $stmt->fetch();
}

if (!$produs) {
    die("<div style='text-align:center; padding:100px; font-family:sans-serif;'><h2>Piesa nu a fost găsită!</h2><a href='index.php' style='color:#0d6efd; text-decoration:none;'>&larr; Înapoi la magazin</a></div>");
}

// --- VERIFICĂM DACĂ PIESA E LA FAVORITE ---
$este_favorit = false;
if (isset($_SESSION['client_id'])) {
    $stmt_fav = $pdo->prepare("SELECT id FROM favorite WHERE id_client = ? AND id_produs = ?");
    $stmt_fav->execute([$_SESSION['client_id'], $produs['id']]);
    if ($stmt_fav->fetch()) {
        $este_favorit = true;
    }
}

// --- 3. EXTRAGEM MAȘINILE COMPATIBILE ---
$stmt_compatibilitati = $pdo->prepare("
    SELECT m.marca, m.model, m.an_fabricatie, m.motorizare 
    FROM masini m 
    JOIN compatibilitati c ON m.id = c.id_masina 
    WHERE c.id_produs = ?
    ORDER BY m.marca ASC, m.model ASC
");
$stmt_compatibilitati->execute([$produs['id']]);
$masini_compatibile = $stmt_compatibilitati->fetchAll();

// --- MEMORARE PRODUSE RECENT VIZIONATE ---
if (!isset($_SESSION['vizionate'])) {
    $_SESSION['vizionate'] = [];
}
if (!in_array($produs['id'], $_SESSION['vizionate'])) {
    array_unshift($_SESSION['vizionate'], $produs['id']);
    $_SESSION['vizionate'] = array_slice($_SESSION['vizionate'], 0, 4);
}

// Număr produse în coș
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;

// Pregătim poza
$poza = !empty($produs['imagine']) ? "uploads/" . $produs['imagine'] : "https://via.placeholder.com/600x400?text=Fara+Poza";

// Formatare preț
$pret_intreg = floor($produs['pret']);
$pret_zecimale = str_pad(round(($produs['pret'] - $pret_intreg) * 100), 2, '0', STR_PAD_RIGHT);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produs['nume_piesa']) ?> - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh;}
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        .breadcrumb-custom { font-size: 0.85rem; padding: 10px 0; background: transparent; }
        .breadcrumb-custom a { color: #6c757d; text-decoration: none; }
        .breadcrumb-custom a:hover { color: #0d6efd; text-decoration: underline; }

        /* MODIFICARE: Titlu Bold */
        .product-title { font-weight: 700; font-size: 2.2rem; color: #2c3e50; margin-bottom: 5px; }
        .product-meta { font-size: 0.85rem; color: #7f8c8d; margin-bottom: 30px; }
        .product-meta span { margin-right: 15px; }

        /* MODIFICARE: Poza mult mai mare */
        .product-image-container { text-align: center; padding: 10px; }
        .product-image { max-width: 100%; max-height: 500px; object-fit: contain; }

        /* Tabel specificații subtil */
        .specs-title { font-size: 1.1rem; font-weight: 500; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .specs-table { font-size: 0.9rem; margin-bottom: 0; }
        .specs-table td, .specs-table th { padding: 8px 0; border-bottom: 1px solid #f8f9fa; }
        /* MODIFICARE: Text Alb/Negru (Gri închis și Negru) */
        .specs-table th { font-weight: normal; color: #555; width: 45%; }
        .specs-table td { font-weight: 600; color: #000; }

        /* Caseta de cumpărare */
        .buy-box { padding: 0; }
        .stock-indicator { font-size: 0.85rem; color: #27ae60; margin-bottom: 15px; display: flex; align-items: center; gap: 6px; }
        .stock-dot { width: 8px; height: 8px; background-color: #27ae60; border-radius: 50%; display: inline-block; }
        .stock-indicator.out-of-stock { color: #e74c3c; }
        .stock-indicator.out-of-stock .stock-dot { background-color: #e74c3c; }
        
        .return-info { font-size: 0.85rem; color: #555; display: flex; align-items: center; gap: 8px; margin-bottom: 25px; }
        
        .price-display { display: flex; align-items: baseline; gap: 4px; margin-bottom: 5px; }
        .price-int { font-size: 2.5rem; font-weight: bold; color: #333; line-height: 1; }
        .price-dec { font-size: 1.2rem; font-weight: bold; color: #333; position: relative; top: -10px; }
        .price-currency { font-size: 1.5rem; color: #333; margin-left: 5px; }
        
        .tax-info { font-size: 0.75rem; color: #7f8c8d; line-height: 1.4; margin-bottom: 20px; }
        .tax-info a { color: #34495e; text-decoration: underline; }

        .qty-input-group { border: 1px solid #ddd; border-radius: 4px; overflow: hidden; height: 45px; display: flex; }
        .qty-input-group input { border: none; text-align: center; width: 50px; font-weight: bold; outline: none; }
        .qty-input-group .qty-btns { display: flex; flex-direction: column; width: 30px; border-left: 1px solid #ddd; background: #f8f9fa; }
        .qty-input-group .qty-btn { flex: 1; border: none; background: transparent; font-size: 0.7rem; cursor: pointer; color: #555; }
        .qty-input-group .qty-btn:hover { background: #e9ecef; }
        .qty-input-group .qty-btn:first-child { border-bottom: 1px solid #ddd; }

        /* MODIFICARE: Buton Albastru Premium */
        .btn-add-cart { background-color: #0d6efd; color: white; border: none; font-weight: bold; font-size: 1rem; border-radius: 4px; height: 45px; transition: background 0.2s; }
        .btn-add-cart:hover { background-color: #0b5ed7; color: white; }
        .btn-add-cart:disabled { background-color: #ccc; cursor: not-allowed; }

        /* Buton Favorite */
        .btn-favorite {
            background: #f8f9fa; border: 1px solid #ddd; color: #dc3545;
            font-size: 1.2rem; border-radius: 4px; padding: 0 15px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease; height: 45px;
        }
        .btn-favorite:hover { background: #fee2e2; border-color: #dc3545; }
        .btn-favorite i { transition: transform 0.2s; }
        .btn-favorite:hover i { transform: scale(1.1); }    

        /* Tabs inferioare */
        .nav-tabs { border-bottom: 1px solid #eee; margin-top: 40px; }
        .nav-tabs .nav-link { color: #666; border: none; background: transparent; font-weight: 500; font-size: 1.1rem; padding: 15px 20px; border-bottom: 3px solid transparent; }
        .nav-tabs .nav-link.active { color: #000; border-bottom: 3px solid #0d6efd; background: transparent; }
        
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .scale-up { transform: scale(1.3) translate(-35%, -35%) !important; transition: transform 0.2s; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>


<div class="bg-light border-bottom">
    <div class="container">
        <div class="breadcrumb-custom">
            <a href="index.php">Magazin piese auto</a> <span class="mx-1 text-muted">|</span> 
            <a href="piese.php?categorie=<?= htmlspecialchars($produs['categorie'] ?? '') ?>" class="text-capitalize"><?= htmlspecialchars($produs['categorie'] ?? 'Catalog') ?></a> <span class="mx-1 text-muted">|</span> 
            <span class="text-muted"><?= htmlspecialchars($produs['nume_piesa']) ?></span>
        </div>
    </div>
</div>

<div class="container mt-4 mb-5 flex-grow-1">
    
    <h1 class="product-title"><?= htmlspecialchars($produs['nume_piesa']) ?></h1>
    <div class="product-meta d-flex align-items-center">
        <span>Numărul articolului: <strong class="text-dark"><?= htmlspecialchars($produs['cod_piesa']) ?></strong></span>
        <span>Stare: <strong class="text-dark">Nou</strong></span>
    </div>

    <div class="row mt-4">
        
        <div class="col-lg-5 mb-4 mb-lg-0">
            <div class="product-image-container">
                <img src="<?= $poza ?>" alt="<?= htmlspecialchars($produs['nume_piesa']) ?>" class="product-image">
            </div>
        </div>

        <div class="col-lg-4 mb-4 mb-lg-0 pe-lg-4">
            <h5 class="specs-title">Descrierea</h5>
            
            <table class="table table-borderless specs-table w-100">
                <tbody>
                    <tr>
                        <th>Producător:</th>
                        <td>Original Equipment (OE)</td>
                    </tr>
                    <tr>
                        <th>Cod OE:</th>
                        <td><?= htmlspecialchars($produs['cod_piesa']) ?></td>
                    </tr>
                    <tr>
                        <th>Categorie:</th>
                        <td class="text-capitalize"><?= htmlspecialchars($produs['categorie'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="collapse" id="maiMulteDetalii">
                <table class="table table-borderless specs-table w-100 mt-0 pt-0">
                    <tbody>
                        <tr>
                            <th>Stare:</th>
                            <td>Articol nou</td>
                        </tr>
                        <tr>
                            <th>Garanție:</th>
                            <td>2 ani garanție legală</td>
                        </tr>
                        <tr>
                            <th>Partea de montare:</th>
                            <td>Axa față / spate</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <a class="text-primary small fw-bold text-decoration-none mt-2 d-inline-block" data-bs-toggle="collapse" href="#maiMulteDetalii" role="button" aria-expanded="false" aria-controls="maiMulteDetalii" id="btnToggleDetalii" style="cursor: pointer;">
                Mai multe detalii ▼
            </a>
        </div>

        <div class="col-lg-3">
            <div class="buy-box">
                
                <?php if ($produs['stoc'] > 0): ?>
                    <div class="stock-indicator"><span class="stock-dot"></span> În stoc</div>
                <?php else: ?>
                    <div class="stock-indicator out-of-stock"><span class="stock-dot"></span> Stoc Epuizat</div>
                <?php endif; ?>

                <div class="return-info">
                    <i class="fas fa-history fs-5 text-secondary"></i>
                    <span>Dreptul la returnarea mărfii timp de 14 zile</span>
                </div>

                <div class="price-display">
                    <span class="price-int"><?= $pret_intreg ?></span>
                    <span class="price-dec">,<?= $pret_zecimale ?></span>
                    <span class="price-currency">lei</span>
                </div>
                
                <div class="tax-info">
                    Prețul incl. 21% TVA<br>
                    fără <a href="#">costurile de livrare</a>
                </div>

                <form action="adauga_cos.php" method="POST" class="form-adauga-cos mt-3" data-nume="<?= htmlspecialchars($produs['nume_piesa'], ENT_QUOTES) ?>" data-poza="<?= $poza ?>">
                    <input type="hidden" name="id_produs" value="<?= $produs['id'] ?>">
                    
                    <div class="d-flex gap-2 mb-3">
                        <div class="qty-input-group">
                            <input type="number" name="cantitate" id="qtyInput" value="1" min="1" max="<?= $produs['stoc'] > 0 ? $produs['stoc'] : 1 ?>" <?= $produs['stoc'] <= 0 ? 'readonly' : '' ?>>
                            <div class="qty-btns">
                                <button type="button" class="qty-btn" onclick="document.getElementById('qtyInput').stepUp()"><i class="fas fa-chevron-up"></i></button>
                                <button type="button" class="qty-btn" onclick="document.getElementById('qtyInput').stepDown()"><i class="fas fa-chevron-down"></i></button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-add-cart flex-grow-1" <?= $produs['stoc'] <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-shopping-cart me-2"></i> Cumpără
                        </button>
                        <button type="button" class="btn-favorite" id="btnFavorite" data-id="<?= $produs['id'] ?>" title="Adaugă la favorite">
                            <i class="<?= $este_favorit ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
        
    </div>
    
    <div class="row" id="tab-compatibilitati">
        <div class="col-12">
            <ul class="nav nav-tabs" id="produsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="compatibilitati-tab" data-bs-toggle="tab" data-bs-target="#compatibilitati" type="button" role="tab">Mașini Compatibile</button>
                </li>
            </ul>
            
            <div class="tab-content pt-4" id="produsTabsContent">
                <div class="tab-pane fade show active" id="compatibilitati" role="tabpanel">
                    <?php if(count($masini_compatibile) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped text-center align-middle" style="font-size: 0.9rem;">
                                <thead class="table-light text-muted text-uppercase" style="font-size: 0.8rem;">
                                    <tr>
                                        <th>Marcă</th>
                                        <th>Model</th>
                                        <th>An Fabricație</th>
                                        <th>Motorizare</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($masini_compatibile as $mc): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($mc['marca']) ?></td>
                                            <td><?= htmlspecialchars($mc['model']) ?></td>
                                            <td><?= htmlspecialchars($mc['an_fabricatie']) ?></td>
                                            <td class="text-primary"><?= htmlspecialchars($mc['motorizare']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted"><i class="fas fa-info-circle me-1"></i> Verificați codul OE (<?= htmlspecialchars($produs['cod_piesa']) ?>) pentru a vă asigura de compatibilitate.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// ==========================================
// BLOC NOU: AI VIZITAT RECENT (CU SLIDER)
// ==========================================
if (isset($_SESSION['vizionate']) && count($_SESSION['vizionate']) > 0) {
    
    // Extragem ID-ul curent direct din link și filtrăm lista
    $id_curent = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $vizionate_filtrate = array_filter($_SESSION['vizionate'], function($v) use ($id_curent) {
        return $v != $id_curent;
    });

    if (count($vizionate_filtrate) > 0) {
        // MĂRIM LIMITA: Luăm maxim 8 produse în loc de 4!
        $vizionate_limit = array_slice($vizionate_filtrate, 0, 8);
        $ids_vizionate = implode(',', array_map('intval', $vizionate_limit));
        
        // Extragem produsele, păstrând ordinea exactă
        $stmt_vizionate = $pdo->query("SELECT * FROM produse WHERE id IN ($ids_vizionate) ORDER BY FIELD(id, $ids_vizionate)");
        $produse_vizionate = $stmt_vizionate->fetchAll();

        if (count($produse_vizionate) > 0):
?>
<div class="container mb-5 mt-5 border-top pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark m-0">
            <i class="fas fa-history text-primary me-2"></i> Ai vizitat recent
        </h4>
    </div>
    
    <div class="position-relative" style="padding: 0 10px;">
        
        <button id="btn-recent-left" class="slider-arrow arrow-left">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button id="btn-recent-right" class="slider-arrow arrow-right">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="recent-scroll-wrapper" id="recent-track">
            <?php foreach ($produse_vizionate as $pv): ?>
                <?php $poza_v = !empty($pv['imagine']) ? "uploads/" . $pv['imagine'] : "https://via.placeholder.com/300x200?text=Fara+Poza"; ?>
                
                <div class="recent-item">
                    <div class="card h-100 shadow-sm border-0 recent-card" style="border-radius: 12px; cursor: pointer;" onclick="window.location.href='produs.php?id=<?= $pv['id'] ?>'">
                        <img src="<?= $poza_v ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($pv['nume_piesa']) ?>" style="height: 140px; object-fit: contain;">
                        <div class="card-body p-3 border-top bg-light d-flex flex-column" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                            <h6 class="text-dark mb-1 text-truncate" style="font-size: 0.9rem;" title="<?= htmlspecialchars($pv['nume_piesa']) ?>">
                                <?= htmlspecialchars($pv['nume_piesa']) ?>
                            </h6>
                            <div class="fw-bold text-primary mt-auto"><?= number_format($pv['pret'], 2) ?> RON</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* Stilurile pentru carduri */
.recent-card { transition: transform 0.3s, box-shadow 0.3s; height: 100%; display: flex; flex-direction: column; }
.recent-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }

/* Wrapper-ul slider-ului */
.recent-scroll-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 1.5rem; /* Spațiul dintre carduri */
    padding: 15px 5px;
    scroll-behavior: smooth;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.recent-scroll-wrapper::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

/* Lățimea fixă a unui element ca să încapă exact 4 pe ecran mare */
.recent-item {
    flex: 0 0 calc(25% - 1.125rem); /* 25% din lățime minus spațiul dintre ele */
    min-width: 220px; /* Să nu se facă prea mici pe telefon */
}

/* Săgețile Premium */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 45px;
    height: 45px;
    background-color: white;
    color: #0d6efd;
    border: 1px solid #e9ecef;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.arrow-left { left: -20px; }
.arrow-right { right: -20px; }

.slider-arrow:hover {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
    transform: translateY(-50%) scale(1.1);
    border-color: #0d6efd;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const trackRecent = document.getElementById('recent-track');
    const btnLeftRecent = document.getElementById('btn-recent-left');
    const btnRightRecent = document.getElementById('btn-recent-right');
    
    if (trackRecent && btnLeftRecent && btnRightRecent) {
        // Distanța parcursă la un click (aproximativ lățimea unui card + gap)
        const scrollAmountRecent = trackRecent.offsetWidth / 2; 

        btnRightRecent.addEventListener('click', () => {
            trackRecent.scrollBy({ left: scrollAmountRecent, behavior: 'smooth' });
        });

        btnLeftRecent.addEventListener('click', () => {
            trackRecent.scrollBy({ left: -scrollAmountRecent, behavior: 'smooth' });
        });
    }
});
</script>
<?php 
        endif; 
    }
}
// ==========================================
?>

<?php include 'includes/footer.php'; ?>

<div class="modal fade" id="modalCos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 10px;">
      <div class="modal-header bg-success text-white border-0">
        <h5 class="modal-title fw-normal"><i class="fas fa-check me-2"></i> Produs adăugat în coș!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <img id="modal-poza-piesa" src="" alt="Piesa" style="max-height: 100px; object-fit: contain; margin-bottom: 15px;">
        <h6 id="modal-nume-piesa" class="fw-bold text-dark mb-0"></h6>
      </div>
      <div class="modal-footer justify-content-center border-0 pb-4">
        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Înapoi</button>
        <a href="cos.php" class="btn btn-success px-4 py-2 fw-bold shadow-sm">Spre Coș <i class="fas fa-chevron-right ms-1" style="font-size:0.8rem;"></i></a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Script pentru butonul de Mai multe detalii
    const btnToggleDetalii = document.getElementById('btnToggleDetalii');
    if(btnToggleDetalii) {
        btnToggleDetalii.addEventListener('click', function() {
            if (this.getAttribute('aria-expanded') === 'true') {
                this.innerHTML = 'Mai puține detalii ▲';
            } else {
                this.innerHTML = 'Mai multe detalii ▼';
            }
        });
    }

    // Script pentru Adăugare în Coș (AJAX)
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
});

// Script pentru Butonul de Favorite
    const btnFavorite = document.getElementById('btnFavorite');
    if (btnFavorite) {
        btnFavorite.addEventListener('click', function() {
            const idProdus = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            const formData = new FormData();
            formData.append('id_produs', idProdus);

            fetch('actiune_favorit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'neautentificat') {
                    // Dacă nu e logat, îl trimitem la pagina de login
                    window.location.href = 'login_client.php';
                } else if (data.status === 'success') {
                    // Schimbăm inimioara vizual instant!
                    if (data.actiune === 'adaugat') {
                        icon.classList.remove('far'); // Scoate conturul
                        icon.classList.add('fas');    // Pune inimă plină
                    } else {
                        icon.classList.remove('fas'); // Scoate plina
                        icon.classList.add('far');    // Pune contur
                    }
                } else {
                    alert(data.mesaj);
                }
            })
            .catch(error => console.error('Eroare favorite:', error));
        });
    }
</script>

</body>
</html>