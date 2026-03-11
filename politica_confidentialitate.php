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
    <title>Politica de Confidențialitate - AutoParts Pro</title>
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
            <li class="breadcrumb-item active" aria-current="page">Politica de Confidențialitate</li>
          </ol>
        </nav>

        <div class="legal-card">
            <h1>Politica de Confidențialitate</h1>
            <div class="last-updated">Ultima actualizare: <strong><?php echo date('d.m.Y'); ?></strong></div>

            <h2><i class="fas fa-info-circle"></i> 1. Introducere</h2>
            <p>Bine ai venit pe <strong>AutoParts Pro</strong>. Ne angajăm să îți protejăm datele personale și dreptul la confidențialitate. Această politică explică modul în care colectăm, folosim și protejăm informațiile tale atunci când vizitezi site-ul nostru și cumperi piese auto de la noi.</p>

            <h2><i class="fas fa-database"></i> 2. Ce informații colectăm</h2>
            <p>Pentru a-ți putea oferi serviciile și produsele noastre, colectăm următoarele date:</p>
            <ul>
                <li><strong>Informații despre Cont și Comenzi:</strong> Când îți creezi un cont sau plasezi o comandă, colectăm numele, adresa de e-mail, numărul de telefon și adresa de livrare.</li>
                <li><strong>Informații de Plată:</strong> Procesăm plățile pentru comenzile tale. <em>(Notă: Datele cardului bancar sunt procesate în siguranță de către partenerii noștri de plată. Noi nu stocăm detaliile complete ale cardului tău.)</em></li>
                <li><strong>Date de Navigare și Obiceiuri:</strong> Folosim cookie-uri și tehnologii similare pentru a urmări modul în care interacționezi cu site-ul nostru. Acest lucru ne ajută să înțelegem ce piese cauți și să îmbunătățim funcționarea magazinului online.</li>
            </ul>

            <h2><i class="fas fa-tasks"></i> 3. Cum folosim informațiile tale</h2>
            <p>Folosim datele colectate în următoarele scopuri:</p>
            <ul>
                <li>Pentru a procesa și livra comenzile tale de piese auto.</li>
                <li>Pentru a gestiona și securiza contul tău de utilizator.</li>
                <li>Pentru a comunica cu tine în legătură cu statusul comenzii, livrarea acesteia și asistența pentru clienți.</li>
                <li>Pentru a analiza traficul pe site și a optimiza experiența ta de cumpărături.</li>
            </ul>

            <h2><i class="fas fa-share-alt"></i> 4. Cu cine partajăm informațiile tale</h2>
            <p><strong>Nu vindem datele tale personale către terți.</strong> Partajăm informațiile doar cu parteneri de încredere, strict pentru desfășurarea activității noastre:</p>
            <ul>
                <li><strong>Firme de Curierat:</strong> (ex. Fan Courier, Sameday, Cargus) pentru a-ți putea livra piesele la adresă.</li>
                <li><strong>Procesatori de Plăți:</strong> Pentru a gestiona tranzacțiile financiare în siguranță.</li>
                <li><strong>Furnizori de Servicii IT și Analiză:</strong> Pentru găzduirea site-ului și analizarea performanței acestuia (ex. Google Analytics).</li>
            </ul>

            <h2><i class="fas fa-user-shield"></i> 5. Drepturile tale (conform GDPR)</h2>
            <p>Conform legislației europene, ai următoarele drepturi:</p>
            <ul>
                <li><strong>Dreptul de acces:</strong> Poți solicita o copie a datelor pe care le deținem despre tine.</li>
                <li><strong>Dreptul la rectificare:</strong> Poți corecta datele inexacte sau incomplete din contul tău.</li>
                <li><strong>Dreptul la ștergere ("dreptul de a fi uitat"):</strong> Poți solicita ștergerea contului și a datelor tale personale.</li>
                <li><strong>Dreptul de a retrage consimțământul:</strong> Poți refuza utilizarea cookie-urilor de urmărire în orice moment.</li>
            </ul>
            <p>Dacă dorești să îți exerciți oricare dintre aceste drepturi, ne poți contacta la <strong>contact@magazinul-tau.ro</strong>.</p>

            <h2><i class="fas fa-archive"></i> 6. Cât timp păstrăm datele</h2>
            <p>Păstrăm informațiile tale doar atât timp cât este necesar pentru a îndeplini scopurile descrise în această politică sau pe perioada impusă de legile fiscale și contabile din România (de regulă, facturile și datele aferente trebuie păstrate pentru o perioadă determinată de lege).</p>

            <h2><i class="fas fa-envelope-open-text"></i> 7. Contact</h2>
            <p>Dacă ai întrebări sau nelămuriri legate de această politică, ne poți contacta la:</p>
            <ul>
                <li><strong>E-mail:</strong> alexgabimihai4@gmail.com</li>
                <li><strong>Adresă:</strong> Ploiesti</li>
            </ul>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>