<?php
include 'config.php';

// verificare user care asteapta verficare mail
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: register.php');
    exit;
}

$eroare = '';
// cerere post cu codul 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cod_introdus = trim($_POST['code']);
    $user_id = $_SESSION['temp_user_id'];

    $stmt = $pdo->prepare("SELECT id, nume, rol, 2fa_code, 2fa_expiry FROM Utilizatori WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $acum = date('Y-m-d H:i:s');
        
        // validare cod cu timp expirare
        if ($user['2fa_code'] === $cod_introdus && $acum <= $user['2fa_expiry']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nume'] = $user['nume'];
            $_SESSION['user_rol'] = $user['rol'];

            // stergere cod generat dupa logare reusita
            $pdo->prepare("UPDATE Utilizatori SET 2fa_code = NULL, 2fa_expiry = NULL WHERE id = ?")->execute([$user_id]);
            
            unset($_SESSION['temp_user_id']); // eliminare id in session pentru verify2fa (luat din register.php)
            
            header('Location: profil.php');
            exit;
        } else {
            $eroare = "Codul introdus este invalid sau a expirat.";
        }
    }
}

include 'header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Verificare Email</h2>
            <p>Am trimis un cod de 6 cifre pe email-ul tău. Te rugăm să-l introduci mai jos pentru a activa contul.</p>
        </div>

        <?php if($eroare) echo "<div class='alert alert-danger'>$eroare</div>"; ?>

        <form method="POST" class="auth-form" style="text-align: center;">
            <div class="form-group">
                <input type="text" name="code" maxlength="6" required placeholder="000000" 
                       style="font-size: 32px; text-align: center; letter-spacing: 10px; border: 2px solid #27ae60;">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Confirmă Codul</button>
        </form>
        
        <div class="auth-footer" style="margin-top: 20px;">
            <p><a href="register.php">Anulează și reia înregistrarea</a></p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>