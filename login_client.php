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

// --- LOGICA DE AUTENTIFICARE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    $stmt = $pdo->prepare("SELECT * FROM clienti WHERE email = ?");
    $stmt->execute([$email]);
    $client = $stmt->fetch();

    if ($client && password_verify($parola, $client['password'])) {
        // LOGIN REUȘIT!
        $_SESSION['client_id'] = $client['id'];
        $_SESSION['client_nume'] = $client['nume'];
        $_SESSION['client_email'] = $client['email'];
        
        header("Location: index.php"); 
        exit();
    } else {
        $mesaj = "<div class='alert alert-danger d-flex align-items-center mb-4 shadow-sm' role='alert'>
                    <i class='fas fa-exclamation-triangle me-2'></i> 
                    <div>Email sau parolă incorectă! Te rugăm să încerci din nou.</div>
                  </div>";
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
    <title>Autentificare - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Auth Card Premium */
        .auth-wrapper { margin: 60px auto; width: 100%; max-width: 450px; }
        .auth-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; }
        
        .icon-circle { width: 80px; height: 80px; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px auto; }
        
        .form-floating > .form-control { border-radius: 8px; border-color: #ddd; }
        .form-floating > .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        .btn-login { background-color: #0d6efd; border: none; font-weight: bold; font-size: 1.1rem; border-radius: 8px; padding: 12px; transition: all 0.3s ease; }
        .btn-login:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
        
        .separator { display: flex; align-items: center; text-align: center; color: #adb5bd; margin: 25px 0; }
        .separator::before, .separator::after { content: ''; flex: 1; border-bottom: 1px solid #dee2e6; }
        .separator:not(:empty)::before { margin-right: .25em; }
        .separator:not(:empty)::after { margin-left: .25em; }

        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="auth-wrapper">
        <div class="auth-card">
            
            <div class="icon-circle">
                <i class="fas fa-user-lock"></i>
            </div>
            
            <h3 class="text-center fw-bold text-dark mb-1">Bine ai revenit!</h3>
            <p class="text-center text-muted mb-4 pb-2">Conectează-te pentru a vedea istoricul comenzilor.</p>
            
            <?= $mesaj ?>
            
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="nume@exemplu.ro" required>
                    <label for="emailInput"><i class="fas fa-envelope text-muted me-1"></i> Adresa de Email</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" name="parola" class="form-control" id="parolaInput" placeholder="Parola" required>
                    <label for="parolaInput"><i class="fas fa-lock text-muted me-1"></i> Parola</label>
                </div>
                
                <button type="submit" class="btn btn-login text-white w-100 shadow-sm">
                    Intră în cont <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </form>
            
            <div class="separator">sau</div>
            
            <div class="text-center">
                <p class="text-muted mb-2">Ești client nou?</p>
                <a href="register.php" class="btn btn-outline-primary w-100 fw-bold">
                    Creează un cont nou <i class="fas fa-user-plus ms-1"></i>
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>