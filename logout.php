<?php
session_start();
session_destroy(); // Distruge sesiunea (îți taie brățara de acces)
header("Location: login.php"); // Te trimite înapoi la login
exit();
?>