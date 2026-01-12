<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitClub</title>
    <link rel="icon" type="image/png" href="src/icons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="src/icons/favicon.svg" />
    <link rel="shortcut icon" href="src/icons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="src/icons/apple-touch-icon.png" />
    <link rel="manifest" href="src/icons/site.webmanifest" />
    <link rel="stylesheet" href="src/css/stil.css">
    </head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Acasă</a></li>
            <li><a href="orar.php">Orar Clase</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profil.php">Profilul Meu</a></li>
                <li><a href="logout.php">Deconectare</a></li>
            <?php else: ?>
                <li><a href="login.php">Autentificare</a></li>
                <li><a href="register.php">Înregistrare</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<hr>
<main>