<?php
include 'config.php';

$eroare = '';

// verificare user deja logat
if (isset($_SESSION['user_id'])) {
    header('Location: profil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    if (empty($email) || empty($parola)) {
        $eroare = "Te rugăm să introduci atât email-ul cât și parola.";
    } else {
        $sql = "SELECT id, nume, email, parola, rol FROM Utilizatori WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // folosire password verify cu parola din db
        if ($user && password_verify($parola, $user['parola'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nume'] = $user['nume'];
            $_SESSION['user_rol'] = $user['rol'];

            header('Location: profil.php');
            exit;
        } else {
            $eroare = "Email sau parolă incorectă.";
        }
    }
}

include 'header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Autentificare</h2>
            <p>Introdu datele contului tău FitClub</p>
        </div>
        
        <?php if ($eroare): ?>
            <div class="alert alert-danger">
                <?php echo $eroare; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Adresă Email</label>
                <input type="email" id="email" name="email" required placeholder="nume@exemplu.ro" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="parola">Parolă</label>
                <input type="password" id="parola" name="parola" required placeholder="********">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                Conectare
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Nu ai încă un cont? <a href="register.php">Înregistrează-te acum</a></p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>