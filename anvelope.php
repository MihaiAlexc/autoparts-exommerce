
<?php
session_start();

// --- CONECTARE DB ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Numar produse in cos
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;

// --- MOTOR DE CĂUTARE ANVELOPE ---
$where_clauses = ["p.categorie = 'anvelope'"];
$params = [];

if (isset($_GET['latime']) && $_GET['latime'] != '') {
    $where_clauses[] = "a.latime = ?";
    $params[] = $_GET['latime'];
}
if (isset($_GET['inaltime']) && $_GET['inaltime'] != '') {
    $where_clauses[] = "a.inaltime = ?";
    $params[] = $_GET['inaltime'];
}
if (isset($_GET['raza']) && $_GET['raza'] != '') {
    $where_clauses[] = "a.raza = ?";
    $params[] = $_GET['raza'];
}
if (isset($_GET['sezon']) && $_GET['sezon'] != '') {
    $where_clauses[] = "a.sezon = ?";
    $params[] = $_GET['sezon'];
}

$where_sql = implode(' AND ', $where_clauses);

// Extragem produsele din DB făcând JOIN cu tabelul nou anvelope_detalii
$sql = "SELECT p.*, a.latime, a.inaltime, a.raza, a.sezon 
        FROM produse p 
        LEFT JOIN anvelope_detalii a ON p.id = a.id_produs 
        WHERE $where_sql 
        ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$anvelope = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anvelope Auto - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; display: flex; flex-direction: column; min-height: 100vh; font-family: 'Segoe UI', sans-serif;}
        .filter-box { background: linear-gradient(135deg, #1e293b, #334155); border-radius: 12px; padding: 30px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); margin-top: -30px; position: relative; z-index: 10;}
        .tire-card { transition: transform 0.3s; height: 100%; border-radius: 12px; border: none; overflow: hidden; }
        .tire-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .tire-img { height: 200px; object-fit: contain; padding: 15px; background: white; border-bottom: 1px solid #f8f9fa; }
        .tire-specs span { display: inline-block; background: #e2e8f0; color: #334155; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; margin-right: 5px; margin-bottom: 5px; }
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="bg-primary text-white text-center py-5" style="background: url('https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&q=80&w=1200') center/cover; position: relative;">
    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6);"></div>
    <div class="container position-relative z-1 py-4">
        <h1 class="display-5 fw-bold mb-3">Găsește anvelopele perfecte</h1>
        <p class="lead mb-0 opacity-75">Caută după dimensiuni și sezon pentru o potrivire 100% garantată.</p>
    </div>
</div>

<div class="container mb-5 flex-grow-1">
    
    <div class="filter-box mb-5">
        <form action="anvelope.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label text-light fw-bold small">Lățime</label>
                <select name="latime" class="form-select border-0 shadow-sm">
                    <option value="">Toate</option>
                    <option value="175" <?= (isset($_GET['latime']) && $_GET['latime'] == '175') ? 'selected' : '' ?>>175</option>
                    <option value="185" <?= (isset($_GET['latime']) && $_GET['latime'] == '185') ? 'selected' : '' ?>>185</option>
                    <option value="195" <?= (isset($_GET['latime']) && $_GET['latime'] == '195') ? 'selected' : '' ?>>195</option>
                    <option value="205" <?= (isset($_GET['latime']) && $_GET['latime'] == '205') ? 'selected' : '' ?>>205</option>
                    <option value="225" <?= (isset($_GET['latime']) && $_GET['latime'] == '225') ? 'selected' : '' ?>>225</option>
                    <option value="245" <?= (isset($_GET['latime']) && $_GET['latime'] == '245') ? 'selected' : '' ?>>245</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-light fw-bold small">Înălțime</label>
                <select name="inaltime" class="form-select border-0 shadow-sm">
                    <option value="">Toate</option>
                    <option value="45" <?= (isset($_GET['inaltime']) && $_GET['inaltime'] == '45') ? 'selected' : '' ?>>45</option>
                    <option value="50" <?= (isset($_GET['inaltime']) && $_GET['inaltime'] == '50') ? 'selected' : '' ?>>50</option>
                    <option value="55" <?= (isset($_GET['inaltime']) && $_GET['inaltime'] == '55') ? 'selected' : '' ?>>55</option>
                    <option value="60" <?= (isset($_GET['inaltime']) && $_GET['inaltime'] == '60') ? 'selected' : '' ?>>60</option>
                    <option value="65" <?= (isset($_GET['inaltime']) && $_GET['inaltime'] == '65') ? 'selected' : '' ?>>65</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-light fw-bold small">Rază</label>
                <select name="raza" class="form-select border-0 shadow-sm">
                    <option value="">Toate</option>
                    <option value="R15" <?= (isset($_GET['raza']) && $_GET['raza'] == 'R15') ? 'selected' : '' ?>>R15</option>
                    <option value="R16" <?= (isset($_GET['raza']) && $_GET['raza'] == 'R16') ? 'selected' : '' ?>>R16</option>
                    <option value="R17" <?= (isset($_GET['raza']) && $_GET['raza'] == 'R17') ? 'selected' : '' ?>>R17</option>
                    <option value="R18" <?= (isset($_GET['raza']) && $_GET['raza'] == 'R18') ? 'selected' : '' ?>>R18</option>
                    <option value="R19" <?= (isset($_GET['raza']) && $_GET['raza'] == 'R19') ? 'selected' : '' ?>>R19</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-light fw-bold small">Sezon</label>
                <select name="sezon" class="form-select border-0 shadow-sm">
                    <option value="">Toate sezoanele</option>
                    <option value="Vara" <?= (isset($_GET['sezon']) && $_GET['sezon'] == 'Vara') ? 'selected' : '' ?>>Vară ☀️</option>
                    <option value="Iarna" <?= (isset($_GET['sezon']) && $_GET['sezon'] == 'Iarna') ? 'selected' : '' ?>>Iarnă ❄️</option>
                    <option value="All-Season" <?= (isset($_GET['sezon']) && $_GET['sezon'] == 'All-Season') ? 'selected' : '' ?>>All-Season 🌤️</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" style="height: 38px;">
                    <i class="fas fa-search me-2"></i> Caută Anvelope
                </button>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
        <h4 class="fw-bold m-0 text-dark">Rezultate Căutare</h4>
        <span class="badge bg-secondary rounded-pill"><?= count($anvelope) ?> produse găsite</span>
    </div>

    <?php if (count($anvelope) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($anvelope as $produs): 
                $poza = !empty($produs['imagine']) ? "uploads/" . $produs['imagine'] : "https://via.placeholder.com/300x200?text=Fara+Poza";
            ?>
                <div class="col">
                    <div class="card tire-card shadow-sm bg-white" style="cursor: pointer;" onclick="window.location.href='produs.php?id=<?= $produs['id'] ?>'">
                        <img src="<?= $poza ?>" class="card-img-top tire-img" alt="<?= htmlspecialchars($produs['nume_piesa']) ?>">
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-dark fw-bold mb-2" style="font-size: 0.95rem; line-height: 1.3;">
                                <?= htmlspecialchars($produs['nume_piesa']) ?>
                            </h6>
                            
                            <?php if(!empty($produs['latime'])): ?>
                                <div class="tire-specs mb-3">
                                    <span><?= htmlspecialchars($produs['latime']) ?>/<?= htmlspecialchars($produs['inaltime']) ?> <?= htmlspecialchars($produs['raza']) ?></span>
                                    <span><i class="fas fa-cloud-sun text-primary"></i> <?= htmlspecialchars($produs['sezon']) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <h5 class="fw-bold text-primary mb-3"><?= number_format($produs['pret'], 2) ?> RON</h5>
                                
                                <form action="adauga_cos.php" method="POST" class="form-adauga-cos w-100" data-nume="<?= htmlspecialchars($produs['nume_piesa'], ENT_QUOTES) ?>" data-poza="<?= $poza ?>">
                                    <input type="hidden" name="id_produs" value="<?= $produs['id'] ?>">
                                    <input type="hidden" name="cantitate" value="1">
                                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold" <?= $produs['stoc'] <= 0 ? 'disabled' : '' ?> onclick="event.stopPropagation();">
                                        <i class="fas fa-cart-plus me-1"></i> <?= $produs['stoc'] > 0 ? 'Adaugă în Coș' : 'Stoc Epuizat' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="text-muted">Nu am găsit nicio anvelopă cu aceste specificații.</h5>
            <p class="text-secondary small">Încearcă să modifici filtrele de căutare.</p>
            <a href="anvelope.php" class="btn btn-outline-secondary mt-2">Resetează Filtrele</a>
        </div>
    <?php endif; ?>

</div>

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
        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Continuă Cumpărăturile</button>
        <a href="cos.php" class="btn btn-success px-4 py-2 fw-bold shadow-sm">Spre Coș <i class="fas fa-chevron-right ms-1" style="font-size:0.8rem;"></i></a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Script pentru Adăugare în Coș prin AJAX
document.addEventListener("DOMContentLoaded", function() {
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
                    if(cartBadge) cartBadge.textContent = data.total_produse;
                    modalNumePiesa.textContent = numePiesa;
                    modalPozaPiesa.src = pozaPiesa;
                    modalCos.show();
                }
            })
            .catch(error => console.error('Eroare:', error));
        });
    });
});
</script>

</body>
</html>