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
$id_antrenor_edit = 0; 
$mod_editare = false;
$mesaj = '';

// Adaugare/modificare clase
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nume_clasa = trim($_POST['nume_clasa']);
    $descriere = trim($_POST['descriere']);
    $id_antrenor = (!empty($_POST['id_antrenor'])) ? $_POST['id_antrenor'] : NULL;
    $id_hidden = $_POST['id'] ?? 0;

    if (empty($nume_clasa)) {
        $mesaj = '<p style="color:red;">Numele clasei este obligatoriu.</p>';
    } else {
        try {
            if (empty($id_hidden)) {
                $sql = "INSERT INTO Clase (nume_clasa, descriere, id_antrenor) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nume_clasa, $descriere, $id_antrenor]);
                $mesaj = '<p style="color:green;">Clasă adăugată cu succes!</p>';
            } else {
                $sql = "UPDATE Clase SET nume_clasa = ?, descriere = ?, id_antrenor = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nume_clasa, $descriere, $id_antrenor, $id_hidden]);
                $mesaj = '<p style="color:green;">Clasă modificată cu succes!</p>';
            }
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare: ' . $e->getMessage() . '</p>';
        }
    }
}

// stergere
if (isset($_GET['actiune'])) {
    $actiune = $_GET['actiune'];
    $id = $_GET['id'] ?? 0;

    if ($actiune == 'sterge' && $id > 0) {
        try {
            $sql = "DELETE FROM Clase WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            header('Location: admin_clase.php?mesaj_succes=Sters!');
            exit;
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare la ștergere (posibil clasa este folosită în orar): ' . $e->getMessage() . '</p>';
        }
    }
    
    if ($actiune == 'modifica' && $id > 0) {
        $sql = "SELECT * FROM Clase WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $clasa_curenta = $stmt->fetch();
        
        if ($clasa_curenta) {
            $id_editare = $clasa_curenta['id'];
            $nume_edit = $clasa_curenta['nume_clasa'];
            $descriere_edit = $clasa_curenta['descriere'];
            $id_antrenor_edit = $clasa_curenta['id_antrenor'];
            $mod_editare = true;
        }
    }
}
// incarcare antrenori
try {
    $stmt_antrenori = $pdo->query("SELECT id, nume FROM Utilizatori WHERE rol = 'antrenor' ORDER BY nume ASC");
    $antrenori = $stmt_antrenori->fetchAll();
} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la încărcarea antrenorilor: ' . $e->getMessage() . '</p>';
    $antrenori = [];
}

include 'header.php';
echo '<div style="margin-bottom: 20px;"><a href="profil.php">&larr; Înapoi la Profil</a></div>';
?>

<h2>Management Clase (Admin)</h2>

<?php echo $mesaj; ?>
<?php if (isset($_GET['mesaj_succes'])) echo '<p style="color:green;">Operațiune reușită!</p>'; ?>


<h3><?php echo $mod_editare ? 'Modifică Clasă' : 'Adaugă Clasă Nouă'; ?></h3>
<form action="admin_clase.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id_editare; ?>">
    
    <div>
        <label for="nume_clasa">Nume Clasă:</label><br>
        <input type="text" id="nume_clasa" name="nume_clasa" value="<?php echo htmlspecialchars($nume_edit); ?>" required>
    </div>
    <br>
    <div>
        <label for="descriere">Descriere (opțional):</label><br>
        <textarea id="descriere" name="descriere"><?php echo htmlspecialchars($descriere_edit); ?></textarea>
    </div>
    <br>
    <div>
        <label for="id_antrenor">Antrenor (opțional):</label><br>
        <select id="id_antrenor" name="id_antrenor">
            <option value="0">-- Fără antrenor --</option>
            <?php foreach ($antrenori as $antrenor): ?>
                <option value="<?php echo $antrenor['id']; ?>" <?php echo ($id_antrenor_edit == $antrenor['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($antrenor['nume']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($antrenori)): ?>
            <p style="color:darkorange;"><i>Notă: Nu ai definit niciun antrenor. Mergi în phpMyAdmin și schimbă rolul unui utilizator în 'antrenor'.</i></p>
        <?php endif; ?>
    </div>
    <br>
    <div>
        <button type="submit"><?php echo $mod_editare ? 'Salvează Modificările' : 'Adaugă Clasă'; ?></button>
        <?php if ($mod_editare): ?>
            <a href="admin_clase.php">Anulează Modificarea</a>
        <?php endif; ?>
    </div>
</form>

<hr>

<h3>Listă Clase Existente</h3>
<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nume Clasă</th>
            <th>Descriere</th>
            <th>Antrenor</th>
            <th>Acțiuni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // luam numele antrenorului din tabela Utilizatori
        $sql_select = "SELECT C.*, U.nume AS nume_antrenor 
                       FROM Clase C
                       LEFT JOIN Utilizatori U ON C.id_antrenor = U.id
                       ORDER BY C.nume_clasa ASC";
        
        try {
            $stmt = $pdo->query($sql_select);
            $clase = $stmt->fetchAll();
            
            if (count($clase) == 0) {
                echo '<tr><td colspan="5">Nu există clase definite.</td></tr>';
            }
            
            foreach ($clase as $clasa) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($clasa['id']) . '</td>';
                echo '<td>' . htmlspecialchars($clasa['nume_clasa']) . '</td>';
                echo '<td>' . htmlspecialchars($clasa['descriere']) . '</td>';
                echo '<td>' . htmlspecialchars($clasa['nume_antrenor'] ?? 'N/A') . '</td>';
                echo '<td>';
                echo '<a href="admin_clase.php?actiune=modifica&id=' . $clasa['id'] . '">Modifică</a>';
                echo ' | ';
                echo '<a href="admin_clase.php?actiune=sterge&id=' . $clasa['id'] . '" 
                       onclick="return confirm(\'Ești sigur că vrei să ștergi această clasă?\');">Șterge</a>';
                echo '</td>';
                echo '</tr>';
            }
        } catch (PDOException $e) {
            echo '<tr><td colspan="5">Eroare la afișare: ' . $e->getMessage() . '</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';

?>
