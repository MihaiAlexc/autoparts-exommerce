<?php
session_start();
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Dacă nu este logat, îl trimitem la poartă (login)
if (!isset($_SESSION['client_id'])) {
    header("Location: login_client.php");
    exit();
}

$id_client = $_SESSION['client_id'];
$tab_activ = 'comenzi'; // Tab-ul afișat implicit
$mesaj_update = "";

// --- 1. SALVARE DATE PERSONALE (Dacă a dat submit la formular) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profil'])) {
    $tab_activ = 'profil'; // Păstrăm clientul pe tab-ul de profil după salvare
    
    $nume_nou = trim($_POST['nume']);
    $telefon_nou = trim($_POST['telefon']);
    $adresa_noua = trim($_POST['adresa']);
    
    try {
        $stmt_up = $pdo->prepare("UPDATE clienti SET nume = ?, telefon = ?, adresa = ? WHERE id = ?");
        $stmt_up->execute([$nume_nou, $telefon_nou, $adresa_noua, $id_client]);
        
        $_SESSION['client_nume'] = $nume_nou; // Actualizăm și numele din sesiune pentru header
        
        $mesaj_update = "<div class='alert alert-success d-flex align-items-center mb-4 shadow-sm'>
                            <i class='fas fa-check-circle me-2'></i> 
                            <div>Datele tale au fost actualizate cu succes!</div>
                         </div>";
    } catch (Exception $e) {
        $mesaj_update = "<div class='alert alert-danger shadow-sm'>A apărut o eroare la salvarea datelor.</div>";
    }
}

// --- 2. PRELUARE DATE CLIENT (Pentru formularul de profil) ---
$stmt_user = $pdo->prepare("SELECT * FROM clienti WHERE id = ?");
$stmt_user->execute([$id_client]);
$client_data = $stmt_user->fetch();

// --- 3. PRELUARE COMENZI (Pentru istoricul comenzilor) ---
$stmt_comenzi = $pdo->prepare("SELECT * FROM comenzi WHERE id_client = ? ORDER BY data_comanda DESC");
$stmt_comenzi->execute([$id_client]);
$comenzile_mele = $stmt_comenzi->fetchAll();

// Helper pentru afișare status frumos
function afiseaza_status($st) {
    if($st == 'primita') return ['text' => 'Comandă Primită', 'class' => 'bg-secondary', 'icon' => 'fa-clipboard-check'];
    if($st == 'in_locatie') return ['text' => 'Sosit în Locație', 'class' => 'bg-warning text-dark', 'icon' => 'fa-warehouse'];
    if($st == 'curier') return ['text' => 'La Curier', 'class' => 'bg-primary', 'icon' => 'fa-truck'];
    if($st == 'livrata') return ['text' => 'Livrată', 'class' => 'bg-success', 'icon' => 'fa-check-circle'];
    return ['text' => 'În procesare', 'class' => 'bg-secondary', 'icon' => 'fa-clock'];
}

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contul Meu - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        
        /* Sidebar styling */
        .sidebar-menu .list-group-item { border: none; padding: 15px 20px; font-weight: 500; color: #495057; transition: all 0.2s; border-radius: 8px; margin-bottom: 5px; }
        .sidebar-menu .list-group-item:hover { background-color: #f8f9fa; color: #0d6efd; }
        .sidebar-menu .list-group-item.active { background-color: #0d6efd; color: white; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2); }
        
        /* Card comenzi styling */
        .card-order { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 25px; overflow: hidden; transition: transform 0.2s; }
        .card-order:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
        .card-header-order { background: white; border-bottom: 1px solid #f1f1f1; padding: 15px 25px; }
        .status-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 15px; border-radius: 20px; color: white; font-weight: 600; font-size: 0.85rem; }
        
        /* Profil styling */
        .profile-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; }
        .form-control { border-radius: 8px; padding: 10px 15px; border: 1px solid #dee2e6; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container flex-grow-1 mt-5 mb-5">
    
    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
            <?= strtoupper(substr($client_data['nume'], 0, 1)) ?>
        </div>
        <div>
            <h2 class="fw-bold text-dark m-0">Salut, <?= htmlspecialchars($client_data['nume']) ?>!</h2>
            <p class="text-muted m-0">Gestionează-ți comenzile și datele personale din acest panou.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-3">
            <div class="bg-white p-3 rounded-4 shadow-sm">
                <div class="nav flex-column nav-pills sidebar-menu" role="tablist" aria-orientation="vertical">
                    <button class="nav-link text-start <?= $tab_activ == 'comenzi' ? 'active' : '' ?>" id="v-pills-comenzi-tab" data-bs-toggle="pill" data-bs-target="#v-pills-comenzi" type="button" role="tab">
                        <i class="fas fa-box me-2"></i> Istoric Comenzi
                    </button>
                    <button class="nav-link text-start <?= $tab_activ == 'profil' ? 'active' : '' ?>" id="v-pills-profil-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profil" type="button" role="tab">
                        <i class="fas fa-user-edit me-2"></i> Datele Mele
                    </button>
                    <hr class="my-2 border-light">
                    <a href="logout_client.php" class="nav-link text-start text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Deconectare
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                
                <div class="tab-pane fade <?= $tab_activ == 'comenzi' ? 'show active' : '' ?>" id="v-pills-comenzi" role="tabpanel">
                    <h4 class="mb-4 fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i> Comenzile plasate de mine</h4>
                    
                    <?php if (count($comenzile_mele) > 0): ?>
                        <?php foreach ($comenzile_mele as $c): ?>
                            <?php $status_data = afiseaza_status($c['status'] ?? 'primita'); ?>
                            
                            <div class="card card-order">
                                <div class="card-header-order d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-light text-dark border p-2 fs-6">#<?= $c['id'] ?></span>
                                        <div class="status-pill <?= $status_data['class'] ?>">
                                            <i class="fas <?= $status_data['icon'] ?>"></i> <?= $status_data['text'] ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold fs-5 text-dark text-success"><?= $c['total'] ?> Lei</div>
                                        <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d.m.Y H:i', strtotime($c['data_comanda'])) ?></small>
                                    </div>
                                </div>
                                <div class="card-body bg-white p-4">
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-md-6">
                                            <p class="mb-1 text-muted small text-uppercase fw-bold">Adresă de livrare:</p>
                                            <p class="mb-0 fw-medium"><i class="fas fa-map-marker-alt text-danger me-1"></i> <?= htmlspecialchars($c['adresa'] ?: 'Nu este specificată') ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead class="text-secondary" style="background-color: #f8f9fa;">
                                                <tr>
                                                    <th class="ps-3 py-2 rounded-start">Produs</th>
                                                    <th class="py-2 text-center">Cant.</th>
                                                    <th class="text-end pe-3 py-2 rounded-end">Preț Buc.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmt_prod = $pdo->prepare("SELECT d.*, p.nume_piesa FROM detalii_comanda d JOIN produse p ON d.id_produs = p.id WHERE d.id_comanda = ?");
                                                $stmt_prod->execute([$c['id']]);
                                                $detalii = $stmt_prod->fetchAll();
                                                ?>
                                                <?php foreach ($detalii as $d): ?>
                                                    <tr class="border-bottom border-light">
                                                        <td class="ps-3 py-2 fw-medium"><?= htmlspecialchars($d['nume_piesa']) ?></td>
                                                        <td class="py-2 text-center text-muted"><?= $d['cantitate'] ?>x</td>
                                                        <td class="text-end pe-3 py-2"><?= $d['pret'] ?> Lei</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-light text-center p-5 border border-dashed rounded-4 shadow-sm bg-white">
                            <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                            <h4 class="text-dark">Nu ai plasat nicio comandă încă.</h4>
                            <p class="text-muted">Aici va apărea istoricul tuturor pieselor achiziționate.</p>
                            <a href="index.php" class="btn btn-primary mt-2 px-4 rounded-pill">Începe Cumpărăturile <i class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade <?= $tab_activ == 'profil' ? 'show active' : '' ?>" id="v-pills-profil" role="tabpanel">
                    <h4 class="mb-4 fw-bold"><i class="fas fa-address-card text-primary me-2"></i> Date de Livrare & Facturare</h4>
                    
                    <div class="card profile-card">
                        <div class="card-body p-4 p-md-5">
                            
                            <?= $mesaj_update ?>
                            
                            <div class="alert alert-info bg-opacity-10 border-info mb-4 d-flex align-items-center">
                                <i class="fas fa-info-circle text-info fs-4 me-3"></i>
                                <span class="small">Completează-ți numărul de telefon și adresa aici, iar la următoarea comandă nu va mai fi nevoie să le tastezi!</span>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="update_profil" value="1">
                                
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Nume Complet <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                            <input type="text" name="nume" class="form-control" value="<?= htmlspecialchars($client_data['nume']) ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Adresa de Email <i class="fas fa-lock text-muted ms-1" title="Nu poate fi modificată"></i></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($client_data['email']) ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Număr de Telefon</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="text" name="telefon" class="form-control" placeholder="Ex: 07xx xxx xxx" value="<?= htmlspecialchars($client_data['telefon'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Adresă de Livrare Principală</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                            <textarea name="adresa" class="form-control" rows="3" placeholder="Oraș, Stradă, Număr, Bloc, Etaj..."><?= htmlspecialchars($client_data['adresa'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success fw-bold px-5 py-2 rounded-pill shadow-sm">
                                        <i class="fas fa-save me-2"></i> Salvează Modificările
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>