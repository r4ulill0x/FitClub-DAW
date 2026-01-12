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
$id_clasa_edit = 0;
$zi_edit = '';
$ora_inceput_edit = '';
$ora_sfarsit_edit = '';
$locuri_edit = 10; 
$mod_editare = false;
$mesaj = '';

$zile_saptamana = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];

// Adaugare/modificare orar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_clasa = $_POST['id_clasa'];
    $zi_saptamana = $_POST['zi_saptamana'];
    $ora_inceput = $_POST['ora_inceput'];
    $ora_sfarsit = $_POST['ora_sfarsit'];
    $locuri_disponibile = filter_var($_POST['locuri_disponibile'], FILTER_VALIDATE_INT);
    $id_hidden = $_POST['id'] ?? 0;

    if (empty($id_clasa) || empty($zi_saptamana) || empty($ora_inceput) || empty($ora_sfarsit) || $locuri_disponibile === false) {
        $mesaj = '<p style="color:red;">Toate câmpurile sunt obligatorii.</p>';
    } elseif ($ora_inceput >= $ora_sfarsit) {
        $mesaj = '<p style="color:red;">Ora de început trebuie să fie înaintea orei de sfârșit.</p>';
    } else {
        try {
            if (empty($id_hidden)) {
                $sql = "INSERT INTO Program_Clase (id_clasa, zi_saptamana, ora_inceput, ora_sfarsit, locuri_disponibile) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_clasa, $zi_saptamana, $ora_inceput, $ora_sfarsit, $locuri_disponibile]);
                $mesaj = '<p style="color:green;">Programare adăugată cu succes!</p>';
            } else {
                $sql = "UPDATE Program_Clase SET id_clasa = ?, zi_saptamana = ?, ora_inceput = ?, ora_sfarsit = ?, locuri_disponibile = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_clasa, $zi_saptamana, $ora_inceput, $ora_sfarsit, $locuri_disponibile, $id_hidden]);
                $mesaj = '<p style="color:green;">Programare modificată cu succes!</p>';
            }
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare: ' . $e->getMessage() . '</p>';
        }
    }
}

// stergere orar
if (isset($_GET['actiune'])) {
    $actiune = $_GET['actiune'];
    $id = $_GET['id'] ?? 0;

    if ($actiune == 'sterge' && $id > 0) {
        try {
            $sql = "DELETE FROM Program_Clase WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            header('Location: admin_orar.php?mesaj_succes=Sters!');
            exit;
        } catch (PDOException $e) {
            $mesaj = '<p style="color:red;">Eroare la ștergere (posibil există rezervări): ' . $e->getMessage() . '</p>';
        }
    }
    
    if ($actiune == 'modifica' && $id > 0) {
        $sql = "SELECT * FROM Program_Clase WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $program_curent = $stmt->fetch();
        
        if ($program_curent) {
            $id_editare = $program_curent['id'];
            $id_clasa_edit = $program_curent['id_clasa'];
            $zi_edit = $program_curent['zi_saptamana'];
            $ora_inceput_edit = $program_curent['ora_inceput'];
            $ora_sfarsit_edit = $program_curent['ora_sfarsit'];
            $locuri_edit = $program_curent['locuri_disponibile'];
            $mod_editare = true;
        }
    }
}

// luam numarul de rezervari pentru a calcula locuriramase
$total_rezervari_clasa = []; 
try {
    $sql_count = "SELECT id_program_clasa, COUNT(*) AS numar_rezervari FROM Rezervari GROUP BY id_program_clasa";
    $stmt_count = $pdo->query($sql_count);
    $count_raw = $stmt_count->fetchAll();
    foreach ($count_raw as $count) {
        $total_rezervari_clasa[$count['id_program_clasa']] = $count['numar_rezervari'];
    }
} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la calcularea locurilor rezervate: ' . $e->getMessage() . '</p>';
}

// luam lista de clase pt dropdown
try {
    $stmt_clase = $pdo->query("SELECT id, nume_clasa FROM Clase ORDER BY nume_clasa ASC");
    $clase_disponibile = $stmt_clase->fetchAll();
} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la încărcarea claselor: ' . $e->getMessage() . '</p>';
    $clase_disponibile = [];
}

include 'header.php';
echo '<div style="margin-bottom: 20px;"><a href="profil.php">&larr; Înapoi la Profil</a></div>';
?>

<h2>Management Orar (Admin)</h2>

<?php echo $mesaj; ?>
<?php if (isset($_GET['mesaj_succes'])) echo '<p style="color:green;">Operațiune reușită!</p>'; ?>


<h3><?php echo $mod_editare ? 'Modifică Programare' : 'Adaugă Programare Nouă'; ?></h3>

<?php if (empty($clase_disponibile)): ?>
    <p style="color:red; font-weight:bold;">Atenție! Nu poți adăuga un orar dacă nu ai definit nicio clasă. Mergi la <a href="admin_clase.php">Management Clase</a> întâi.</p>
<?php else: ?>

<form action="admin_orar.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id_editare; ?>">
    
    <div>
        <label for="id_clasa">Clasa:</label><br>
        <select id="id_clasa" name="id_clasa" required>
            <option value="">-- Alege o clasă --</option>
            <?php foreach ($clase_disponibile as $clasa): ?>
                <option value="<?php echo $clasa['id']; ?>" <?php echo ($id_clasa_edit == $clasa['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($clasa['nume_clasa']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="zi_saptamana">Ziua Săptămânii:</label><br>
        <select id="zi_saptamana" name="zi_saptamana" required>
            <option value="">-- Alege o zi --</option>
            <?php foreach ($zile_saptamana as $zi): ?>
                 <option value="<?php echo $zi; ?>" <?php echo ($zi_edit == $zi) ? 'selected' : ''; ?>>
                    <?php echo $zi; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <label for="ora_inceput">Ora Început:</label><br>
        <input type="time" id="ora_inceput" name="ora_inceput" value="<?php echo htmlspecialchars($ora_inceput_edit); ?>" required>
    </div>
    <br>
    <div>
        <label for="ora_sfarsit">Ora Sfârșit:</label><br>
        <input type="time" id="ora_sfarsit" name="ora_sfarsit" value="<?php echo htmlspecialchars($ora_sfarsit_edit); ?>" required>
    </div>
    <br>
    <div>
        <label for="locuri_disponibile">Locuri Disponibile (Total):</label><br>
        <input type="number" id="locuri_disponibile" name="locuri_disponibile" value="<?php echo htmlspecialchars($locuri_edit); ?>" min="1" required>
    </div>
    <br>
    <div>
        <button type="submit"><?php echo $mod_editare ? 'Salvează Modificările' : 'Adaugă Programare'; ?></button>
        <?php if ($mod_editare): ?>
            <a href="admin_orar.php">Anulează Modificarea</a>
        <?php endif; ?>
    </div>
</form>
<?php endif; ?>

<hr>

<h3>Orar Curent</h3>
<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th>Clasa</th>
            <th>Antrenor</th>
            <th>Ziua</th>
            <th>Ora Început</th>
            <th>Ora Sfârșit</th>
            <th>Locuri Rămase / Total</th> <th>Acțiuni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_select = "SELECT P.*, C.nume_clasa, U.nume AS nume_antrenor 
                       FROM Program_Clase P
                       JOIN Clase C ON P.id_clasa = C.id
                       LEFT JOIN Utilizatori U ON C.id_antrenor = U.id
                       ORDER BY FIELD(P.zi_saptamana, 'Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'), P.ora_inceput ASC";
        
        try {
            $stmt = $pdo->query($sql_select);
            $orar = $stmt->fetchAll();
            
            if (count($orar) == 0) {
                echo '<tr><td colspan="7">Nu există nicio programare în orar.</td></tr>';
            }
            
            foreach ($orar as $program) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($program['nume_clasa']) . '</td>';
                echo '<td>' . htmlspecialchars($program['nume_antrenor'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($program['zi_saptamana']) . '</td>';
                echo '<td>' . date('H:i', strtotime($program['ora_inceput'])) . '</td>';
                echo '<td>' . date('H:i', strtotime($program['ora_sfarsit'])) . '</td>';
                
                $locuri_totale = $program['locuri_disponibile'];
                $rezervari_existente = $total_rezervari_clasa[$program['id']] ?? 0;
                $locuri_ramase = $locuri_totale - $rezervari_existente;
            
                echo '<td>' . htmlspecialchars($locuri_ramase) . ' / ' . htmlspecialchars($locuri_totale) . '</td>';

                echo '<td>';
                echo '<a href="admin_orar.php?actiune=modifica&id=' . $program['id'] . '">Modifică</a>';
                echo ' | ';
                echo '<a href="admin_orar.php?actiune=sterge&id=' . $program['id'] . '" 
                       onclick="return confirm(\'Ești sigur că vrei să ștergi această programare?\');">Șterge</a>';
                echo '</td>';
                echo '</tr>';
            }
        } catch (PDOException $e) {
            echo '<tr><td colspan="7">Eroare la afișare: ' . $e->getMessage() . '</td></tr>';
        }
        ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>