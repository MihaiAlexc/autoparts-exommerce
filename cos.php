<?php
session_start();

// --- CONECTARE DB ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare: " . $e->getMessage()); }

// --- LOGICA CART (Scoatere produs sau Golire) ---
if (isset($_GET['action'])) {
    // Scoatere un singur produs
    if ($_GET['action'] == 'remove' && isset($_GET['id'])) {
        unset($_SESSION['cos'][$_GET['id']]);
        header("Location: cos.php"); 
        exit();
    }
    // Golire totală
    if ($_GET['action'] == 'goleste') {
        $_SESSION['cos'] = [];
        header("Location: cos.php"); 
        exit();
    }
}

// --- PRELUARE PRODUSE PENTRU AFIȘARE ---
$produse_in_cos = [];
$total_general = 0;

if (isset($_SESSION['cos']) && count($_SESSION['cos']) > 0) {
    $ids = array_keys($_SESSION['cos']);
    $in  = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id, nume_piesa, cod_piesa, pret, imagine FROM produse WHERE id IN ($in)");
    $stmt->execute($ids);
    $produse_db = $stmt->fetchAll();
    
    foreach ($produse_db as $p) {
        $cantitate = $_SESSION['cos'][$p['id']];
        $subtotal = $p['pret'] * $cantitate;
        
        // Adăugăm datele calculate în array pentru a le afișa în HTML
        $p['cantitate_cos'] = $cantitate;
        $p['subtotal'] = $subtotal;
        
        $total_general += $subtotal;
        $produse_in_cos[] = $p;
    }
}
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;

// --- PRELUARE DATE CLIENT (Auto-completare Checkout) ---
$nume_precompletat = "";
$telefon_precompletat = "";
$adresa_precompletata = "";

if (isset($_SESSION['client_id'])) {
    $stmt_client = $pdo->prepare("SELECT nume, telefon, adresa FROM clienti WHERE id = ?");
    $stmt_client->execute([$_SESSION['client_id']]);
    $client_info = $stmt_client->fetch();
    
    if ($client_info) {
        $nume_precompletat = $client_info['nume'];
        $telefon_precompletat = $client_info['telefon'];
        $adresa_precompletata = $client_info['adresa'];
    }
}

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coșul tău - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar (Consistență cu restul site-ului) */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        
        /* Cart Styling Premium */
        .cart-title { font-weight: 700; color: #2c3e50; margin-bottom: 25px; font-size: 1.8rem; }
        
        .cart-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); background: #fff; margin-bottom: 20px; overflow: hidden; }
        .cart-header { background: #fff; border-bottom: 2px solid #f8f9fa; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; }
        .cart-header h5 { font-weight: 700; color: #34495e; margin: 0; font-size: 1.1rem; }
        
        /* Lista produse coș */
        .cart-item { padding: 25px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; transition: background 0.2s; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item:hover { background-color: #fcfcfc; }
        
        .item-img-wrap { width: 90px; height: 90px; flex-shrink: 0; background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 5px; display: flex; align-items: center; justify-content: center; }
        .item-img-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        .item-details { flex-grow: 1; padding: 0 20px; }
        .item-title { font-weight: 700; color: #2c3e50; margin-bottom: 5px; font-size: 1.05rem; }
        .item-meta { font-size: 0.85rem; color: #7f8c8d; }
        
        .item-price-calc { text-align: right; min-width: 120px; }
        .item-subtotal { font-weight: 800; font-size: 1.2rem; color: #0d6efd; }
        .item-unit-price { font-size: 0.8rem; color: #95a5a6; }
        
        .btn-remove { color: #e74c3c; background: transparent; border: none; font-size: 1.2rem; transition: color 0.2s; cursor: pointer; padding: 5px; }
        .btn-remove:hover { color: #c0392b; }

        /* Checkout Sidebar */
        .checkout-sidebar { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 25px; position: sticky; top: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; color: #555; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #eee; font-size: 1.3rem; font-weight: 800; color: #2c3e50; }
        
        .form-floating > .form-control { border-radius: 8px; border-color: #ddd; }
        .form-floating > .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        .btn-checkout { background-color: #27ae60; border: none; font-weight: bold; font-size: 1.1rem; border-radius: 8px; padding: 14px; transition: all 0.3s ease; }
        .btn-checkout:hover { background-color: #219653; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3); }
        
        .trust-icons { margin-top: 20px; text-align: center; color: #7f8c8d; font-size: 0.8rem; }
        .trust-icons i { font-size: 1.2rem; margin-bottom: 5px; color: #bdc3c7; }

        /* Empty Cart */
        .empty-cart { padding: 60px 20px; text-align: center; }
        .empty-cart i { font-size: 5rem; color: #dee2e6; margin-bottom: 20px; }
        
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>


<div class="container mt-5 mb-5 flex-grow-1">
    
    <?php if (count($produse_in_cos) > 0): ?>
        <h2 class="cart-title">Coșul tău de cumpărături</h2>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="cart-card">
                    <div class="cart-header">
                        <h5>Produse adăugate (<?= count($produse_in_cos) ?>)</h5>
                        <a href="cos.php?action=goleste" class="text-danger text-decoration-none small" onclick="return confirm('Ești sigur că vrei să golești coșul?');"><i class="fas fa-trash-alt me-1"></i> Golește tot</a>
                    </div>
                    
                    <div class="cart-body">
                        <?php foreach ($produse_in_cos as $item): ?>
                            <?php $poza = !empty($item['imagine']) ? "uploads/" . $item['imagine'] : "https://via.placeholder.com/150"; ?>
                            <div class="cart-item">
                                <div class="item-img-wrap">
                                    <img src="<?= $poza ?>" alt="<?= htmlspecialchars($item['nume_piesa']) ?>">
                                </div>
                                
                                <div class="item-details">
                                    <div class="item-title"><?= htmlspecialchars($item['nume_piesa']) ?></div>
                                    <div class="item-meta mb-2">Cod OE: <strong><?= htmlspecialchars($item['cod_piesa']) ?></strong></div>
                                    <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.75rem;"><i class="fas fa-check me-1"></i> În stoc</div>
                                </div>
                                
                                <div class="item-price-calc d-flex flex-column align-items-end">
                                    <a href="cos.php?action=remove&id=<?= $item['id'] ?>" class="btn-remove mb-3" title="Șterge produs"><i class="fas fa-times"></i></a>
                                    <div class="item-subtotal"><?= number_format($item['subtotal'], 2) ?> lei</div>
                                    <div class="item-unit-price"><?= $item['cantitate_cos'] ?> buc. x <?= number_format($item['pret'], 2) ?> lei</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="d-none d-lg-flex align-items-center text-muted small mt-3">
                    <i class="fas fa-shield-alt text-success fs-5 me-2"></i> Toate produsele beneficiază de garanție de la producător și retur simplificat în 14 zile.
                </div>
            </div>

            <div class="col-lg-4">
                <div class="checkout-sidebar">
                    <h5 class="fw-bold text-dark mb-4">Sumar Comandă</h5>
                    
                    <div class="summary-row">
                        <span>Cost produse:</span>
                        <span class="fw-medium text-dark"><?= number_format($total_general, 2) ?> lei</span>
                    </div>
                    <div class="summary-row text-success">
                        <span>Cost livrare:</span>
                        <span class="fw-bold">GRATUIT</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span class="text-primary"><?= number_format($total_general, 2) ?> lei</span>
                    </div>
                    <div class="text-end text-muted mb-4" style="font-size: 0.75rem;">TVA inclus</div>

                    <form action="trimite_comanda.php" method="POST">
                        <h6 class="fw-bold text-secondary mb-3 mt-2" style="font-size: 0.85rem; text-transform: uppercase;">Date Livrare</h6>
                        
                        <?php if(!isset($_SESSION['client_id'])): ?>
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i> Ai cont? <a href="login_client.php" class="alert-link fw-bold">Loghează-te</a> pentru auto-completare.
                            </div>
                        <?php endif; ?>

                        <div class="form-floating mb-3">
                            <input type="text" name="nume" class="form-control" id="numeInput" placeholder="Numele tău complet" value="<?= htmlspecialchars($nume_precompletat) ?>" required>
                            <label for="numeInput"><i class="fas fa-user text-muted me-1"></i> Nume Complet</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="tel" name="telefon" class="form-control" id="telInput" placeholder="07XX XXX XXX" value="<?= htmlspecialchars($telefon_precompletat) ?>" required>
                            <label for="telInput"><i class="fas fa-phone text-muted me-1"></i> Telefon</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <textarea name="adresa" class="form-control" id="adresaInput" placeholder="Adresa completă" style="height: 100px" required><?= htmlspecialchars($adresa_precompletata) ?></textarea>
                            <label for="adresaInput"><i class="fas fa-map-marker-alt text-muted me-1"></i> Adresă de livrare (Jud, Oraș, Str)</label>
                        </div>
                        
                        <input type="hidden" name="total_comanda" value="<?= $total_general ?>">
                        
                        <button type="submit" class="btn btn-checkout text-white w-100">
                            Finalizează Comanda <i class="fas fa-chevron-right ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="trust-icons row mt-4 pt-3 border-top">
                        <div class="col-4">
                            <i class="fas fa-lock text-success"></i><br>Date Securizate
                        </div>
                        <div class="col-4">
                            <i class="fas fa-box text-primary"></i><br>Ambalare Sigură
                        </div>
                        <div class="col-4">
                            <i class="fas fa-hand-holding-usd text-warning"></i><br>Plată Ramburs
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="cart-card empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3 class="fw-bold text-dark mb-3">Coșul tău este momentan gol</h3>
            <p class="text-muted mb-4">Se pare că nu ai adăugat încă nicio piesă auto. Caută piesa potrivită pentru mașina ta!</p>
            <a href="index.php" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                <i class="fas fa-search me-2"></i> Începe căutarea
            </a>
        </div>
    <?php endif; ?>
    
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
