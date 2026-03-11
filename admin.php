<?php
// --- 1. SECURITATE ---
session_start();
if (!isset($_SESSION['admin_logat']) || $_SESSION['admin_logat'] !== true) {
    header("Location: login.php");
    exit();
}

// --- CONFIGURARE ---
ini_set('display_errors', 1); error_reporting(E_ALL);
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Am setat Dashboard ca pagină principală implicită
$pagina_curenta = $_GET['pagina'] ?? 'dashboard';
$mesaj = "";

// --- 2. LOGICA (Backend) ---

// A. ACTUALIZARE STATUS COMANDĂ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actiune']) && $_POST['actiune'] == 'update_status') {
    $id_comanda = $_POST['id_comanda'];
    $status_nou = $_POST['status_nou'];
    
    $pdo->prepare("UPDATE comenzi SET status = ? WHERE id = ?")->execute([$status_nou, $id_comanda]);
    header("Location: admin.php?pagina=comenzi");
    exit();
}

// B. ȘTERGERE COMANDĂ
if (isset($_GET['sterge_comanda'])) {
    $id = $_GET['sterge_comanda'];
    $pdo->prepare("DELETE FROM detalii_comanda WHERE id_comanda = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM comenzi WHERE id = ?")->execute([$id]);
    header("Location: admin.php?pagina=comenzi");
    exit();
}

// C. ADĂUGARE PRODUS 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actiune']) && $_POST['actiune'] == 'adauga_produs') {
    $categorie = empty($_POST['categorie']) ? null : $_POST['categorie'];
    $nume = $_POST['nume']; 
    $descriere = $_POST['descriere']; 
    $cod = $_POST['cod'];
    
    $pret_achizitie = (float)$_POST['pret_achizitie'];
    $adaos = (int)$_POST['adaos'];
    $tva = (int)$_POST['tva'];
    $pret_final = (float)$_POST['pret_final']; 

    $stoc = (int)$_POST['stoc']; 

    $nume_poza_db = "";
    if (isset($_FILES['poza']) && $_FILES['poza']['error'] == 0) {
        $cale = "uploads/" . time() . "_" . basename($_FILES["poza"]["name"]);
        if (move_uploaded_file($_FILES["poza"]["tmp_name"], $cale)) $nume_poza_db = basename($cale);
    }
    
    try {
        $sql = "INSERT INTO produse (categorie, nume_piesa, descriere, cod_piesa, pret_achizitie, adaos, tva, pret, stoc, imagine) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$categorie, $nume, $descriere, $cod, $pret_achizitie, $adaos, $tva, $pret_final, $stoc, $nume_poza_db]);
        $id_nou = $pdo->lastInsertId();
        
        if (!empty($_POST['masini'])) {
            $stmt_comp = $pdo->prepare("INSERT INTO compatibilitati (id_produs, id_masina) VALUES (?, ?)");
            foreach ($_POST['masini'] as $masina_id) {
                $stmt_comp->execute([$id_nou, $masina_id]);
            }
        }
        
        $mesaj = "<div class='alert success'>✅ Produs adăugat!</div>";
    } catch (Exception $e) { $mesaj = "<div class='alert error'>Eroare: " . $e->getMessage() . "</div>"; }
}

// D. ȘTERGERE PRODUS
if (isset($_GET['sterge_produs'])) {
    $id_de_sters = $_GET['sterge_produs'];
    try {
        $pdo->prepare("DELETE FROM compatibilitati WHERE id_produs = ?")->execute([$id_de_sters]);
        $pdo->prepare("DELETE FROM produse WHERE id = ?")->execute([$id_de_sters]);
        $mesaj = "<div class='alert success'>✅ Produsul a fost șters!</div>";
    } catch (Exception $e) {
        $mesaj = "<div class='alert error'>❌ Nu poți șterge acest produs deoarece este deja într-o comandă.</div>";
    }
}

// --- 3. PRELUARE DATE COMUNE ---
$masini = $pdo->query("SELECT * FROM masini")->fetchAll();
$comenzi = $pdo->query("SELECT * FROM comenzi ORDER BY data_comanda DESC")->fetchAll();

$search = $_GET['cauta'] ?? '';
if ($search != '') {
    $stmt = $pdo->prepare("SELECT * FROM produse WHERE cod_piesa LIKE ? OR nume_piesa LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
    $toate_produsele = $stmt->fetchAll();
} else {
    $toate_produsele = $pdo->query("SELECT * FROM produse ORDER BY id DESC")->fetchAll();
}

// --- STATISTICI CARDURI GENERALE ---
$stat_vanzari = $pdo->query("SELECT SUM(total) FROM comenzi")->fetchColumn() ?: 0;
$stat_comenzi_noi = $pdo->query("SELECT COUNT(*) FROM comenzi WHERE status = 'primita'")->fetchColumn();
$stat_stoc_zero = $pdo->query("SELECT COUNT(*) FROM produse WHERE stoc <= 0")->fetchColumn();
$stat_clienti = $pdo->query("SELECT COUNT(*) FROM clienti")->fetchColumn();

// --- DATE SPECIFICE PENTRU DASHBOARD NOU ---
if ($pagina_curenta == 'dashboard') {
    // Încasări pe perioade de timp
    $incasari_azi = $pdo->query("SELECT SUM(total) FROM comenzi WHERE DATE(data_comanda) = CURDATE()")->fetchColumn() ?: 0;
    $vizitatori_azi = $pdo->query("SELECT COUNT(*) FROM vizitatori WHERE data_vizita = CURDATE()")->fetchColumn() ?: 0;
    $incasari_7zile = $pdo->query("SELECT SUM(total) FROM comenzi WHERE data_comanda >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn() ?: 0;
    $incasari_30zile = $pdo->query("SELECT SUM(total) FROM comenzi WHERE data_comanda >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn() ?: 0;

    $lista_stoc_zero = $pdo->query("SELECT id, nume_piesa, cod_piesa, pret FROM produse WHERE stoc <= 0 ORDER BY id DESC")->fetchAll();
    $lista_clienti = $pdo->query("SELECT id, nume, email, telefon FROM clienti ORDER BY id DESC")->fetchAll();
    $ultimele_comenzi = $pdo->query("SELECT id, nume_client, total, data_comanda, status FROM comenzi ORDER BY data_comanda DESC LIMIT 5")->fetchAll();
}

function get_status_color($st) {
    if($st == 'primita') return '#6c757d'; 
    if($st == 'in_locatie') return '#ffc107'; 
    if($st == 'curier') return '#0d6efd'; 
    if($st == 'livrata') return '#198754'; 
    return '#333';
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; display: flex; min-height: 100vh; background: #f4f6f9; }
        .sidebar { width: 250px; background: #343a40; color: white; display: flex; flex-direction: column; padding: 20px; flex-shrink: 0; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #4b545c; padding-bottom: 10px; font-size: 1.4rem;}
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 15px; margin-bottom: 5px; border-radius: 5px; display: block; font-weight: 500;}
        .sidebar a.active, .sidebar a:hover { background: #007bff; color: white; }
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 12px; border: none; width: 100%; cursor: pointer; border-radius: 4px; font-weight: bold;}
        
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; color: white; font-weight: bold; font-size: 0.8rem; margin-left: 10px;}
        .update-form { display: flex; gap: 10px; align-items: center; margin-top: 10px; background: #f8f9fa; padding: 10px; border-radius: 5px;}
        .update-form select { margin: 0; width: auto; flex-grow: 1; }
        .update-form button { width: auto; padding: 8px 15px; font-size: 0.9rem; background: #007bff; }

        table.produse-table { width: 100%; border-collapse: collapse; font-size: 0.9em; background: white; }
        table.produse-table th { text-align: left; background: #343a40; color: white; padding: 12px 15px; }
        table.produse-table td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        table.produse-table tr:hover { background-color: #f8f9fa; }
        
        .btn-action { padding: 6px 12px; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85rem; display: inline-block; }
        .btn-edit { background: #007bff; }
        .btn-delete { background: #dc3545; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold;}
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745;}
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545;}
        
        .flex-row { display: flex; gap: 15px; }
        .flex-col { flex: 1; }
        /* --- STILURI CORECTATE PENTRU CĂSUȚE MAȘINI --- */
        .checkbox-list { border: 1px solid #ddd; border-radius: 4px; max-height: 180px; overflow-y: auto; margin-bottom: 15px; background: #fff; padding: 0; }
        .checkbox-list label { display: flex !important; align-items: center; cursor: pointer; padding: 8px 15px; border-bottom: 1px solid #f9f9f9; margin: 0; transition: background 0.2s; font-size: 0.95rem; }
        .checkbox-list label:hover { background: #f1f8ff; }
        /* Forțăm căsuța să nu mai ocupe 100% lățime și să se alinieze la stânga perfect cu textul */
        .checkbox-list input[type="checkbox"] { width: 18px !important; height: 18px !important; margin: 0 10px 0 0 !important; cursor: pointer; flex-shrink: 0; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="fas fa-cogs"></i> Admin Panel</h2>
        <a href="admin.php?pagina=dashboard" class="<?= $pagina_curenta == 'dashboard' ? 'active' : '' ?>"><i class="fas fa-chart-line me-2"></i> Dashboard</a>
        <a href="admin.php?pagina=produse" class="<?= $pagina_curenta == 'produse' ? 'active' : '' ?>"><i class="fas fa-box-open me-2"></i> Produse</a>
        <a href="admin.php?pagina=comenzi" class="<?= $pagina_curenta == 'comenzi' ? 'active' : '' ?>"><i class="fas fa-shopping-cart me-2"></i> Comenzi</a>
        <a href="index.php" target="_blank" style="margin-top: 20px; background: #4b545c;"><i class="fas fa-store me-2"></i> Vezi Magazinul</a>
        <a href="logout.php" style="background:#dc3545; margin-top:auto;"><i class="fas fa-sign-out-alt me-2"></i> Deconectare</a>
    </div>

    <div class="content">
        <?= $mesaj ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="border-left: 5px solid #28a745; display: flex; flex-direction: row; align-items: center; padding: 20px; gap: 15px; margin-bottom: 0;">
                <div style="font-size: 2.5rem; color: #28a745;"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div style="color: #6c757d; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Încasări Totale</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #333;"><?= number_format($stat_vanzari, 2) ?> Lei</div>
                </div>
            </div>
            <div class="card" style="border-left: 5px solid #007bff; display: flex; flex-direction: row; align-items: center; padding: 20px; gap: 15px; margin-bottom: 0;">
                <div style="font-size: 2.5rem; color: #007bff;"><i class="fas fa-shopping-basket"></i></div>
                <div>
                    <div style="color: #6c757d; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Comenzi Noi</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: <?= $stat_comenzi_noi > 0 ? '#007bff' : '#333' ?>;"><?= $stat_comenzi_noi ?></div>
                </div>
            </div>
            <div class="card" style="border-left: 5px solid #dc3545; display: flex; flex-direction: row; align-items: center; padding: 20px; gap: 15px; margin-bottom: 0;">
                <div style="font-size: 2.5rem; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div style="color: #6c757d; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Produse Stoc 0</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: <?= $stat_stoc_zero > 0 ? '#dc3545' : '#333' ?>;"><?= $stat_stoc_zero ?></div>
                </div>
            </div>
            <div class="card" style="border-left: 5px solid #ffc107; display: flex; flex-direction: row; align-items: center; padding: 20px; gap: 15px; margin-bottom: 0;">
                <div style="font-size: 2.5rem; color: #ffc107;"><i class="fas fa-users"></i></div>
                <div>
                    <div style="color: #6c757d; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">Clienți Înregistrați</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #333;"><?= $stat_clienti ?></div>
                </div>
            </div>
        </div>

        <?php if ($pagina_curenta == 'dashboard'): ?>
            
            <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 20px;">
                <h3 style="padding: 15px 20px; background: #343a40; color: white; margin: 0; font-size: 1.1rem;">
                    <i class="fas fa-chart-pie me-2"></i> Raport Încasări
                </h3>
                <div style="display: flex; justify-content: space-around; padding: 25px 20px; text-align: center; flex-wrap: wrap; gap: 15px; background: #fff;">
                    
                    <div style="flex: 1;">
                        <div style="color: #6c757d; font-size: 0.95rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">Astăzi</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #28a745;">
                            <?= number_format($incasari_azi, 2) ?> <span style="font-size: 1rem; color: #666;">Lei</span>
                        </div>
                    </div>

                    <div style="flex: 1; border-left: 1px solid #e9ecef;">
                        <div style="color: #6c757d; font-size: 0.95rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">Ultimele 7 Zile</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #17a2b8;">
                            <?= number_format($incasari_7zile, 2) ?> <span style="font-size: 1rem; color: #666;">Lei</span>
                        </div>
                    </div>

                    <div style="flex: 1; border-left: 1px solid #e9ecef;">
                        <div style="color: #6c757d; font-size: 0.95rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">Ultimele 30 Zile</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #007bff;">
                            <?= number_format($incasari_30zile, 2) ?> <span style="font-size: 1rem; color: #666;">Lei</span>
                        </div>
                        <div style="flex: 1; border-left: 1px solid #e9ecef;">
                        <div style="color: #6c757d; font-size: 0.95rem; font-weight: bold; text-transform: uppercase; margin-bottom: 5px;">Vizitatori Astăzi</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #ff851b;">
                            <?= $vizitatori_azi ?> <i class="fas fa-eye" style="font-size: 1.2rem; color: #ccc;"></i>
                        </div>
                    </div>
                    </div>

                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                
                <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
                    <h3 style="padding: 15px 20px; background: #dc3545; color: white; margin: 0; font-size: 1.1rem;">
                        <i class="fas fa-exclamation-circle me-2"></i> Urgențe Aprovizionare (Stoc 0)
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="produse-table">
                            <thead><tr><th>Nume Piesă</th><th>Cod</th><th>Acțiune</th></tr></thead>
                            <tbody>
                                <?php if(count($lista_stoc_zero) > 0): ?>
                                    <?php foreach($lista_stoc_zero as $pz): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($pz['nume_piesa']) ?></strong></td>
                                        <td><?= htmlspecialchars($pz['cod_piesa']) ?></td>
                                        <td><a href="edit_produs.php?id=<?= $pz['id'] ?>" class="btn-action btn-edit"><i class="fas fa-plus"></i> Stoc</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align: center; padding: 20px; color: #28a745; font-weight: bold;">Toate produsele sunt în stoc! ✅</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 0;">
                    <h3 style="padding: 15px 20px; background: #ffc107; color: #333; margin: 0; font-size: 1.1rem;">
                        <i class="fas fa-users me-2"></i> Clienți Înregistrați
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="produse-table">
                            <thead><tr><th>Nume Client</th><th>Email</th><th>Telefon</th></tr></thead>
                            <tbody>
                                <?php if(count($lista_clienti) > 0): ?>
                                    <?php foreach($lista_clienti as $cl): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cl['nume']) ?></strong></td>
                                        <td><a href="mailto:<?= htmlspecialchars($cl['email']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($cl['email']) ?></a></td>
                                        <td><?= htmlspecialchars($cl['telefon']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align: center; padding: 20px;">Niciun client înregistrat încă.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="card" style="padding: 0; overflow: hidden;">
                <h3 style="padding: 15px 20px; background: #007bff; color: white; margin: 0; font-size: 1.1rem;">
                    <i class="fas fa-history me-2"></i> Ultimele 5 Comenzi
                </h3>
                <table class="produse-table">
                    <thead><tr><th>ID</th><th>Client</th><th>Data</th><th>Total</th><th>Status</th><th>Detalii</th></tr></thead>
                    <tbody>
                        <?php if(count($ultimele_comenzi) > 0): ?>
                            <?php foreach($ultimele_comenzi as $uc): ?>
                            <tr>
                                <td>#<?= $uc['id'] ?></td>
                                <td><strong><?= htmlspecialchars($uc['nume_client']) ?></strong></td>
                                <td><?= date('d.m.Y H:i', strtotime($uc['data_comanda'])) ?></td>
                                <td><strong style="color: #28a745;"><?= $uc['total'] ?> Lei</strong></td>
                                <td>
                                    <span class="status-badge" style="background-color: <?= get_status_color($uc['status']) ?>;">
                                        <?= strtoupper(str_replace('_', ' ', $uc['status'])) ?>
                                    </span>
                                </td>
                                <td><a href="admin.php?pagina=comenzi" class="btn-action btn-edit" style="background: #6c757d;">Vezi</a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 20px;">Nicio comandă plasată încă.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>


        <?php if ($pagina_curenta == 'produse'): ?>
            <div class="card" style="max-width: 650px; margin: 0 auto;">
                <h1><i class="fas fa-plus-circle text-primary"></i> Adaugă Piesă Nouă</h1>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="actiune" value="adauga_produs">
                    
                    <label>Categorie Principală:</label>
                    <select name="categorie">
                        <option value="">Fără categorie (Apare doar la căutare)</option>
                        <option value="anvelope">Anvelope și jante</option>
                        <option value="frane">Sistem de frânare</option>
                        <option value="amortizare">Amortizare</option>
                        <option value="uleiuri">Uleiuri și lichide</option>
                        <option value="suspensie">Suspensie, tije</option>
                        <option value="filtre">Filtre</option>
                        <option value="motor">Motor</option>
                        <option value="caroserie">Caroserie</option>
                        <option value="ambreiaj">Ambreiaj / piese</option>
                        <option value="curele">Curele, role</option>
                        <option value="esapament">Eșapament</option>
                        <option value="alte">Alte categorii</option>
                    </select>

                    <label>Nume Piesă:</label><input type="text" name="nume" required>
                    <label>Descriere:</label><textarea name="descriere" rows="3"></textarea>
                    <label>Cod Piesă (OE):</label><input type="text" name="cod" required>
                    
                    <hr style="border: 1px solid #eee; margin: 20px 0;">
                    <h4 style="margin-top: 0; color: #555;">💰 Calcul Preț</h4>

                    <div class="flex-row">
                        <div class="flex-col">
                            <label>Preț Achiziție:</label>
                            <input type="number" step="0.01" id="pret_achizitie" name="pret_achizitie" placeholder="Ex: 100" required>
                        </div>
                        <div class="flex-col">
                            <label>Adaos (%):</label>
                            <input type="number" id="adaos" name="adaos" value="30" required>
                        </div>
                        <div class="flex-col">
                            <label>TVA (%):</label>
                            <select id="tva" name="tva">
                                <option value="21" selected>21% (Standard)</option>
                                <option value="9">9% (Redus)</option>
                                <option value="5">5% (Cărți/Lemne)</option>
                                <option value="0">0% (Scutit)</option>
                            </select>
                        </div>
                    </div>

                    <label style="color: #28a745; font-weight: bold;">Preț Final Client (Calculat automat):</label>
                    <input type="number" step="0.01" id="pret_final" name="pret_final" readonly style="background-color: #e9ecef; font-weight: bold; font-size: 1.1em; color: #28a745; outline: none; border: 2px solid #28a745;">
                    <hr style="border: 1px solid #eee; margin: 20px 0;">

                    <label>Stoc:</label><input type="number" name="stoc" required>
                    
                    <label style="margin-bottom: 5px; font-weight: bold;">Compatibilă cu (Alege și apasă Adaugă):</label>
            <div style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                
                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;">
                    <select id="sel_marca" style="flex: 1 1 18%; min-width: 120px; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">1. Marca...</option>
                    </select>
                    
                    <select id="sel_model" style="flex: 1 1 18%; min-width: 120px; padding: 10px; border-radius: 4px; border: 1px solid #ccc;" disabled>
                        <option value="">2. Model...</option>
                    </select>

                    <select id="sel_an" style="flex: 1 1 18%; min-width: 120px; padding: 10px; border-radius: 4px; border: 1px solid #ccc;" disabled>
                        <option value="">3. An...</option>
                    </select>

                    <select id="sel_combustibil" style="flex: 1 1 18%; min-width: 120px; padding: 10px; border-radius: 4px; border: 1px solid #ccc;" disabled>
                        <option value="">4. Combustibil...</option>
                    </select>

                    <select id="sel_motor" style="flex: 1 1 25%; min-width: 150px; padding: 10px; border-radius: 4px; border: 1px solid #ccc;" disabled>
                        <option value="">5. Motorizare...</option>
                    </select>

                    <button type="button" id="btn_adauga_masina" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;" disabled>
                        <i class="fas fa-plus"></i> Adaugă
                    </button>
                </div>

                <div id="container_masini_alese" style="display: flex; flex-wrap: wrap; gap: 10px; min-height: 40px; padding: 10px; background: #fff; border: 1px dashed #ccc; border-radius: 4px; align-items: center;">
                    <span style="color: #999; font-size: 0.9em; margin: auto;" id="text_gol_masini">Nicio mașină adăugată încă. Produsul va fi universal dacă nu alegi nimic.</span>
                </div>
            </div>

                    <label>Imagine:</label><input type="file" name="poza" accept="image/*" required>
                    <button type="submit"><i class="fas fa-save"></i> Salvează Produs</button>
                </form>
            </div>

            <h2 style="margin-top: 50px;"><i class="fas fa-boxes"></i> Lista Produse</h2>
            
            <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; max-width: 500px;">
                <input type="hidden" name="pagina" value="produse">
                <input type="text" name="cauta" placeholder="Caută după Cod OE sau Nume..." value="<?= htmlspecialchars($search) ?>" style="margin: 0;">
                <button type="submit" style="width: auto; padding: 10px 20px;"><i class="fas fa-search"></i> Caută</button>
                <?php if($search): ?>
                    <a href="admin.php?pagina=produse" style="padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; display:flex; align-items:center;">❌ Șterge</a>
                <?php endif; ?>
            </form>
            
            <div class="card" style="padding: 0; overflow: hidden;">
                <table class="produse-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nume Piesă</th>
                            <th>Categorie</th>
                            <th>Cod</th>
                            <th>Preț Final</th>
                            <th>Stoc</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($toate_produsele as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['nume_piesa']) ?></strong></td>
                            <td><?= $p['categorie'] ? ucfirst($p['categorie']) : '<span style="color:#aaa;">-</span>' ?></td>
                            <td><?= htmlspecialchars($p['cod_piesa']) ?></td>
                            <td style="color: #28a745; font-weight: bold;"><?= $p['pret'] ?> Lei</td>
                            <td>
                                <?php if($p['stoc'] > 0): ?>
                                    <span style="color: green; font-weight:bold;"><?= $p['stoc'] ?> buc.</span>
                                <?php else: ?>
                                    <span style="color: red; font-weight:bold;">Stoc 0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_produs.php?id=<?= $p['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="admin.php?sterge_produs=<?= $p['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Ești sigur că vrei să ștergi acest produs?');"><i class="fas fa-trash"></i> Șterge</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($pagina_curenta == 'comenzi'): ?>
            <h1><i class="fas fa-clipboard-list"></i> Gestionare Comenzi</h1>
            <?php foreach ($comenzi as $c): ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <h3 style="margin:0;">Comanda #<?= $c['id'] ?></h3>
                            <div style="margin-top:5px;">
                                Status actual: 
                                <span class="status-badge" style="background-color: <?= get_status_color($c['status']) ?>">
                                    <?php 
                                        if($c['status'] == 'primita') echo "PRIMITĂ";
                                        elseif($c['status'] == 'in_locatie') echo "ÎN LOCAȚIE (RIDICARE)";
                                        elseif($c['status'] == 'curier') echo "LA CURIER";
                                        elseif($c['status'] == 'livrata') echo "LIVRATĂ";
                                        else echo "NECUNOSCUT";
                                    ?>
                                </span>
                            </div>
                            <p class="small text-muted"><i class="far fa-clock"></i> <?= $c['data_comanda'] ?></p>
                            <p><strong><i class="fas fa-user"></i> Client:</strong> <?= htmlspecialchars($c['nume_client']) ?> | <strong><i class="fas fa-phone"></i> Tel:</strong> <?= htmlspecialchars($c['telefon']) ?></p>
                            <p><strong><i class="fas fa-map-marker-alt"></i> Adresă:</strong> <?= htmlspecialchars($c['adresa']) ?></p>
                        </div>
                        <div style="text-align:right;">
                             <h3 style="color:#28a745; margin:0;"><?= $c['total'] ?> Lei</h3>
                             <a href="admin.php?sterge_comanda=<?= $c['id'] ?>" onclick="return confirm('Ești sigur că vrei să ștergi această comandă? Nu poate fi recuperată!')" style="color:red; font-size:0.8rem; margin-top: 10px; display: inline-block;"><i class="fas fa-trash"></i> Șterge Comanda</a>
                        </div>
                    </div>

                    <form method="POST" class="update-form">
                        <input type="hidden" name="actiune" value="update_status">
                        <input type="hidden" name="id_comanda" value="<?= $c['id'] ?>">
                        <label style="margin:0; font-weight:bold;">Schimbă starea:</label>
                        <select name="status_nou">
                            <option value="primita" <?= $c['status']=='primita'?'selected':'' ?>>1. Primită</option>
                            <option value="in_locatie" <?= $c['status']=='in_locatie'?'selected':'' ?>>2. Sosit în Locație (Ridicare)</option>
                            <option value="curier" <?= $c['status']=='curier'?'selected':'' ?>>3. Predată Curier</option>
                            <option value="livrata" <?= $c['status']=='livrata'?'selected':'' ?>>4. Livrată la Client</option>
                        </select>
                        <button type="submit"><i class="fas fa-sync-alt"></i> Update</button>
                    </form>

                    <table class="produse-table" style="margin-top: 20px;">
                        <thead style="background: #f1f3f5;">
                            <tr><th style="color: #333; background: #e9ecef;">Produs</th><th style="color: #333; background: #e9ecef;">Cant</th><th style="color: #333; background: #e9ecef;">Preț</th></tr>
                        </thead>
                        <tbody>
                            <?php
                                $stmt = $pdo->prepare("SELECT d.*, p.nume_piesa FROM detalii_comanda d JOIN produse p ON d.id_produs = p.id WHERE d.id_comanda = ?");
                                $stmt->execute([$c['id']]);
                                $produse_comanda = $stmt->fetchAll();
                            ?>
                            <?php foreach ($produse_comanda as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['nume_piesa']) ?></td>
                                    <td><strong><?= $p['cantitate'] ?>x</strong></td>
                                    <td><?= $p['pret'] * $p['cantitate'] ?> Lei</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // =======================================================
        // --- 1. SCRIPT CALCUL PREȚ (NEATINS) ---
        // =======================================================
        function calculeazaPret() {
            let achizitie = parseFloat(document.getElementById('pret_achizitie').value) || 0;
            let adaos = parseFloat(document.getElementById('adaos').value) || 0;
            let tva = parseFloat(document.getElementById('tva').value) || 0;

            let pretCuAdaos = achizitie + (achizitie * adaos / 100);
            let pretFinal = pretCuAdaos + (pretCuAdaos * tva / 100);

            let campPret = document.getElementById('pret_final');
            if(campPret) campPret.value = pretFinal.toFixed(2);
        }

        let inputAchizitie = document.getElementById('pret_achizitie');
        let inputAdaos = document.getElementById('adaos');
        let selectTva = document.getElementById('tva');

        if(inputAchizitie) inputAchizitie.addEventListener('input', calculeazaPret);
        if(inputAdaos) inputAdaos.addEventListener('input', calculeazaPret);
        if(selectTva) selectTva.addEventListener('change', calculeazaPret);


        // =======================================================
        // --- 2. SCRIPT SELECȚIE MAȘINI (NOU - 5 PAȘI) ---
        // =======================================================
        const selMarca = document.getElementById('sel_marca');
        const selModel = document.getElementById('sel_model');
        const selAn = document.getElementById('sel_an');
        const selCombustibil = document.getElementById('sel_combustibil');
        const selMotor = document.getElementById('sel_motor');
        const btnAdauga = document.getElementById('btn_adauga_masina');
        const containerMasini = document.getElementById('container_masini_alese');
        const textGol = document.getElementById('text_gol_masini');

        let masiniSelectate = []; 

        if (selMarca) {
            // 1. Încărcăm Mărcile
            fetch('api_masini.php?actiune=get_marci')
                .then(r => r.json())
                .then(marci => marci.forEach(m => selMarca.add(new Option(m, m))));

            // 2. Schimbare Marcă
            selMarca.addEventListener('change', function() {
                selModel.innerHTML = '<option value="">2. Model...</option>';
                selAn.innerHTML = '<option value="">3. An...</option>';
                selCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
                selMotor.innerHTML = '<option value="">5. Motorizare...</option>';
                selModel.disabled = true; selAn.disabled = true; selCombustibil.disabled = true; selMotor.disabled = true; btnAdauga.disabled = true;

                if (this.value) {
                    fetch(`api_masini.php?actiune=get_modele&marca=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(modele => { modele.forEach(m => selModel.add(new Option(m, m))); selModel.disabled = false; });
                }
            });

            // 3. Schimbare Model
            selModel.addEventListener('change', function() {
                selAn.innerHTML = '<option value="">3. An...</option>';
                selCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
                selMotor.innerHTML = '<option value="">5. Motorizare...</option>';
                selAn.disabled = true; selCombustibil.disabled = true; selMotor.disabled = true; btnAdauga.disabled = true;

                if (this.value) {
                    fetch(`api_masini.php?actiune=get_ani&marca=${encodeURIComponent(selMarca.value)}&model=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(anii => { anii.forEach(a => selAn.add(new Option(a, a))); selAn.disabled = false; });
                }
            });

            // 4. Schimbare An
            selAn.addEventListener('change', function() {
                selCombustibil.innerHTML = '<option value="">4. Combustibil...</option>';
                selMotor.innerHTML = '<option value="">5. Motorizare...</option>';
                selCombustibil.disabled = true; selMotor.disabled = true; btnAdauga.disabled = true;

                if (this.value) {
                    fetch(`api_masini.php?actiune=get_combustibil&marca=${encodeURIComponent(selMarca.value)}&model=${encodeURIComponent(selModel.value)}&an=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(comb => { comb.forEach(c => selCombustibil.add(new Option(c, c))); selCombustibil.disabled = false; });
                }
            });

            // 5. Schimbare Combustibil
            selCombustibil.addEventListener('change', function() {
                selMotor.innerHTML = '<option value="">5. Motorizare...</option>';
                selMotor.disabled = true; btnAdauga.disabled = true;

                if (this.value) {
                    fetch(`api_masini.php?actiune=get_motoare&marca=${encodeURIComponent(selMarca.value)}&model=${encodeURIComponent(selModel.value)}&an=${encodeURIComponent(selAn.value)}&combustibil=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(motoare => { motoare.forEach(m => selMotor.add(new Option(m.motorizare, m.id))); selMotor.disabled = false; });
                }
            });

            // 6. Activare Buton Adaugă
            selMotor.addEventListener('change', function() {
                btnAdauga.disabled = !this.value;
            });

            // 7. Acțiunea de a Adăuga o mașină (crearea etichetei)
            btnAdauga.addEventListener('click', function() {
                let idMasina = parseInt(selMotor.value);
                let textMasina = `${selMarca.value} ${selModel.value} ${selAn.value} ${selCombustibil.value} ${selMotor.options[selMotor.selectedIndex].text}`;

                if (masiniSelectate.includes(idMasina)) {
                    alert("Ai adăugat deja această mașină!");
                    return;
                }

                masiniSelectate.push(idMasina);
                if(textGol) textGol.style.display = 'none';

                let tag = document.createElement('div');
                tag.style.cssText = "background: #e2e8f0; color: #334155; padding: 5px 12px; border-radius: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;";
                tag.innerHTML = `
                    <i class="fas fa-car text-primary"></i> <strong>${textMasina}</strong>
                    <i class="fas fa-times-circle" style="cursor: pointer; color: #dc3545; font-size: 1.2em; margin-left: 5px;" title="Șterge"></i>
                    <input type="hidden" name="masini[]" value="${idMasina}">
                `;

                tag.querySelector('.fa-times-circle').addEventListener('click', function() {
                    tag.remove();
                    masiniSelectate = masiniSelectate.filter(id => id !== idMasina);
                    if (masiniSelectate.length === 0 && textGol) textGol.style.display = 'block';
                });

                containerMasini.appendChild(tag);
                selMotor.value = "";
                btnAdauga.disabled = true;
            });
        }
    </script>
</body>
</html>