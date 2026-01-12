<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?eroare=Acces interzis. Va rugam sa va autentificati.');
    exit;
}

include 'header.php';
?>

<h2>Profilul Meu - Bun venit, <?php echo htmlspecialchars($_SESSION['user_nume']); ?>!</h2>

<p>Aici sunt detaliile tale:</p>
<ul>
    <li>ID Utilizator: <?php echo htmlspecialchars($_SESSION['user_id']); ?></li>
    <li>Nume: <?php echo htmlspecialchars($_SESSION['user_nume']); ?></li>
    <li>Rol: <?php echo htmlspecialchars($_SESSION['user_rol']); ?></li>
</ul>

<br>

<?php
// erificare de rol de admin
if ($_SESSION['user_rol'] == 'admin') {
    echo '<h3>Panou Administrator</h3>';
    echo '<p>Pentru că ești admin, ai acces la:</p>';
    echo '<ul>';
    echo '<li><a href="admin_abonamente.php">Management Abonamente</a></li>';
    echo '<li><a href="admin_clase.php">Management Clase</a></li>';
    echo '<li><a href="admin_orar.php">Management Orar Clase</a></li>';
    echo '<li><a href="admin_utilizatori.php">Management Utilizatori</a></li>';
    echo '<li><a href="admin_statistici.php">Statistici Web</a></li>';
    echo '</ul>';
}
?>

<?php
include 'footer.php';
?>