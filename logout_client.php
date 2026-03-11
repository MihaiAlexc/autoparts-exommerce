<?php
session_start();
// Distrugem doar sesiunea clientului, nu și a adminului (dacă e cazul)
unset($_SESSION['client_id']);
unset($_SESSION['client_nume']);
unset($_SESSION['client_email']);
header("Location: index.php");
exit();
?>