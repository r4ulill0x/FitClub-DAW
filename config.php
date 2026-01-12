<?php
// Pornim sesiunea
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Setari basic baza de date
define('DB_HOST', 'localhost');    
define('DB_NAME', 'nume_baza_date'); 
define('DB_USER', 'utilizator_db');         
define('DB_PASS', '');

// Date mail 
define('SMTP_HOST', 'smtp.exemplu.com');
define('SMTP_USER', 'email@exemplu.ro');
define('SMTP_PASS', 'parola_mail_aici'); 
define('SMTP_PORT', 587);        
define('SMTP_FROM', 'noreply@exemplu.ro');
define('SMTP_FROM_NAME', 'FitClub Security');

// Creare DSN (Data Source Name)
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

// Conectare PDO
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Eroare la conectare: ' . $e->getMessage());
}
?>
