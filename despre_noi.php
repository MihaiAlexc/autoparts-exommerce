<?php
session_start();

// --- CONECTARE DB (Pentru a afișa corect numărul de produse din coș și numele clientului) ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (\PDOException $e) { die("Eroare: " . $e->getMessage()); }

// Câte produse avem în coș?
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despre Noi - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* About Header */
        .about-header { 
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(52, 152, 219, 0.8)), url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&q=80&w=1200') no-repeat center center; 
            background-size: cover;
            color: white; 
            padding: 80px 0; 
            margin-bottom: 50px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }

        /* Content Cards */
        .content-card { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: none; margin-bottom: 30px; }
        .content-card h2 { font-weight: 800; color: #2c3e50; margin-bottom: 20px; }
        .content-card p { color: #555; line-height: 1.8; font-size: 1.05rem; }

        /* Core Values */
        .value-box { text-align: center; padding: 30px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; transition: transform 0.3s; }
        .value-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .value-icon { width: 80px; height: 80px; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 20px auto; }
        .value-box h5 { font-weight: 700; color: #34495e; margin-bottom: 15px; }
        .value-box p { color: #6c757d; font-size: 0.95rem; margin-bottom: 0; }

        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="about-header text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Pasiunea pentru mașini ne definește</h1>
        <p class="lead mb-0" style="opacity: 0.9; max-width: 700px; margin: 0 auto;">Suntem mai mult decât un magazin. Suntem partenerul tău de drum lung, dedicat să mențină mașina ta în cea mai bună formă.</p>
    </div>
</div>

<div class="container mb-5 flex-grow-1">
    
    <div class="row mb-5 align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="content-card h-100 mb-0 border-start border-primary border-5">
                <h2>Povestea AutoParts Pro</h2>
                <p>Am pornit la drum cu un singur scop: să oferim șoferilor și mecanicilor din România o alternativă de încredere, rapidă și transparentă pentru achiziționarea pieselor auto.</p>
                <p>Știm cât de frustrant poate fi să comanzi o piesă și să descoperi că nu se potrivește sau să aștepți zile în șir cu mașina blocată pe elevator. De aceea, am creat o platformă intuitivă, cu o bază de date exactă și un sistem de asistență gata să te ajute la fiecare pas.</p>
                <p class="mb-0 fw-bold text-primary">Misiunea noastră: "Să menținem România în mișcare, cu piese de calitate și servicii impecabile."</p>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1503376794736-11f8b6528b12?auto=format&fit=crop&q=80&w=800" alt="Mecanic auto AutoParts Pro" class="img-fluid rounded-4 shadow-sm" style="object-fit: cover; height: 100%; min-height: 350px;">
        </div>
    </div>

    <h3 class="fw-bold text-center text-dark mb-5">De ce să colaborezi cu noi?</h3>
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="value-box">
                <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                <h5>Garanția Calității</h5>
                <p>Colaborăm exclusiv cu producători de top și furnizori autorizați. Fiecare piesă comercializată respectă standardele de calitate OEM și beneficiază de garanție legală completă.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="value-box">
                <div class="value-icon bg-success bg-opacity-10 text-success"><i class="fas fa-shipping-fast"></i></div>
                <h5>Livrare Rapidă 24h</h5>
                <p>Timpul tău este prețios. Depozitul nostru modern și parteneriatele solide cu firmele de curierat ne permit să expediem majoritatea comenzilor în aceeași zi.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="value-box">
                <div class="value-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-headset"></i></div>
                <h5>Suport Tehnic Specializat</h5>
                <p>Nu ești sigur ce piesă se potrivește? Echipa noastră de consultanți este pregătită să verifice compatibilitatea după seria de șasiu (VIN) și să-ți ofere cea mai bună soluție.</p>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>