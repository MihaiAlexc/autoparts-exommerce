<?php
// --- SECURITATE ---
session_start();
if (!isset($_SESSION['admin_logat']) || $_SESSION['admin_logat'] !== true) {
    header("Location: login.php");
    exit();
}

// --- CONFIGURARE DB ---
ini_set('display_errors', 1); error_reporting(E_ALL);
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// --- PRELUARE ID PRODUS ---
if (!isset($_GET['id'])) {
    die("ID produs lipsă!");
}
$id_produs = $_GET['id'];
$mesaj = "";

// --- SALVARE DATE NOI (Când se apasă butonul Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categorie = empty($_POST['categorie']) ? null : $_POST['categorie'];
    $nume = $_POST['nume'];
    $descriere = $_POST['descriere'];
    $cod = $_POST['cod'];
    
    // Preluăm noile câmpuri financiare
    $pret_achizitie = (float)$_POST['pret_achizitie'];
    $adaos = (int)$_POST['adaos'];
    $tva = (int)$_POST['tva'];
    $pret_final = (float)$_POST['pret_final']; // Cel calculat de JS
    
    $stoc = (int)$_POST['stoc'];

    try {
        // 1. Actualizăm datele de bază ale produsului cu noile câmpuri
        $sql = "UPDATE produse SET categorie=?, nume_piesa=?, descriere=?, cod_piesa=?, pret_achizitie=?, adaos=?, tva=?, pret=?, stoc=? WHERE id=?";
        $pdo->prepare($sql)->execute([$categorie, $nume, $descriere, $cod, $pret_achizitie, $adaos, $tva, $pret_final, $stoc, $id_produs]);

        // 2. Actualizăm compatibilitatea (ștergem legăturile vechi și punem noile bife/etichete)
        $pdo->prepare("DELETE FROM compatibilitati WHERE id_produs=?")->execute([$id_produs]);
        if (!empty($_POST['masini'])) {
            $stmt_comp = $pdo->prepare("INSERT INTO compatibilitati (id_produs, id_masina) VALUES (?, ?)");
            foreach ($_POST['masini'] as $masina_id) {
                $stmt_comp->execute([$id_produs, $masina_id]);
            }
        }

        // Redirecționăm înapoi la tabelul cu produse
        header("Location: admin.php?pagina=produse");
        exit();
    } catch (Exception $e) {
        $mesaj = "<div style='color:red; margin-bottom:15px; padding:10px; border:1px solid red; background:#fdd;'>Eroare la salvare: " . $e->getMessage() . "</div>";
    }
}

// --- PRELUARE DATE ACTUALE (Pentru a umple formularul) ---
$stmt = $pdo->prepare("SELECT * FROM produse WHERE id = ?");
$stmt->execute([$id_produs]);
$produs = $stmt->fetch();

if (!$produs) {
    die("Produsul nu a fost găsit în baza de date!");
}

// Aflăm ce mașini sunt bifate în prezent (ne returnează un array simplu cu ID-urile mașinilor)
$stmt_comp = $pdo->prepare("SELECT id_masina FROM compatibilitati WHERE id_produs = ?");
$stmt_comp->execute([$id_produs]);
$masini_selectate = $stmt_comp->fetchAll(PDO::FETCH_COLUMN); 

// --- NOU: Aflăm detaliile mașinilor deja selectate pentru a le afișa ca etichete (tag-uri) ---
$masini_preselectate_detalii = [];
if (!empty($masini_selectate)) {
    // Creăm un string cu semne de întrebare de lungimea array-ului (ex: "?,?,?")
    $in_str = str_repeat('?,', count($masini_selectate) - 1) . '?';
    $stmt_detalii = $pdo->prepare("SELECT id, marca, model, motorizare, an_fabricatie FROM masini WHERE id IN ($in_str)");
    $stmt_detalii->execute($masini_selectate);
    $masini_preselectate_detalii = $stmt_detalii->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editează Produs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 650px; margin: 0 auto; }
        input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 12px; border: none; width: 100%; cursor: pointer; border-radius: 5px; font-size: 16px; font-weight: bold; transition: 0.2s;}
        button:hover { background: #0056b3; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #6c757d; text-decoration: none; font-weight: bold;}
        .back-link:hover { color: #343a40; }
        
        .flex-row { display: flex; gap: 15px; }
        .flex-col { flex: 1; }
    </style>
</head>
<body>

    <div class="card">
        <a href="admin.php?pagina=produse" class="back-link"><i class="fas fa-arrow-left"></i> Înapoi la Admin</a>
        <h2 style="margin-top:0; color: #333;"><i class="fas fa-edit text-primary"></i> Editează Produs #<?= $produs['id'] ?></h2>
        
        <?= $mesaj ?>

        <form method="POST">
            <label>Categorie Principală:</label>
            <select name="categorie">
                <option value="" <?= empty($produs['categorie']) ? 'selected' : '' ?>>Fără categorie (Apare doar la căutare)</option>
                <option value="anvelope" <?= $produs['categorie'] == 'anvelope' ? 'selected' : '' ?>>Anvelope și jante</option>
                <option value="frane" <?= $produs['categorie'] == 'frane' ? 'selected' : '' ?>>Sistem de frânare</option>
                <option value="amortizare" <?= $produs['categorie'] == 'amortizare' ? 'selected' : '' ?>>Amortizare</option>
                <option value="uleiuri" <?= $produs['categorie'] == 'uleiuri' ? 'selected' : '' ?>>Uleiuri și lichide</option>
                <option value="suspensie" <?= $produs['categorie'] == 'suspensie' ? 'selected' : '' ?>>Suspensie, tije</option>
                <option value="filtre" <?= $produs['categorie'] == 'filtre' ? 'selected' : '' ?>>Filtre</option>
                <option value="motor" <?= $produs['categorie'] == 'motor' ? 'selected' : '' ?>>Motor</option>
                <option value="caroserie" <?= $produs['categorie'] == 'caroserie' ? 'selected' : '' ?>>Caroserie</option>
                <option value="ambreiaj" <?= $produs['categorie'] == 'ambreiaj' ? 'selected' : '' ?>>Ambreiaj / piese</option>
                <option value="curele" <?= $produs['categorie'] == 'curele' ? 'selected' : '' ?>>Curele, role</option>
                <option value="esapament" <?= $produs['categorie'] == 'esapament' ? 'selected' : '' ?>>Eșapament</option>
                <option value="alte" <?= $produs['categorie'] == 'alte' ? 'selected' : '' ?>>Alte categorii</option>
            </select>

            <label>Nume Piesă:</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($produs['nume_piesa']) ?>" required>

            <label>Descriere:</label>
            <textarea name="descriere" rows="3"><?= htmlspecialchars($produs['descriere']) ?></textarea>

            <label>Cod Piesă (OE):</label>
            <input type="text" name="cod" value="<?= htmlspecialchars($produs['cod_piesa']) ?>" required>

            <hr style="border: 1px solid #eee; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #555;">💰 Calcul Preț</h4>

            <div class="flex-row">
                <div class="flex-col">
                    <label>Preț Achiziție:</label>
                    <input type="number" step="0.01" id="pret_achizitie" name="pret_achizitie" value="<?= $produs['pret_achizitie'] ?>" required>
                </div>
                <div class="flex-col">
                    <label>Adaos (%):</label>
                    <input type="number" id="adaos" name="adaos" value="<?= $produs['adaos'] ?>" required>
                </div>
                <div class="flex-col">
                    <label>TVA (%):</label>
                    <select id="tva" name="tva">
                        <option value="19" <?= $produs['tva'] == 19 ? 'selected' : '' ?>>19% (Standard)</option>
                        <option value="9" <?= $produs['tva'] == 9 ? 'selected' : '' ?>>9% (Redus)</option>
                        <option value="5" <?= $produs['tva'] == 5 ? 'selected' : '' ?>>5% (Cărți/Lemne)</option>
                        <option value="0" <?= $produs['tva'] == 0 ? 'selected' : '' ?>>0% (Scutit)</option>
                    </select>
                </div>
            </div>

            <label style="color: #28a745; font-weight: bold;">Preț Final Client (Calculat automat):</label>
            <input type="number" step="0.01" id="pret_final" name="pret_final" value="<?= $produs['pret'] ?>" readonly style="background-color: #e9ecef; font-weight: bold; font-size: 1.1em; color: #28a745; outline: none; border: 2px solid #28a745;">

            <hr style="border: 1px solid #eee; margin: 20px 0;">

            <label>Stoc (Bucăți):</label>
            <input type="number" name="stoc" value="<?= $produs['stoc'] ?>" required>

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
                    <span style="color: #999; font-size: 0.9em; margin: auto; <?= count($masini_preselectate_detalii) > 0 ? 'display:none;' : '' ?>" id="text_gol_masini">Nicio mașină adăugată încă. Produsul va fi universal dacă nu alegi nimic.</span>
                    
                    <?php foreach ($masini_preselectate_detalii as $mp): ?>
                        <?php $text_masina_db = $mp['marca'] . ' ' . $mp['model'] . ' ' . $mp['an_fabricatie'] . ' ' . $mp['combustibil'] . ' ' . $mp['motorizare']; ?>
                        <div class="tag-masina" data-id="<?= $mp['id'] ?>" style="background: #e2e8f0; color: #334155; padding: 5px 12px; border-radius: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-car text-primary"></i> <strong><?= htmlspecialchars($text_masina_db) ?></strong>
                            <i class="fas fa-times-circle btn-sterge-tag" style="cursor: pointer; color: #dc3545; font-size: 1.2em; margin-left: 5px;" title="Șterge"></i>
                            <input type="hidden" name="masini[]" value="<?= $mp['id'] ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" style="background: #28a745; margin-top: 10px;"><i class="fas fa-save"></i> Salvează Modificările</button>
        </form>
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

        // Încărcăm în memorie mașinile care sunt deja asociate produsului
        let masiniSelectate = [<?= !empty($masini_selectate) ? implode(',', $masini_selectate) : '' ?>];

        // Activăm butoanele de ștergere ("X") pentru mașinile pre-încărcate din DB
        document.querySelectorAll('.btn-sterge-tag').forEach(btn => {
            btn.addEventListener('click', function() {
                let tag = this.closest('.tag-masina');
                let id = parseInt(tag.getAttribute('data-id'));
                tag.remove();
                masiniSelectate = masiniSelectate.filter(item => item !== id);
                if (masiniSelectate.length === 0 && textGol) textGol.style.display = 'block';
            });
        });

        if (selMarca) {
            // 1. Încărcăm Mărcile
            fetch('api_masini.php?actiune=get_marci')
                .then(r => r.json())
                .then(marci => marci.forEach(m => selMarca.add(new Option(m, m))));

            // 2. Schimbare Marcă -> Modele
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

            // 3. Schimbare Model -> An
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

            // 4. Schimbare An -> Combustibil
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

            // 5. Schimbare Combustibil -> Motoare
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

            // 7. Click pe Adaugă -> Generare Etichetă
            btnAdauga.addEventListener('click', function() {
                let idMasina = parseInt(selMotor.value);
                let textMasina = `${selMarca.value} ${selModel.value} ${selAn.value} ${selCombustibil.value} ${selMotor.options[selMotor.selectedIndex].text}`;

                if (masiniSelectate.includes(idMasina)) {
                    alert("Această mașină este deja asociată acestui produs!");
                    return;
                }

                masiniSelectate.push(idMasina);
                if(textGol) textGol.style.display = 'none';

                let tag = document.createElement('div');
                tag.className = 'tag-masina';
                tag.setAttribute('data-id', idMasina);
                tag.style.cssText = "background: #e2e8f0; color: #334155; padding: 5px 12px; border-radius: 20px; font-size: 0.9em; display: flex; align-items: center; gap: 8px;";
                
                tag.innerHTML = `
                    <i class="fas fa-car text-primary"></i> <strong>${textMasina}</strong>
                    <i class="fas fa-times-circle btn-sterge-tag-nou" style="cursor: pointer; color: #dc3545; font-size: 1.2em; margin-left: 5px;" title="Șterge"></i>
                    <input type="hidden" name="masini[]" value="${idMasina}">
                `;

                tag.querySelector('.btn-sterge-tag-nou').addEventListener('click', function() {
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