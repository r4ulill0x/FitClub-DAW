<?php
include 'config.php';

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    include 'header.php';
    echo '<h2>🚫 Acces Interzis</h2>';
    echo '<p style="color:var(--culoare-eroare); font-weight:bold; font-family: \'Orbitron\', sans-serif;">Nu aveți permisiunea necesară pentru a accesa această resursă.</p>';
    include 'footer.php';
    exit;
}

$id_editare = 0;
$nume_edit = '';
$descriere_edit = '';
$pret_edit = '';
$durata_edit = '';
$mod_editare = false;
$mesaj = '';
// Adaugare abonamente
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nume = trim($_POST['nume']);
    $descriere = trim($_POST['descriere']);
    $pret = filter_var($_POST['pret'], FILTER_VALIDATE_FLOAT);
    $durata_zile = filter_var($_POST['durata_zile'], FILTER_VALIDATE_INT);
    $id_hidden = $_POST['id'] ?? 0;

    if (empty($nume) || $pret === false || $durata_zile === false) {
        $mesaj = '<p style="color:red;">Nume, preț și durată (zile) sunt obligatorii și trebuie să fie corecte.</p>';
    } else {
        try {
            if (empty($id_hidden)) {
                $sql = "INSERT INTO Abonamente (nume, descriere, pret, durata_zile) VALUES (?, ?, ?, ?)";
                $interogare = $pdo->prepare($sql);
                $interogare->execute([$nume, $descriere, $pret, $durata_zile]);
                $mesaj = '<p style="color:green;">Abonament adăugat cu succes!</p>';
            } else {
                $sql = "UPDATE Abonamente SET nume = ?, descriere = ?, pret = ?, durata_zile = ? WHERE id = ?";
                $interogare = $pdo->prepare($sql);
                $interogare->execute([$nume, $descriere, $pret, $durata_zile, $id_hidden]);
                $mesaj = '<p style="color:green;">Abonament modificat cu succes!</p>';
            }
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare: ' . $e->getMessage() . '</p>';
        }
    }
}

// Stergere/modifcare abonamente
if (isset($_GET['actiune'])) {
    $actiune = $_GET['actiune'];
    $id = $_GET['id'] ?? 0;

    if ($actiune == 'sterge' && $id > 0) {
        try {
            $sql = "DELETE FROM Abonamente WHERE id = ?";
            $interogare = $pdo->prepare($sql);
            $interogare->execute([$id]);
            header('Location: admin_abonamente.php?mesaj_succes=Sters!');
            exit;
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare la ștergere: ' . $e->getMessage() . '</p>';
        }
    }
    
    if ($actiune == 'modifica' && $id > 0) {
        $sql = "SELECT * FROM Abonamente WHERE id = ?";
        $interogare = $pdo->prepare($sql);
        $interogare->execute([$id]);
        $abonament_curent = $interogare->fetch();
        
        if ($abonament_curent) {
            $id_editare = $abonament_curent['id'];
            $nume_edit = $abonament_curent['nume'];
            $descriere_edit = $abonament_curent['descriere'];
            $pret_edit = $abonament_curent['pret'];
            $durata_edit = $abonament_curent['durata_zile'];
            $mod_editare = true;
        }
    }
}


include 'header.php';
echo '<div style="margin-bottom: 20px;"><a href="profil.php">&larr; Înapoi la Profil</a></div>';
?>

<h2>Management Abonamente (Admin)</h2>

<?php echo $mesaj; ?>
<?php if (isset($_GET['mesaj_succes'])) echo '<p style="color:green;">Operațiune reușită!</p>'; ?>


<h3><?php echo $mod_editare ? 'Modifică Abonament' : 'Adaugă Abonament Nou'; ?></h3>
<form action="admin_abonamente.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id_editare; ?>">
    
    <div>
        <label for="nume">Nume Abonament:</label><br>
        <input type="text" id="nume" name="nume" value="<?php echo htmlspecialchars($nume_edit); ?>" required>
    </div>
    <br>
    <div>
        <label for="descriere">Descriere (opțional):</label><br>
        <textarea id="descriere" name="descriere"><?php echo htmlspecialchars($descriere_edit); ?></textarea>
    </div>
    <br>
    <div>
        <label for="pret">Preț (RON):</label><br>
        <input type="number" step="0.01" id="pret" name="pret" value="<?php echo htmlspecialchars($pret_edit); ?>" required>
    </div>
    <br>
    <div>
        <label for="durata_zile">Durată (zile):</label><br>
        <input type="number" id="durata_zile" name="durata_zile" value="<?php echo htmlspecialchars($durata_edit); ?>" required>
    </div>
    <br>
    <div>
        <button type="submit"><?php echo $mod_editare ? 'Salvează Modificările' : 'Adaugă Abonament'; ?></button>
        <?php if ($mod_editare): ?>
            <a href="admin_abonamente.php">Anulează Modificarea</a>
        <?php endif; ?>
    </div>
</form>

<hr>

<h3>Listă Abonamente Existente</h3>
<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nume</th>
            <th>Descriere</th>
            <th>Preț (RON)</th>
            <th>Durată (zile)</th>
            <th>Acțiuni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        try {
            $interogare = $pdo->query("SELECT * FROM Abonamente ORDER BY nume ASC");
            $abonamente = $interogare->fetchAll();
            
            if (count($abonamente) == 0) {
                echo '<tr><td colspan="6">Nu există abonamente definite.</td></tr>';
            }
            
            foreach ($abonamente as $abon) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($abon['id']) . '</td>';
                echo '<td>' . htmlspecialchars($abon['nume']) . '</td>';
                echo '<td>' . htmlspecialchars($abon['descriere']) . '</td>';
                echo '<td>' . htmlspecialchars($abon['pret']) . '</td>';
                echo '<td>' . htmlspecialchars($abon['durata_zile']) . '</td>';
                echo '<td>';
                echo '<a href="admin_abonamente.php?actiune=modifica&id=' . $abon['id'] . '">Modifică</a>';
                echo ' | ';
                echo '<a href="admin_abonamente.php?actiune=sterge&id=' . $abon['id'] . '" 
                       onclick="return confirm(\'Ești sigur că vrei să ștergi acest abonament?\');">Șterge</a>';
                echo '</td>';
                echo '</tr>';
            }
        } catch (PDOException $e) {
            echo '<tr><td colspan="6">Eroare la afișare: ' . $e->getMessage() . '</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>