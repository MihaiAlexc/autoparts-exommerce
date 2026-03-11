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
    <title>Termeni și Condiții - AutoParts Pro</title>
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
        
        .alert-legal { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; color: #856404; font-weight: 500; }
        
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
            <li class="breadcrumb-item active" aria-current="page">Termeni și Condiții</li>
          </ol>
        </nav>

        <div class="legal-card">
            <h1>Termeni și Condiții</h1>
            <div class="last-updated">Ultima actualizare: <strong><?php echo date('d.m.Y'); ?></strong></div>

            <h2><i class="fas fa-file-contract"></i> 1. Dispoziții Generale</h2>
            <p>Utilizarea acestui magazin online (navigarea, crearea unui cont, plasarea comenzilor) implică acceptarea în totalitate a prezentelor Termeni și Condiții. Magazinul <strong>AutoParts Pro</strong> își rezervă dreptul de a modifica aceste prevederi fără o notificare prealabilă, actualizările fiind vizibile pe această pagină.</p>

            <h2><i class="fas fa-box-open"></i> 2. Produse și Prețuri</h2>
            <p>Toate prețurile afișate pe site sunt exprimate în RON și includ TVA. Facem eforturi constante pentru a menține acuratețea informațiilor (inclusiv poze, descrieri și coduri OE). Cu toate acestea, ne rezervăm dreptul de a anula comenzile în cazul în care un preț a fost afișat eronat dintr-o eroare tehnică (preț derizoriu conform art. 1665 Cod Civil).</p>
            <p>Disponibilitatea stocurilor este afișată cu titlu informativ. O comandă se consideră confirmată doar după contactarea clientului (telefonic sau pe email) de către un operator AutoParts Pro.</p>

            <h2><i class="fas fa-truck-fast"></i> 3. Livrare și Plată</h2>
            <p>Livrarea produselor se face exclusiv pe teritoriul României, prin intermediul firmelor de curierat rapid (Fan Courier, Sameday, etc.). Termenul estimat de livrare este de 24-48 de ore lucrătoare din momentul confirmării comenzii.</p>
            <p>Plata se poate efectua în sistem ramburs (la curier) sau online cu cardul bancar (dacă această opțiune este activată în pagina de checkout).</p>

            <h2><i class="fas fa-tools"></i> 4. Garanția Produselor Auto (IMPORTANT)</h2>
            <p>Toate piesele comercializate beneficiază de garanție conform legislației în vigoare. Cu toate acestea, având în vedere natura produselor, se aplică o regulă strictă privind validarea garanției:</p>
            
            <div class="alert-legal">
                <i class="fas fa-exclamation-triangle me-2"></i> Pentru a beneficia de garanție, piesele auto TREBUIE montate într-un service auto autorizat R.A.R. În cazul unei reclamații/defecțiuni, clientul este obligat să prezinte devizul de montaj, factura fiscală emisă de service și nota de constatare a defecțiunii.
            </div>

            <p>Garanția se anulează dacă piesa prezintă urme de montaj incorect, lovituri, a fost folosită în alte scopuri decât cele recomandate de producător, sau a fost montată în regie proprie (fără autorizație RAR).</p>

            <h2><i class="fas fa-undo-alt"></i> 5. Dreptul de Retur</h2>
            <p>Conform O.U.G. 34/2014, consumatorul are dreptul de a returna produsele achiziționate online în termen de <strong>14 zile calendaristice</strong>, fără penalități și fără a invoca un motiv. Produsul returnat trebuie să fie în aceeași stare în care a fost livrat (în ambalajul original, fără urme de montaj, ulei, vaselină sau uzură, cu etichetele intacte).</p>
            <p>Costul transportului pentru retur va fi suportat de către client. Rambursarea contravalorii se va face în termen de maxim 14 zile de la recepția returului, în contul IBAN indicat de client.</p>

            <h2><i class="fas fa-balance-scale"></i> 6. Limitarea Răspunderii</h2>
            <p>Verificarea compatibilității pieselor comandate cade în responsabilitatea cumpărătorului (prin compararea codurilor OE sau prin furnizarea seriei de șasiu). AutoParts Pro nu își asumă răspunderea pentru piesele comandate greșit de către client.</p>
            
            <h2><i class="fas fa-envelope-open-text"></i> 7. Litigii</h2>
            <p>Orice dispută apărută între clienți și AutoParts Pro se va rezolva pe cale amiabilă. În cazul în care acest lucru nu este posibil, litigiul va fi soluționat de instanțele judecătorești competente din România.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>