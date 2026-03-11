<?php
session_start();
// --- CONECTARE ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare: " . $e->getMessage()); }

// --- MEMORARE MAȘINĂ CĂUTATĂ PENTRU FORMULAR ---
$masina_selectata_id = $_GET['masina_id'] ?? null;
$pre_marca = ''; $pre_model = ''; $pre_an = ''; $pre_comb = '';

if ($masina_selectata_id) {
    $stmt_ms = $pdo->prepare("SELECT * FROM masini WHERE id = ?");
    $stmt_ms->execute([$masina_selectata_id]);
    $masina_curenta = $stmt_ms->fetch();
    if ($masina_curenta) {
        $pre_marca = $masina_curenta['marca'];
        $pre_model = $masina_curenta['model'];
        $pre_an = $masina_curenta['an_fabricatie'];
        $pre_comb = $masina_curenta['combustibil'];
    }
}
// -----------------------------------------------

// --- SISTEM DE TRAFIC (Contorizare Vizitatori) ---
$ip_vizitator = $_SERVER['REMOTE_ADDR'];
$data_azi = date('Y-m-d');
try {
    // Înregistrăm vizita. "INSERT IGNORE" previne erorile dacă omul a mai intrat azi.
    $pdo->prepare("INSERT IGNORE INTO vizitatori (ip, data_vizita) VALUES (?, ?)")->execute([$ip_vizitator, $data_azi]);
} catch (Exception $e) {
    // Ignorăm erorile pentru a nu bloca site-ul
}

// --- LOGICA CĂUTARE SUPREMĂ ---
$produse_gasite = [];
$filtru_activ = false;

$sql = "SELECT p.* FROM produse p WHERE 1=1";
$params = [];
$joins = "";

// 1. Filtru după Mașină (din JS)
if (!empty($_GET['masina_id'])) {
    $filtru_activ = true;
    $joins = " JOIN compatibilitati c ON p.id = c.id_produs";
    $sql = "SELECT p.* FROM produse p" . $joins . " WHERE c.id_masina = ?";
    $params[] = $_GET['masina_id'];
}

// 2. Filtru după Categorie (iconițe)
if (!empty($_GET['categorie'])) {
    $filtru_activ = true;
    $sql .= " AND p.categorie = ?";
    $params[] = $_GET['categorie'];
}

// 3. Filtru Text (bara de căutare de sus)
if (!empty($_GET['search'])) {
    $filtru_activ = true;
    $sql .= " AND (p.nume_piesa LIKE ? OR p.cod_piesa LIKE ?)";
    $params[] = "%" . $_GET['search'] . "%";
    $params[] = "%" . $_GET['search'] . "%";
}

// Afișăm rezultatele
$sql .= " ORDER BY p.id DESC LIMIT 20";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produse_gasite = $stmt->fetchAll();

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoParts Pro - Magazin Piese Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .hero-section { 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.8)), url('fundal-auto.avif') no-repeat center center; 
            background-size: cover; color: white; padding: 60px 0 100px 0; margin-bottom: 0; 
        }
        .category-card { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 12px; }
        .category-card:hover { transform: translateY(-8px); box-shadow: 0 15px 25px rgba(0,0,0,0.15) !important; cursor: pointer; border-color:#0d6efd !important;}
        .features-section { background: white; padding: 40px 0; border-top: 1px solid #eee; margin-top: 50px; }
        .feature-box { text-align: center; padding: 15px; }
        .feature-icon { font-size: 2rem; color: #6c757d; margin-bottom: 10px; }
        .card-produs { border: none; transition: 0.3s; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card-produs:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-img-top { height: 180px; object-fit: contain; padding: 20px; border-bottom: 1px solid #f8f9fa; }
        .price { font-size: 1.2rem; font-weight: 700; color: #0d6efd; }
        footer { background: #212529; color: #adb5bd; padding-top: 50px; }
        footer a { color: #adb5bd; text-decoration: none; }
        footer a:hover { color: white; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="hero-section text-white">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-3">Piese Auto Premium</h1>
                <p class="lead mb-4" style="opacity: 0.9;">Compatibilitate 100% garantată pentru mașina ta.</p>
                <div class="d-flex gap-3">
                    <span class="badge bg-light text-dark px-3 py-2 fs-6 rounded-pill shadow-sm"><i class="fas fa-check-circle text-primary me-1"></i> Stoc Real</span>
                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill shadow-sm"><i class="fas fa-truck me-1"></i> Livrare 24h</span>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card bg-light bg-opacity-10 border border-light border-opacity-25 shadow-lg" style="backdrop-filter: blur(10px);">
                    <div class="card-body p-4">
                        <h4 class="mb-3 text-white"><i class="fas fa-car me-2"></i> Alege mașina ta</h4>
                        <form action="catalog.php" method="GET" class="row g-2">
                            <div class="col-md-6">
                                <select id="select_marca" class="form-select form-select-lg" style="font-size: 0.95rem;" required>
                                    <option value="">1. Marca...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="select_model" class="form-select form-select-lg" style="font-size: 0.95rem;" disabled required>
                                    <option value="">2. Model...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="select_an" class="form-select form-select-lg" style="font-size: 0.95rem;" disabled required>
                                    <option value="">3. An Fabricație...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select id="select_combustibil" class="form-select form-select-lg" style="font-size: 0.95rem;" disabled required>
                                    <option value="">4. Combustibil...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <select id="select_motor" name="masina_id" class="form-select form-select-lg" style="font-size: 0.95rem;" disabled required>
                                    <option value="">5. Motorizare / Putere...</option>
                                </select>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" id="btn_cauta_masina" class="btn btn-warning w-100 fw-bold text-dark fs-5 shadow-sm" disabled>Găsește Piese</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container position-relative" style="margin-top: -50px; z-index: 10; margin-bottom: 50px;">
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
        <?php 
        $categorii = [
            'anvelope' => ['Anvelope/Jante', 'Roata'], 'frane' => ['Sistem frânare', 'Frane'],
            'amortizare' => ['Amortizare', 'Amortizor'], 'uleiuri' => ['Ulei/Lichide', 'Ulei'],
            'suspensie' => ['Suspensie', 'Suspensie'], 'filtre' => ['Filtre', 'Filtre'],
            'motor' => ['Motor', 'Motor'], 'caroserie' => ['Caroserie', 'Caroserie'],
            'ambreiaj' => ['Ambreiaj', 'Ambreiaj'], 'curele' => ['Curele/Role', 'Curea'],
            'esapament' => ['Eșapament', 'Esapament'], 'alte' => ['Alte categorii', 'Cutie']
        ];
        $cat_curenta = $_GET['categorie'] ?? '';
        foreach($categorii as $slug => $date): 
            $border = ($cat_curenta == $slug) ? 'border border-primary border-2' : 'border-0';
        ?>
        <div class="col">
            <a href="index.php?categorie=<?= $slug ?>" class="text-decoration-none">
                <div class="card h-100 text-center shadow category-card pt-3 <?= $border ?>">
                    <img src="https://placehold.co/120x100/ffffff/0d6efd?text=<?= $date[1] ?>" class="mx-auto img-fluid" style="height: 50px; object-fit: contain;" alt="<?= $date[0] ?>">
                    <div class="card-body p-2 mt-2"><h6 class="card-title text-dark fw-bold mb-0" style="font-size: 0.85rem;"><?= $date[0] ?></h6></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container mb-5 pt-2">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h4 class="fw-bold text-dark m-0"><?= $filtru_activ ? "Rezultate Căutare" : "Cele mai noi produse" ?></h4>
        <?php if($filtru_activ): ?><a href="index.php" class="btn btn-outline-danger btn-sm rounded-pill px-3"><i class="fas fa-times me-1"></i> Resetare filtre</a><?php endif; ?>
    </div>

    <?php if (count($produse_gasite) > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
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
        <div class="alert alert-light text-center p-5 border shadow-sm rounded-4">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h4>Nu am găsit nicio piesă.</h4>
            <p class="text-muted mb-0">Încearcă să modifici datele căutării.</p>
        </div>
    <?php endif; ?>
</div>

<?php
// Dacă există produse în memorie, le afișăm
if (isset($_SESSION['vizionate']) && count($_SESSION['vizionate']) > 0) {
    // Transformăm lista de ID-uri într-un text pentru SQL (ex: "5,12,8")
    $ids_vizionate = implode(',', array_map('intval', $_SESSION['vizionate']));
    
    // Extragem produsele, păstrând ordinea exactă în care au fost văzute
    $stmt_vizionate = $pdo->query("SELECT * FROM produse WHERE id IN ($ids_vizionate) ORDER BY FIELD(id, $ids_vizionate)");
    $produse_vizionate = $stmt_vizionate->fetchAll();

    if (count($produse_vizionate) > 0):
?>
<div class="container mb-5 mt-5 border-top pt-5">
    <h4 class="fw-bold text-dark mb-4">
        <i class="fas fa-history text-primary me-2"></i> Ai vizitat recent
    </h4>
    <div class="row row-cols-2 row-cols-md-4 g-4">
        <?php foreach ($produse_vizionate as $pv): ?>
            <?php $poza_v = !empty($pv['imagine']) ? "uploads/" . $pv['imagine'] : "https://via.placeholder.com/300x200?text=Fara+Poza"; ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 recent-card" style="border-radius: 12px; transition: transform 0.3s;">
                    <img src="<?= $poza_v ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($pv['nume_piesa']) ?>" style="height: 140px; object-fit: contain;">
                    <div class="card-body p-3 border-top bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <h6 class="text-dark mb-1 text-truncate" style="font-size: 0.9rem;" title="<?= htmlspecialchars($pv['nume_piesa']) ?>">
                            <?= htmlspecialchars($pv['nume_piesa']) ?>
                        </h6>
                        <div class="fw-bold text-primary"><?= number_format($pv['pret'], 2) ?> RON</div>
                        <a href="produs.php?id=<?= $pv['id'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.recent-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
</style>

<?php 
    endif; 
}
?>

<div class="container mb-5 mt-5">
    <div class="row align-items-center bg-white rounded-4 shadow-sm overflow-hidden border border-light">
        <div class="col-lg-6 p-4 p-md-5">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3 fw-bold border border-primary border-opacity-25">
                <i class="fas fa-info-circle me-1"></i> Ghid Auto
            </span>
            <h2 class="fw-bold mb-4 text-dark" style="letter-spacing: -0.5px;">De ce este vitală revizia auto la timp?</h2>
            <p class="text-muted mb-4" style="line-height: 1.8;">
                O mașină întreținută corect nu doar că îți oferă siguranță la volan, dar te salvează de reparații extrem de costisitoare pe termen lung. Schimbul de ulei, înlocuirea filtrelor și verificarea periodică a sistemului de frânare prelungesc semnificativ viața motorului tău.
            </p>
            <ul class="list-unstyled mb-4 text-muted">
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-oil-can text-success fs-5 me-3"></i> 
                    <span><strong>Schimbul de ulei:</strong> Recomandat la fiecare 10.000 - 15.000 km parcurși.</span>
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-filter text-success fs-5 me-3"></i> 
                    <span><strong>Filtre noi:</strong> Asigură un aer curat în habitaclu și un consum optim de carburant.</span>
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fas fa-compact-disc text-success fs-5 me-3"></i> 
                    <span><strong>Sistemul de frânare:</strong> Verifică discurile și plăcuțele la fiecare schimbare de sezon.</span>
                </li>
            </ul>
            <a href="index.php?categorie=filtre" class="btn btn-outline-primary fw-bold px-4 py-2 rounded-pill shadow-sm">
                Vezi piese pentru revizie <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="col-lg-6 p-0 d-none d-lg-block position-relative">
            <img src="https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&q=80&w=800" alt="Mecanic auto la revizie" class="img-fluid w-100 h-100" style="object-fit: cover; min-height: 450px;">
            <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                <p class="text-white mb-0 fw-medium fst-italic">„Mentenanța preventivă este cea mai bună investiție în mașina ta.”</p>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5 mt-5 border-top pt-5">
    <h5 class="fw-bold text-center mb-4 text-secondary text-uppercase" style="letter-spacing: 1px;">Branduri de top cu care colaborăm</h5>
    
    <div class="position-relative" style="padding: 0 15px;">
        
        <button id="btn-brand-left" class="slider-arrow arrow-left">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <button id="btn-brand-right" class="slider-arrow arrow-right">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="brands-scroll-wrapper" id="brands-track">
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=BOSCH" class="img-fluid logo-brand" alt="Bosch"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=BREMBO" class="img-fluid logo-brand" alt="Brembo"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=MOTUL" class="img-fluid logo-brand" alt="Motul"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=CASTROL" class="img-fluid logo-brand" alt="Castrol"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=MANN+FILTER" class="img-fluid logo-brand" alt="Mann Filter"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=VALEO" class="img-fluid logo-brand" alt="Valeo"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=LUK" class="img-fluid logo-brand" alt="LuK"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=NGK" class="img-fluid logo-brand" alt="NGK"></div>
            <div class="brand-item"><img src="https://placehold.co/150x60/ffffff/555555?text=SACHS" class="img-fluid logo-brand" alt="Sachs"></div>
        </div>
    </div>
</div>

<style>
/* --- STILURI NOI PENTRU SĂGEȚI PREMIUM --- */
.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 40px;
    height: 40px;
    background-color: white;
    color: #0d6efd; /* Albastru specific Bootstrap */
    border: 1px solid #e9ecef;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Poziționarea stânga/dreapta ușor în afara rândului */
.arrow-left { left: -10px; }
.arrow-right { right: -10px; }

/* Efectul spectaculos când pui mouse-ul */
.slider-arrow:hover {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
    transform: translateY(-50%) scale(1.15);
    border-color: #0d6efd;
}

/* Ascundem bara de scroll și lăsăm doar funcționalitatea */
.brands-scroll-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 3rem;
    padding: 15px 0;
    align-items: center;
    scroll-behavior: smooth;
    -ms-overflow-style: none;  /* Ascunde scrollbar pentru IE/Edge */
    scrollbar-width: none;  /* Ascunde scrollbar pentru Firefox */
}
.brands-scroll-wrapper::-webkit-scrollbar {
    display: none; /* Ascunde scrollbar pentru Chrome, Safari, Opera */
}

.brand-item {
    flex: 0 0 auto; 
    width: 140px; 
    text-align: center;
}

/* Efect hover pe logo-uri */
.logo-brand { 
    filter: grayscale(100%); 
    opacity: 0.6;
    transition: all 0.3s ease; 
    cursor: pointer; 
}
.logo-brand:hover { 
    filter: grayscale(0%); 
    opacity: 1;
    transform: scale(1.1); 
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const track = document.getElementById('brands-track');
    const btnLeft = document.getElementById('btn-brand-left');
    const btnRight = document.getElementById('btn-brand-right');
    
    // Distanța pe care o parcurge la un click (aprox 2 branduri)
    const scrollAmount = 300; 

    // Click pe săgeata dreapta
    btnRight.addEventListener('click', () => {
        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 5) {
            track.scrollTo({ left: 0, behavior: 'smooth' }); // Rewind
        } else {
            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    });

    // Click pe săgeata stânga
    btnLeft.addEventListener('click', () => {
        if (track.scrollLeft <= 0) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' }); // Fast forward la final
        } else {
            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        }
    });
});
</script>

<div class="features-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 feature-box"><i class="fas fa-shipping-fast feature-icon"></i><h6 class="fw-bold">Livrare Rapidă</h6><p class="text-muted small mb-0">Expediere în 24h oriunde.</p></div>
            <div class="col-md-4 feature-box border-start-md"><i class="fas fa-sync-alt feature-icon"></i><h6 class="fw-bold">Retur Garantat</h6><p class="text-muted small mb-0">30 de zile drept de retur.</p></div>
            <div class="col-md-4 feature-box border-start-md"><i class="fas fa-medal feature-icon"></i><h6 class="fw-bold">Produse Originale</h6><p class="text-muted small mb-0">Garanție de producător.</p></div>
        </div>
    </div>
</div>

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
    
    const sMarca = document.getElementById('select_marca');
    const sModel = document.getElementById('select_model');
    const sAn = document.getElementById('select_an');
    const sCombustibil = document.getElementById('select_combustibil');
    const sMotor = document.getElementById('select_motor');
    const btnCauta = document.getElementById('btn_cauta_masina');

    // Preluăm variabilele invizibile din PHP
    const pre_marca = "<?= $pre_marca ?>";
    const pre_model = "<?= $pre_model ?>";
    const pre_an = "<?= $pre_an ?>";
    const pre_comb = "<?= $pre_comb ?>";
    const pre_motor = "<?= $masina_selectata_id ?>";

    if(sMarca) {

        // =======================================================
        // A. FUNCȚIA CARE ÎNCARCĂ TOTUL AUTOMAT LA REFRESH
        // =======================================================
        async function incarcaDateleSalvate() {
            // 1. Încărcăm Mărcile
            let res = await fetch('api_masini.php?actiune=get_marci');
            let marci = await res.json();
            marci.forEach(m => sMarca.add(new Option(m, m)));

            // 2. Dacă avem o mașină deja căutată, încărcăm tot traseul
            if (pre_marca && pre_model && pre_an && pre_comb && pre_motor) {
                sMarca.value = pre_marca;
                
                res = await fetch(`api_masini.php?actiune=get_modele&marca=${encodeURIComponent(pre_marca)}`);
                let modele = await res.json();
                modele.forEach(m => sModel.add(new Option(m, m)));
                sModel.disabled = false;
                sModel.value = pre_model;

                res = await fetch(`api_masini.php?actiune=get_ani&marca=${encodeURIComponent(pre_marca)}&model=${encodeURIComponent(pre_model)}`);
                let anii = await res.json();
                anii.forEach(a => sAn.add(new Option(a, a)));
                sAn.disabled = false;
                sAn.value = pre_an;

                res = await fetch(`api_masini.php?actiune=get_combustibil&marca=${encodeURIComponent(pre_marca)}&model=${encodeURIComponent(pre_model)}&an=${encodeURIComponent(pre_an)}`);
                let comb = await res.json();
                comb.forEach(c => sCombustibil.add(new Option(c, c)));
                sCombustibil.disabled = false;
                sCombustibil.value = pre_comb;

                res = await fetch(`api_masini.php?actiune=get_motoare&marca=${encodeURIComponent(pre_marca)}&model=${encodeURIComponent(pre_model)}&an=${encodeURIComponent(pre_an)}&combustibil=${encodeURIComponent(pre_comb)}`);
                let motoare = await res.json();
                motoare.forEach(m => sMotor.add(new Option(m.motorizare, m.id)));
                sMotor.disabled = false;
                sMotor.value = pre_motor;

                if(btnCauta) btnCauta.disabled = false;
            }
        }
        
        // Pornim funcția imediat ce se deschide pagina
        incarcaDateleSalvate();


        // =======================================================
        // B. CE SE ÎNTÂMPLĂ CÂND CLIENTUL DĂ CLICK MANUAL PE MENIURI
        // =======================================================
        sMarca.addEventListener('change', function() {
            sModel.innerHTML = '<option value="">2. Model...</option>';
            sAn.innerHTML = '<option value="">3. An Fabricație...</option>';
            sCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
            sMotor.innerHTML = '<option value="">5. Motorizare...</option>';
            sModel.disabled = true; sAn.disabled = true; sCombustibil.disabled = true; sMotor.disabled = true; 
            if(btnCauta) btnCauta.disabled = true;

            if(this.value) {
                fetch(`api_masini.php?actiune=get_modele&marca=${encodeURIComponent(this.value)}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => sModel.add(new Option(item, item)));
                        sModel.disabled = false;
                    });
            }
        });

        sModel.addEventListener('change', function() {
            sAn.innerHTML = '<option value="">3. An Fabricație...</option>';
            sCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
            sMotor.innerHTML = '<option value="">5. Motorizare...</option>';
            sAn.disabled = true; sCombustibil.disabled = true; sMotor.disabled = true; 
            if(btnCauta) btnCauta.disabled = true;

            if(this.value) {
                fetch(`api_masini.php?actiune=get_ani&marca=${encodeURIComponent(sMarca.value)}&model=${encodeURIComponent(this.value)}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => sAn.add(new Option(item, item)));
                        sAn.disabled = false;
                    });
            }
        });

        sAn.addEventListener('change', function() {
            sCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
            sMotor.innerHTML = '<option value="">5. Motorizare...</option>';
            sCombustibil.disabled = true; sMotor.disabled = true; 
            if(btnCauta) btnCauta.disabled = true;

            if(this.value) {
                fetch(`api_masini.php?actiune=get_combustibil&marca=${encodeURIComponent(sMarca.value)}&model=${encodeURIComponent(sModel.value)}&an=${encodeURIComponent(this.value)}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => sCombustibil.add(new Option(item, item)));
                        sCombustibil.disabled = false;
                    });
            }
        });

        sCombustibil.addEventListener('change', function() {
            sMotor.innerHTML = '<option value="">5. Motorizare...</option>';
            sMotor.disabled = true; 
            if(btnCauta) btnCauta.disabled = true;

            if(this.value) {
                fetch(`api_masini.php?actiune=get_motoare&marca=${encodeURIComponent(sMarca.value)}&model=${encodeURIComponent(sModel.value)}&an=${encodeURIComponent(sAn.value)}&combustibil=${encodeURIComponent(this.value)}`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(item => sMotor.add(new Option(item.motorizare, item.id)));
                        sMotor.disabled = false;
                    });
            }
        });

        sMotor.addEventListener('change', function() {
            if(btnCauta) btnCauta.disabled = !this.value;
        });
    }

    // =======================================================
    // --- 2. SCRIPT AJAX PENTRU COȘ (NEATINS) ---
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

});

// Funcția care face cardul click-abil, dar protejează butonul de Coș
function mergiLaProdus(event, url) {
    // Verificăm dacă click-ul a fost dat pe formularul de coș sau pe butonul Detalii
    if (event.target.closest('.form-adauga-cos') || event.target.closest('.btn-outline-secondary')) {
        return; // Oprim scriptul aici, lăsăm AJAX-ul sau butonul să funcționeze normal!
    }
    
    // Dacă a dat click oriunde altundeva pe card (pe poză, pe text, pe margini), îl trimitem la produs
    window.location.href = url;
}
</script>

<style>
.scale-up { transform: scale(1.3) translate(-35%, -35%) !important; transition: transform 0.2s; }
</style>

</body>
</html>