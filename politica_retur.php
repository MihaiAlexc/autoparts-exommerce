<?php
session_start();

// Preluăm numărul de produse din coș pentru a-l afișa în header
$total_in_cos = isset($_SESSION['cos']) ? array_sum($_SESSION['cos']) : 0;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica de Retur - AutoParts Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navbar */
        .navbar { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }

        /* Conținut Legal */
        .legal-container { max-width: 800px; margin: 40px auto; }
        .legal-card { background: white; border-radius: 12px; padding: 40px 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: none; }
        
        .legal-card h1 { font-weight: 800; color: #2c3e50; margin-bottom: 10px; font-size: 2rem; }
        .legal-card .last-updated { color: #7f8c8d; font-size: 0.9rem; margin-bottom: 40px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        
        .legal-card h2 { font-weight: 700; color: #34495e; font-size: 1.3rem; margin-top: 35px; margin-bottom: 15px; display: flex; align-items: center; }
        .legal-card h2 i { color: #dc3545; margin-right: 10px; font-size: 1.1rem; }
        
        .legal-card p, .legal-card li { color: #555; line-height: 1.7; font-size: 1.05rem; }
        .legal-card ul { margin-bottom: 20px; }
        .legal-card li { margin-bottom: 10px; }
        
        .alert-legal { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; color: #721c24; font-weight: 500; }
        
        footer { background: #212529; color: #adb5bd; padding-top: 50px; margin-top: auto; }
        .text-hover-white:hover { opacity: 1 !important; color: white !important; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container flex-grow-1">
    <div class="legal-container">
        
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Acasă</a></li>
            <li class="breadcrumb-item active" aria-current="page">Politica de Retur</li>
          </ol>
        </nav>

        <div class="legal-card">
            <h1>Politica de Retur</h1>
            <div class="last-updated">Ultima actualizare: <strong><?php echo date('d.m.Y'); ?></strong></div>

            <h2><i class="fas fa-calendar-alt"></i> 1. Dreptul de retragere (14 zile)</h2>
            <p>Conform Ordonanței de Urgență nr. 34/2014 privind drepturile consumatorilor, aveți dreptul de a returna produsele achiziționate online de pe <strong>AutoParts Pro</strong>, fără a preciza motivele, în termen de <strong>14 zile calendaristice</strong> de la ziua în care intrați în posesia fizică a produselor.</p>

            <h2><i class="fas fa-clipboard-check"></i> 2. Condiții de acceptare a returului</h2>
            <p>Pentru ca returul să fie acceptat, piesele auto trebuie să respecte cu strictețe următoarele condiții:</p>
            <ul>
                <li><strong>Stare perfectă:</strong> Piesa nu trebuie să prezinte urme de montaj, zgârieturi, lovituri, urme de vaselină, ulei sau alte lichide.</li>
                <li><strong>Ambalaj original:</strong> Produsul trebuie returnat în ambalajul original al producătorului (cutia piesei), fără ca acesta să fie deteriorat, rupt sau cu bandă adezivă (scotch) lipită direct pe el. Vă rugăm să ambalați cutia piesei într-o altă cutie de protecție pentru transport.</li>
                <li><strong>Etichete intacte:</strong> Toate etichetele de identificare ale piesei și ale producătorului trebuie să fie intacte.</li>
            </ul>

            <div class="alert-legal">
                <i class="fas fa-ban me-2"></i> <strong>Atenție!</strong> Piesele care au fost montate pe mașină NU se pot returna. Dacă ați montat piesa și aceasta s-a defectat, produsul intră sub incidența garanției, nu a procedurii de retur.
            </div>

            <h2><i class="fas fa-dolly"></i> 3. Produse care NU se pot returna</h2>
            <p>Sunt exceptate de la dreptul de retur următoarele categorii de produse:</p>
            <ul>
                <li>Uleiurile de motor, antigelul, lichidul de frână, aditivii și alte produse chimice/fluide care au fost desigilate.</li>
                <li>Piesele electronice și componentele electrice auto (senzori, relee, module) care au fost scoase din ambalajul de protecție.</li>
                <li>Piesele aduse pe "Comandă Specială" (piese originale OE care nu se află în stocul uzual și sunt comandate extern la cererea expresă a clientului).</li>
            </ul>

            <h2><i class="fas fa-envelope"></i> 4. Procedura de retur</h2>
            <p>Pentru a iniția un retur, urmați acești pași simpli:</p>
            <ol>
                <li>Trimiteți un email la <strong>alexgabimihai4@gmail.com</strong> cu subiectul "Cerere Retur Comanda #[Număr Comandă]" sau sunați-ne la <strong>0723995579</strong>.</li>
                <li>Specificați numele dvs., numărul comenzii, piesa pe care doriți să o returnați și contul IBAN în care doriți rambursarea banilor (plus numele titularului de cont).</li>
                <li>Ambalați corespunzător piesa și chemați orice firmă de curierat (ex. Fan Courier, Sameday, Cargus) pentru a expedia coletul către noi.</li>
                <li><strong>Costul transportului de retur este suportat integral de dumneavoastră.</strong> Nu acceptăm colete trimise cu plata transportului la destinație sau cu valoare ramburs.</li>
            </ol>

            <h2><i class="fas fa-money-bill-wave"></i> 5. Rambursarea banilor</h2>
            <p>Odată ce piesa ajunge la depozitul nostru și este inspectată tehnic pentru a ne asigura că respectă condițiile de la punctul 2, vom vira contravaloarea produselor în contul IBAN furnizat de dumneavoastră.</p>
            <p>Rambursarea se va efectua în cel mult <strong>14 zile calendaristice</strong> de la primirea produsului returnat, conform reglementărilor legale.</p>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>