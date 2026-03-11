<?php
session_start();

// --- 1. VERIFICARE LOGIN ---
// Dacă un vizitator neautentificat încearcă să intre aici, îl trimitem la poartă (login)
if (!isset($_SESSION['client_id'])) {
    header("Location: login_client.php");
    exit();
}

// --- 2. CONECTARE DB ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

$id_client = $_SESSION['client_id'];

// --- 3. ȘTERGERE DE LA FAVORITE ---
// Dacă omul apasă pe butonul de ștergere (X) de pe card
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id_stergere = (int)$_GET['id'];
    $stmt_del = $pdo->prepare("DELETE FROM favorite WHERE id_client = ? AND id_produs = ?");
    $stmt_del->execute([$id_client, $id_stergere]);
    
    // Refresh rapid ca să dispară piesa din listă
    header("Location: favorite.php");
    exit();
}

// --- 4. PRELUARE PIESE FAVORITE DIN BAZA DE DATE ---
// Facem un JOIN între tabelul 'favorite' și 'produse' ca să aducem detaliile pieselor
$stmt_fav = $pdo->prepare("
    SELECT p.* FROM favorite f
    JOIN produse p ON f.id_produs = p.id
    WHERE f.id_client = ?
    ORDER BY f.data_adaugare DESC
");
$stmt_fav->execute([$id_client]);
$produse_favorite = $stmt_fav->fetchAll();

// Pentru numărul roșu de deasupra coșului
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritele Mele - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Carduri Favorite */
        .fav-card { transition: transform 0.3s, box-shadow 0.3s; height: 100%; display: flex; flex-direction: column; border-radius: 12px; border: none; overflow: hidden; position: relative; }
        .fav-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .fav-img { height: 180px; object-fit: contain; padding: 15px; background: white; border-bottom: 1px solid #f8f9fa; }
        
        /* Buton Ștergere de pe card */
        .btn-remove-fav { position: absolute; top: 10px; right: 10px; background: white; border: 1px solid #eee; color: #dc3545; border-radius: 50%; width: 35px; height: 35px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 10; text-decoration: none;}
        .btn-remove-fav:hover { background: #dc3545; color: white; border-color: #dc3545; }

        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
        .scale-up { transform: scale(1.3) translate(-35%, -35%) !important; transition: transform 0.2s; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5 mb-5 flex-grow-1">
    
    <div class="d-flex align-items-center mb-4">
        <h2 class="fw-bold m-0"><i class="fas fa-heart text-danger me-2"></i> Favoritele Mele</h2>
        <span class="badge bg-secondary ms-3 fs-6 rounded-pill"><?= count($produse_favorite) ?> piese</span>
    </div>

    <?php if (count($produse_favorite) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($produse_favorite as $produs): 
                $poza = !empty($produs['imagine']) ? "uploads/" . $produs['imagine'] : "https://via.placeholder.com/300x200?text=Fara+Poza";
            ?>
                <div class="col">
                    <div class="card fav-card shadow-sm bg-white" style="cursor: pointer;" onclick="mergiLaProdus(event, 'produs.php?id=<?= $produs['id'] ?>')">
                        
                        <a href="favorite.php?action=remove&id=<?= $produs['id'] ?>" class="btn-remove-fav" title="Șterge din favorite">
                            <i class="fas fa-times"></i>
                        </a>

                        <img src="<?= $poza ?>" class="card-img-top fav-img" alt="<?= htmlspecialchars($produs['nume_piesa']) ?>">
                        
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="card-title text-dark fw-bold mb-1" style="font-size: 0.95rem; line-height: 1.3;">
                                <a href="produs.php?id=<?= $produs['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($produs['nume_piesa']) ?>
                                </a>
                            </h6>
                            <small class="text-muted mb-3 d-block">Cod OE: <?= htmlspecialchars($produs['cod_piesa']) ?></small>
                            
                            <div class="mt-auto">
                                <h5 class="fw-bold text-primary mb-3"><?= number_format($produs['pret'], 2) ?> RON</h5>
                                
                                <form action="adauga_cos.php" method="POST" class="form-adauga-cos w-100" data-nume="<?= htmlspecialchars($produs['nume_piesa'], ENT_QUOTES) ?>" data-poza="<?= $poza ?>">
                                    <input type="hidden" name="id_produs" value="<?= $produs['id'] ?>">
                                    <input type="hidden" name="cantitate" value="1">
                                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold" <?= $produs['stoc'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-cart-plus me-1"></i> <?= $produs['stoc'] > 0 ? 'În Coș' : 'Stoc Epuizat' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center bg-white p-5 rounded-4 shadow-sm border mt-4">
            <i class="far fa-heart fa-4x text-muted mb-3 opacity-50"></i>
            <h4 class="fw-bold text-dark">Lista ta de favorite este goală</h4>
            <p class="text-muted mb-4">Salvează aici piesele pe care vrei să le cumperi mai târziu, apăsând pe inimioara din pagina produsului.</p>
            <a href="index.php" class="btn btn-primary px-4 py-2 fw-bold"><i class="fas fa-store me-2"></i> Mergi la magazin</a>
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
        <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Înapoi</button>
        <a href="cos.php" class="btn btn-success px-4 py-2 fw-bold shadow-sm">Spre Coș <i class="fas fa-chevron-right ms-1" style="font-size:0.8rem;"></i></a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Script pentru Adăugare în Coș (Fără Refresh)
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
            .catch(error => console.error('Eroare:', error));
        });
    });
});

// Funcția pentru a face cardul clickabil (dar evităm butoanele din interior)
function mergiLaProdus(event, url) {
    if (event.target.closest('.form-adauga-cos') || event.target.closest('.btn-remove-fav')) {
        return; 
    }
    window.location.href = url;
}
</script>

</body>
</html>