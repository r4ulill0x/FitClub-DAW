<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/vendor/phpmailer/Exception.php';
require 'src/vendor/phpmailer/PHPMailer.php';
require 'src/vendor/phpmailer/SMTP.php';

include 'config.php';

$eroare = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nume = trim($_POST['nume']);
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    if (!empty($nume) && !empty($email) && !empty($parola)) {
        $stmt = $pdo->prepare("SELECT id FROM Utilizatori WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $eroare = "Această adresă de email este deja utilizată.";
        } else {
            // generare cod 6 cifre cu data expirare la 15 min
            $code = sprintf("%06d", mt_rand(0, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $hash_parola = password_hash($parola, PASSWORD_DEFAULT);

            // adaugam user nou in db
            $sql = "INSERT INTO Utilizatori (nume, email, parola, rol, 2fa_code, 2fa_expiry) VALUES (?, ?, ?, 'client', ?, ?)";
            $pdo->prepare($sql)->execute([$nume, $email, $hash_parola, $code, $expiry]);
            $user_id = $pdo->lastInsertId();

            // trimitere cod 2fa pe mail
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;
                
                // rezolvare probleme certificate SSL (gasit pe net)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                $mail->addAddress($email, $nume);

                $mail->isHTML(true);
                $mail->Subject = 'Codul tau de verificare FitClub';
                $mail->Body    = "Salut <b>$nume</b>!<br><br>Codul tau de activare pentru contul FitClub este: <b>$code</b>.<br>Acesta expira in 15 minute.";

                $mail->send();

                // salvare id in session pentru verify2fa
                $_SESSION['temp_user_id'] = $user_id;
                header('Location: verify_2fa.php');
                exit;
            } catch (Exception $e) {
                $eroare = "Cont creat, dar email-ul nu a putut fi trimis. Eroare: " . $mail->ErrorInfo;
            }
        }
    } else {
        $eroare = "Toate câmpurile sunt obligatorii.";
    }
}

include 'header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Creează un cont nou</h2>
            <p>Alătură-te comunității FitClub</p>
        </div>

        <?php if($eroare) echo "<div class='alert alert-danger'>$eroare</div>"; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Nume Complet</label>
                <input type="text" name="nume" required placeholder="Ion Popescu">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="nume@exemplu.ro">
            </div>
            <div class="form-group">
                <label>Parolă</label>
                <input type="password" name="parola" required placeholder="********">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Înregistrare</button>
        </form>
        
        <div class="auth-footer">
            <p>Ai deja cont? <a href="login.php">Loghează-te aici</a></p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>