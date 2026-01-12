<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/vendor/phpmailer/Exception.php';
require 'src/vendor/phpmailer/PHPMailer.php';
require 'src/vendor/phpmailer/SMTP.php';

include 'config.php';

$eroare = '';
$succes = '';
// trimitere cerere post 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nume = trim($_POST['nume']);
    $subiect = trim($_POST['subiect']);
    $mesaj_corp = trim($_POST['mesaj']);

    $user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'smtp.exemplu.com';

    if (!empty($nume) && !empty($mesaj_corp)) {
        
        try {
            $sql = "INSERT INTO Mesaje_Contact (nume, email, subiect, mesaj) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$nume, $user_email, $subiect, $mesaj_corp]);
        } catch (PDOException $e) {
        }

        $mail = new PHPMailer(true);
        try {
			//setari prof
            $mail->SMTPDebug = 0; 

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

            $mail->setFrom(SMTP_USER, 'FitClub Formular');
            
            $mail->addAddress('smtp.exemplu.com'); 
            
            $mail->isHTML(true);
            $mail->Subject = "Mesaj Nou FitClub: " . $subiect;
            $mail->Body    = "
                <div style='font-family: sans-serif; line-height: 1.6;'>
                    <h3>Mesaj nou primit prin formularul de contact</h3>
                    <p><b>Expeditor:</b> $nume</p>
                    <p><b>Subiect:</b> $subiect</p>
                    <hr>
                    <p><b>Mesaj:</b><br>" . nl2br(htmlspecialchars($mesaj_corp)) . "</p>
                </div>
            ";

            $mail->send();
            $succes = "Mesajul a fost trimis cu succes către administrator!";
        } catch (Exception $e) {
            $eroare = "Eroare la trimitere: " . $mail->ErrorInfo;
        }
    } else {
        $eroare = "Te rugăm să completezi numele și mesajul.";
    }
}

include 'header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Contact</h2>
            <p>Trimite un mesaj rapid către noi</p>
        </div>

        <?php if ($succes): ?>
            <div class="alert alert-success"><?php echo $succes; ?></div>
        <?php endif; ?>

        <?php if ($eroare): ?>
            <div class="alert alert-danger"><?php echo $eroare; ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php" class="auth-form">
            <div class="form-group">
                <label for="nume">Numele tău</label>
                <input type="text" id="nume" name="nume" required placeholder="Ex: Ion Popescu">
            </div>
            
            <div class="form-group">
                <label for="subiect">Subiect</label>
                <input type="text" id="subiect" name="subiect" required placeholder="Ex: Întrebare abonament">
            </div>
            
            <div class="form-group">
                <label for="mesaj">Mesaj</label>
                <textarea id="mesaj" name="mesaj" rows="5" required 
                          placeholder="Scrie mesajul tău aici..." 
                          style="width:100%; border:1px solid #ddd; border-radius: 5px; padding:10px; box-sizing: border-box;"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Trimite Mesajul</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
