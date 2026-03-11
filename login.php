<?php
session_start();
// --- CONECTARE BAZĂ DE DATE ---
$host = 'localhost'; $db = 'piese_auto_db'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (\PDOException $e) { die("Eroare DB: " . $e->getMessage()); }

// Dacă e deja logat, îl trimitem la treabă
if (isset($_SESSION['admin_logat']) && $_SESSION['admin_logat'] === true) {
    header("Location: admin.php"); exit();
}

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_trimis = $_POST['user'];
    $pass_trimisa = $_POST['pass'];

    // 1. Căutăm userul "admin" în tabel
    $stmt = $pdo->prepare("SELECT * FROM admini WHERE username = ?");
    $stmt->execute([$user_trimis]);
    $admin_gasit = $stmt->fetch();

    if ($admin_gasit) {
        // 2. Verificăm parola criptată
        // password_verify compară "1234" cu "$2y$10$..." din baza de date
        if (password_verify($pass_trimisa, $admin_gasit['password'])) {
            // E CORECT!
            $_SESSION['admin_logat'] = true;
            header("Location: admin.php");
            exit();
        } else {
            $eroare = "❌ Parolă greșită!";
        }
    } else {
        $eroare = "❌ Acest user nu există!";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login Securizat</title>
    <style>
        body { font-family: sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); width: 300px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #219150; }
        h2 { margin-top: 0; color: #333; }
        .error { color: #e74c3c; margin-bottom: 15px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🔐 Admin Zone</h2>
    <p style="color:#777; font-size:12px;">Login conectat la baza de date</p>
    
    <?php if ($eroare): ?>
        <div class="error"><?= $eroare ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="user" placeholder="Utilizator" required>
        <input type="password" name="pass" placeholder="Parola" required>
        <button type="submit">Autentificare</button>
    </form>
    
    <br>
    <a href="index.php" style="text-decoration:none; color:#7f8c8d; font-size:12px;">← Înapoi la Magazin</a>
</div>

</body>
</html>