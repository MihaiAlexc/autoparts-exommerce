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
    <title>Politica de Cookies - AutoParts Pro</title>
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
        .legal-card h2 i { color: #0d6efd; margin-right: 10px; font-size: 1.1rem; }
        
        .legal-card p, .legal-card li { color: #555; line-height: 1.7; font-size: 1.05rem; }
        .legal-card ul { margin-bottom: 20px; }
        .legal-card li { margin-bottom: 10px; }
        
        .cookie-type { background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .cookie-type h4 { font-size: 1.1rem; font-weight: bold; color: #333; margin-bottom: 5px; }
        .cookie-type p { margin: 0; font-size: 0.95rem; }
        
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
            <li class="breadcrumb-item active" aria-current="page">Politica de Cookies</li>
          </ol>
        </nav>

        <div class="legal-card">
            <h1>Politica de Utilizare a Cookie-urilor</h1>
            <div class="last-updated">Ultima actualizare: <strong><?php echo date('d.m.Y'); ?></strong></div>

            <h2><i class="fas fa-cookie-bite"></i> 1. Ce sunt cookie-urile?</h2>
            <p>Cookie-urile sunt fișiere text de mici dimensiuni, formate din litere și numere, care vor fi salvate pe computerul, terminalul mobil sau alte echipamente ale unui utilizator de pe care se accesează internetul. Cookie-ul este instalat prin solicitarea emisă de către un web-server unui browser (ex: Internet Explorer, Chrome, Safari) și este complet "pasiv" (nu conține programe software, viruși sau spyware și nu poate accesa informațiile de pe hard-drive-ul utilizatorului).</p>

            <h2><i class="fas fa-layer-group"></i> 2. La ce sunt folosite Cookie-urile?</h2>
            <p>Aceste fișiere fac posibilă recunoașterea terminalului utilizatorului și prezentarea conținutului într-un mod relevant, adaptat preferințelor utilizatorului. Cookie-urile asigură utilizatorilor o experiență plăcută de navigare pe <strong>AutoParts Pro</strong> și susțin eforturile noastre pentru a oferi servicii confortabile utilizatorilor (ex: menținerea produselor în coșul de cumpărături, păstrarea sesiunii de logare active, preferințele în materie de confidențialitate online).</p>

            <h2><i class="fas fa-clipboard-list"></i> 3. Ce fel de cookie-uri folosim?</h2>
            
            <div class="cookie-type">
                <h4><i class="fas fa-shield-alt text-success me-2"></i>Cookie-uri strict necesare (Sesiuni)</h4>
                <p>Acestea sunt esențiale pentru funcționarea site-ului. Fără ele, nu ați putea adăuga piese în coșul de cumpărături, nu ați putea finaliza comanda și nu v-ați putea loga în contul de client. Aceste cookie-uri nu rețin date de identificare personală și sunt șterse de cele mai multe ori la închiderea browser-ului.</p>
            </div>

            <div class="cookie-type" style="border-left-color: #f39c12;">
                <h4><i class="fas fa-chart-line text-warning me-2"></i>Cookie-uri de performanță / analiză</h4>
                <p>Aceste cookie-uri ne permit să numărăm vizitele și sursele de trafic, astfel încât să putem măsura și îmbunătăți performanța site-ului nostru. Ne ajută să știm ce piese auto sunt mai populare și să vedem cum se mișcă vizitatorii pe site.</p>
            </div>

            <h2><i class="fas fa-sliders-h"></i> 4. Cum poți controla sau șterge cookie-urile?</h2>
            <p>În general, browserele sunt setate implicit să accepte cookie-uri. Cu toate acestea, puteți să vă configurați browser-ul pentru a respinge toate cookie-urile sau pentru a vă alerta atunci când un cookie este trimis către dispozitivul dumneavoastră.</p>
            <p>Atenție: Dacă alegeți să dezactivați cookie-urile strict necesare, este foarte probabil să nu mai puteți plasa comenzi pe site-ul nostru, deoarece coșul de cumpărături nu va mai funcționa.</p>
            
            <ul>
                <li>Pentru setările cookie-urilor din <strong>Google Chrome</strong>, căutați "Clear browsing data" în setări.</li>
                <li>Pentru setările cookie-urilor din <strong>Mozilla Firefox</strong>, accesați opțiunea "Privacy & Security".</li>
                <li>Pentru setările cookie-urilor din <strong>Safari</strong>, accesați fila "Preferences" și apoi "Privacy".</li>
            </ul>

            <h2><i class="fas fa-envelope-open-text"></i> 5. Contact</h2>
            <p>Pentru orice întrebări suplimentare cu privire la modul în care utilizăm cookie-urile, vă rugăm să ne contactați la adresa de email: <strong>alexgabimihai4@gmail.com</strong>.</p>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>