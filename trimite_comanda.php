<?php
session_start();

$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Dacă se primesc datele din formularul de comandă
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['total_comanda'])) {
    
    if (isset($_SESSION['cos']) && count($_SESSION['cos']) > 0) {
        
        // Preluăm ID-ul clientului (Dacă e logat, luăm ID-ul. Dacă e vizitator, punem NULL)
        $id_client = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : NULL;
        
        $nume = trim($_POST['nume']);
        $telefon = trim($_POST['telefon']);
        $adresa = trim($_POST['adresa']);
        $total = $_POST['total_comanda'];
        $data = date('Y-m-d H:i:s');

        try {
            // Începem tranzacția SQL
            $pdo->beginTransaction();

            // 1. Inserăm comanda principală
            // Dacă coloana id_client nu acceptă NULL în baza ta de date, va trebui s-o modifici din phpMyAdmin (să bifezi "Null" la id_client în tabelul comenzi)
            $sql_comanda = "INSERT INTO comenzi (id_client, nume_client, telefon, adresa, total, data_comanda) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql_comanda);
            $stmt->execute([$id_client, $nume, $telefon, $adresa, $total, $data]);
            
            // Luăm ID-ul comenzii tocmai plasate
            $id_comanda_noua = $pdo->lastInsertId();

            // 2. Parcurgem piesele din coș și le salvăm la comandă
            foreach ($_SESSION['cos'] as $id_produs => $cantitate) {
                // Luăm prețul actual și stocul curent direct din baza de date
                $stmt_pret = $pdo->prepare("SELECT pret, stoc FROM produse WHERE id = ?");
                $stmt_pret->execute([$id_produs]);
                $produs_info = $stmt_pret->fetch(PDO::FETCH_ASSOC);

                if ($produs_info) {
                    // Inserăm în detalii_comanda
                    $sql_detaliu = "INSERT INTO detalii_comanda (id_comanda, id_produs, cantitate, pret) VALUES (?, ?, ?, ?)";
                    $pdo->prepare($sql_detaliu)->execute([$id_comanda_noua, $id_produs, $cantitate, $produs_info['pret']]);

                    // 3. SCĂDEM STOCUL
                    $noul_stoc = $produs_info['stoc'] - $cantitate;
                    if ($noul_stoc < 0) $noul_stoc = 0; // Prevenim stocurile negative
                    
                    $pdo->prepare("UPDATE produse SET stoc = ? WHERE id = ?")->execute([$noul_stoc, $id_produs]);
                }
            }

            // Salvăm totul definitiv
            $pdo->commit();
            
            // Golim coșul
            $_SESSION['cos'] = []; 

            // Redirecționăm clientul spre pagina de MULȚUMIRE
            header("Location: succes.php?id_comanda=" . $id_comanda_noua); 
            exit();

        } catch (Exception $e) {
            $pdo->rollBack(); // Dacă a dat eroare, anulăm modificările
            die("Eroare la salvarea comenzii. Te rugăm să încerci din nou. Detalii: " . $e->getMessage());
        }
    } else {
        // Dacă a încercat să comande cu coșul gol
        header("Location: index.php"); 
        exit();
    }
} else {
    // Dacă a intrat direct pe link
    header("Location: index.php");
    exit();
}
?>