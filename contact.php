<?php
session_start();

// --- CONECTARE DB (Opțional aici, dar util pentru header/coș) ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare: " . $e->getMessage()); }

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;

$mesaj_trimis = false;

// Dacă cineva apasă butonul de trimitere mesaj
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nume = htmlspecialchars($_POST['nume']);
    $email = htmlspecialchars($_POST['email']);
    $subiect = htmlspecialchars($_POST['subiect']);
    $mesaj = htmlspecialchars($_POST['mesaj']);

    // Aici s-ar pune în mod normal funcția mail() din PHP pentru a-ți trimite ție emailul.
    // mail("contact@magazinul-tau.ro", "Mesaj nou: $subiect", $mesaj, "From: $email");
    
    // Deocamdată, doar afișăm mesajul de succes
    $mesaj_trimis = true;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Contact Styling */
        .contact-header { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; padding: 40px 0; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        .info-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: none; height: 100%; transition: transform 0.3s; }
        .info-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        
        .icon-box { width: 60px; height: 60px; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 15px; }
        
        .form-floating > .form-control { border-radius: 8px; border-color: #ddd; }
        .form-floating > .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        .btn-send { background-color: #0d6efd; border: none; font-weight: bold; padding: 12px 30px; border-radius: 8px; transition: all 0.3s ease; }
        .btn-send:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }

        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="contact-header text-center">
    <div class="container">
        <h1 class="fw-bold mb-3">Contactează-ne</h1>
        <p class="lead mb-0" style="opacity: 0.9;">Suntem aici să te ajutăm cu orice întrebare despre piesele auto.</p>
    </div>
</div>

<div class="container mb-5 flex-grow-1">
    
    <?php if ($mesaj_trimis): ?>
        <div class="alert alert-success d-flex align-items-center mb-5 shadow-sm p-4 rounded-4" role="alert">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Mesaj trimis cu succes!</h5>
                <p class="mb-0">Îți mulțumim că ne-ai contactat. Unul dintre consultanții noștri va reveni cu un răspuns în cel mai scurt timp posibil.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        
        <div class="col-lg-5">
            <h4 class="fw-bold mb-4 text-dark">Date de Contact</h4>
            
            <div class="row g-3">
                <div class="col-sm-6 col-lg-12">
                    <div class="info-card">
                        <div class="d-flex align-items-center">
                            <div class="icon-box mb-0 me-3"><i class="fas fa-map-marker-alt text-primary"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Adresa Magazinului</h6>
                                <p class="text-muted small mb-0">Strada Romană nr. 55<br>Ploiești, jud. Prahova</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-12">
                    <div class="info-card">
                        <div class="d-flex align-items-center">
                            <div class="icon-box mb-0 me-3 bg-success bg-opacity-10"><i class="fas fa-phone text-success"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Telefon Suport</h6>
                                <p class="text-muted small mb-0"><a href="tel:0723995579" class="text-decoration-none text-muted fw-bold">0723 995 579</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-12">
                    <div class="info-card">
                        <div class="d-flex align-items-center">
                            <div class="icon-box mb-0 me-3 bg-warning bg-opacity-10"><i class="fas fa-envelope text-warning"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted small mb-0"><a href="mailto:alexgabimihai4@gmail.com" class="text-decoration-none text-muted">alexgabimihai4@gmail.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-lg-12">
                    <div class="info-card">
                        <div class="d-flex align-items-center">
                            <div class="icon-box mb-0 me-3 bg-danger bg-opacity-10"><i class="fas fa-clock text-danger"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1">Program de Lucru</h6>
                                <p class="text-muted small mb-0">Luni - Vineri: 08:00 - 17:30<br>Sâmbătă: 08:00 - 13:00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light">
                <h4 class="fw-bold mb-4 text-dark">Trimite-ne un mesaj</h4>
                
                <form method="POST" action="contact.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="numeInput" name="nume" placeholder="Numele tău" required>
                                <label for="numeInput"><i class="fas fa-user text-muted me-1"></i> Nume Complet</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="emailInput" name="email" placeholder="Email" required>
                                <label for="emailInput"><i class="fas fa-envelope text-muted me-1"></i> Adresa de Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="subiectInput" name="subiect" placeholder="Subiect" required>
                                <label for="subiectInput"><i class="fas fa-tag text-muted me-1"></i> Subiectul mesajului (ex: Piesă VW Golf 4)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="mesajInput" name="mesaj" placeholder="Mesaj" style="height: 150px" required></textarea>
                                <label for="mesajInput"><i class="fas fa-comment-dots text-muted me-1"></i> Scrie mesajul tău aici...</label>
                            </div>
                        </div>
                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-send text-white shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Trimite Mesajul
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
        <iframe src="https://maps.google.com/maps?q=strada%20Romana%20nr%2055,%20Ploiesti,%20Prahova&t=&z=16&ie=UTF8&iwloc=&output=embed" width="100%" height="400" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>