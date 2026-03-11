<?php
session_start();
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Dacă e deja logat, îl trimitem la magazin
if (isset($_SESSION['client_id'])) {
    header("Location: index.php"); 
    exit();
}

$mesaj = "";
// Variabile goale la început, ca să nu dea eroare când intră prima dată pe pagină
$nume = "";
$email = "";

// --- LOGICA DE ÎNREGISTRARE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nume = trim($_POST['nume']);
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];
    $confirm_parola = $_POST['confirm_parola'];

    // Validare Securitate Premium
    if ($parola !== $confirm_parola) {
        $mesaj = "<div class='alert alert-danger d-flex align-items-center mb-4 shadow-sm' role='alert'>
                    <i class='fas fa-exclamation-triangle me-2'></i> 
                    <div>Parolele nu coincid!</div>
                  </div>";
    } elseif (strlen($parola) < 8 || !preg_match("/[A-Za-z]/", $parola) || !preg_match("/[0-9]/", $parola)) {
        // Regula nouă: 8 caractere + minim o literă și o cifră
        $mesaj = "<div class='alert alert-danger d-flex align-items-center mb-4 shadow-sm' role='alert'>
                    <i class='fas fa-shield-alt me-2'></i> 
                    <div>Parola trebuie să aibă minim 8 caractere și să conțină cel puțin o literă și o cifră!</div>
                  </div>";
    } else {
        // Verificăm dacă emailul există deja
        $stmt = $pdo->prepare("SELECT id FROM clienti WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $mesaj = "<div class='alert alert-warning d-flex align-items-center mb-4 shadow-sm' role='alert'>
                        <i class='fas fa-info-circle me-2'></i> 
                        <div>Există deja un cont cu acest email. <a href='login_client.php' class='alert-link'>Loghează-te aici</a>.</div>
                      </div>";
        } else {
            // Creăm contul
            $hash = password_hash($parola, PASSWORD_DEFAULT);
            $sql = "INSERT INTO clienti (nume, email, telefon, adresa, password) VALUES (?, ?, '', '', ?)";
            $pdo->prepare($sql)->execute([$nume, $email, $hash]);
            
            $mesaj = "<div class='alert alert-success d-flex align-items-center mb-4 shadow-sm' role='alert'>
                        <i class='fas fa-check-circle me-2'></i> 
                        <div>Cont creat cu succes! Te poți <a href='login_client.php' class='alert-link'>autentifica</a> acum.</div>
                      </div>";
            
            // Golim câmpurile după succes ca să nu rămână scrise
            $nume = "";
            $email = "";
        }
    }
}

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Auth Card Premium */
        .auth-wrapper { margin: 60px auto; width: 100%; max-width: 500px; }
        .auth-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
        
        .icon-circle { width: 80px; height: 80px; background-color: rgba(25, 135, 84, 0.1); color: #198754; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px auto; }
        
        .form-floating > .form-control { border-radius: 8px; border-color: #ddd; }
        .form-floating > .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
        
        /* Modificări pentru Ochiul Măgic */
        .password-input { padding-right: 45px !important; }
        .btn-toggle-password { 
            position: absolute; 
            top: 50%; 
            right: 5px; 
            transform: translateY(-50%); 
            z-index: 10; 
            border: none; 
            background: transparent; 
            color: #6c757d; 
            padding: 10px;
        }
        .btn-toggle-password:hover { color: #198754; }
        
        .btn-register { background-color: #198754; border: none; font-weight: bold; font-size: 1.1rem; border-radius: 8px; padding: 12px; transition: all 0.3s ease; }
        .btn-register:hover { background-color: #157347; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3); }
        
        .separator { display: flex; align-items: center; text-align: center; color: #adb5bd; margin: 25px 0; }
        .separator::before, .separator::after { content: ''; flex: 1; border-bottom: 1px solid #dee2e6; }
        .separator:not(:empty)::before { margin-right: .25em; }
        .separator:not(:empty)::after { margin-left: .25em; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="icon-circle">
                <i class="fas fa-user-plus"></i>
            </div>
            
            <h3 class="text-center fw-bold text-dark mb-1">Creează Cont Nou</h3>
            <p class="text-center text-muted mb-4 pb-2">Alătură-te comunității AutoParts Pro.</p>
            
            <?= $mesaj ?>
            
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" name="nume" class="form-control" id="numeInput" placeholder="Nume Complet" value="<?= htmlspecialchars($nume) ?>" required>
                    <label for="numeInput"><i class="fas fa-user text-muted me-1"></i> Nume Complet</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="nume@exemplu.ro" value="<?= htmlspecialchars($email) ?>" required>
                    <label for="emailInput"><i class="fas fa-envelope text-muted me-1"></i> Adresa de Email</label>
                </div>
                
                <div class="row g-2 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input type="password" name="parola" class="form-control password-input" id="parolaInput" placeholder="Parola" required minlength="8">
                            <label for="parolaInput"><i class="fas fa-lock text-muted me-1"></i> Parola</label>
                            <button type="button" class="btn-toggle-password" data-target="parolaInput">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating position-relative">
                            <input type="password" name="confirm_parola" class="form-control password-input" id="confirmParolaInput" placeholder="Confirmă Parola" required minlength="8">
                            <label for="confirmParolaInput"><i class="fas fa-check-circle text-muted me-1"></i> Confirmă</label>
                            <button type="button" class="btn-toggle-password" data-target="confirmParolaInput">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Parola trebuie să aibă minim 8 caractere (inclusiv litere și cifre).</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-register text-white w-100 shadow-sm">
                    Înregistrează-te <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>
            
            <div class="separator">sau</div>
            
            <div class="text-center">
                <p class="text-muted mb-2">Ai deja un cont?</p>
                <a href="login_client.php" class="btn btn-outline-secondary w-100 fw-bold">
                    Autentificare <i class="fas fa-sign-in-alt ms-1"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- SCRIPT PENTRU OCHIUL MAGIC ---
    document.querySelectorAll('.btn-toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (inputField.type === 'password') {
                inputField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                inputField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
</body>
</html>