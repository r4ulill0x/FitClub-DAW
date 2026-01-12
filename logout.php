<?php
include 'config.php'; 

// golim variabilele 
$_SESSION = array();

// distrugem sesiunea
session_destroy();

// redirect index.php
header('Location: index.php?mesaj=Deconectare reusita!');
exit;
?>