<?php
include 'config.php'; 

// verificare logare pentru a rezerva
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?eroare=Trebuie sa fii logat pentru a face rezervari.');
    exit;
}

$id_client_curent = $_SESSION['user_id'];
$mesaj = '';

// creare rezervare
if (isset($_GET['actiune'])) {
    $actiune = $_GET['actiune'];
    $id_program_clasa = $_GET['id_program'] ?? 0;
    
    try {
        if ($actiune == 'rezerva' && $id_program_clasa > 0) {
            
            $sql_check = "SELECT id FROM Rezervari WHERE id_client = ? AND id_program_clasa = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$id_client_curent, $id_program_clasa]);
            
            if ($stmt_check->fetch()) {
                $mesaj = '<p style="color:darkorange;">Ai rezervat deja un loc la această clasă!</p>';
            } else {
                
                $sql_insert = "INSERT INTO Rezervari (id_client, id_program_clasa, status) VALUES (?, ?, 'confirmata')";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([$id_client_curent, $id_program_clasa]);
                $mesaj = '<p style="color:green;">Rezervare efectuată cu succes!</p>';
            }

        } elseif ($actiune == 'anuleaza' && $id_program_clasa > 0) {
            // stergere rezervare
            
            $sql_delete = "DELETE FROM Rezervari WHERE id_client = ? AND id_program_clasa = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$id_client_curent, $id_program_clasa]);
            
            if ($stmt_delete->rowCount() > 0) {
                $mesaj = '<p style="color:green;">Rezervare anulată cu succes!</p>';
            } else {
                $mesaj = '<p style="color:red;">Nu am putut anula rezervarea.</p>';
            }
        }
    } catch (PDOException $e) {
        // eroare de dublu-click rapid
        if ($e->errorInfo[1] == 1062) {
            $mesaj = '<p style="color:darkorange;">Ai rezervat deja un loc la această clasă!</p>';
        } else {
            $mesaj = '<p style="color:red;">Eroare: ' . $e->getMessage() . '</p>';
        }
    }
}



// preluare rezervari user logat
$rezervarile_mele = []; 
try {
    $sql_mele = "SELECT id_program_clasa FROM Rezervari WHERE id_client = ?";
    $stmt_mele = $pdo->prepare($sql_mele);
    $stmt_mele->execute([$id_client_curent]);
    $rez_mele_raw = $stmt_mele->fetchAll();
    foreach ($rez_mele_raw as $rez) {
        $rezervarile_mele[$rez['id_program_clasa']] = true;
    }
} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la citirea rezervărilor: ' . $e->getMessage() . '</p>';
}

// preluare rezervari user logat pentru fiecare clasa
$total_rezervari_clasa = []; 
try {
    $sql_count = "SELECT id_program_clasa, COUNT(*) AS numar_rezervari FROM Rezervari GROUP BY id_program_clasa";
    $stmt_count = $pdo->query($sql_count);
    $count_raw = $stmt_count->fetchAll();
    foreach ($count_raw as $count) {
        $total_rezervari_clasa[$count['id_program_clasa']] = $count['numar_rezervari'];
    }
} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la calcularea locurilor: ' . $e->getMessage() . '</p>';
}


// preluare orar
$zile_saptamana = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];
$orar_grupat = [];
foreach ($zile_saptamana as $zi) {
    $orar_grupat[$zi] = [];
}

try {
    $sql_select = "SELECT P.*, C.nume_clasa, U.nume AS nume_antrenor 
                   FROM Program_Clase P
                   JOIN Clase C ON P.id_clasa = C.id
                   LEFT JOIN Utilizatori U ON C.id_antrenor = U.id
                   ORDER BY FIELD(P.zi_saptamana, 'Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'), P.ora_inceput ASC";
                   
    $stmt = $pdo->query($sql_select);
    $programari = $stmt->fetchAll();
    
    foreach ($programari as $program) {
        $orar_grupat[$program['zi_saptamana']][] = $program;
    }

} catch (PDOException $e) {
    $mesaj .= '<p style="color:red;">Eroare la încărcarea orarului: ' . $e->getMessage() . '</p>';
    $programari = []; 
}

include 'header.php';
?>

<h2>Rezervă un Loc la Clase</h2>

<?php echo $mesaj; ?>

<?php if (empty($programari)): ?>
    <p>Momentan nu este stabilit niciun orar. Vă rugăm reveniți mai târziu.</p>
<?php else: ?>
    
    <?php
    foreach ($orar_grupat as $zi => $clase_zi):
        if (!empty($clase_zi)):
    ?>
            <h3><?php echo htmlspecialchars($zi); ?></h3>
            
            <table style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th>Interval Orar</th>
                        <th>Clasa</th>
                        <th>Antrenor</th>
                        <th>Locuri Rămase</th>
                        <th>Acțiune</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clase_zi as $clasa): ?>
                        <tr>
                            <td>
                                <?php echo date('H:i', strtotime($clasa['ora_inceput'])) . ' - ' . date('H:i', strtotime($clasa['ora_sfarsit'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($clasa['nume_clasa']); ?></td>
                            <td><?php echo htmlspecialchars($clasa['nume_antrenor'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                $rezervari_existente = $total_rezervari_clasa[$clasa['id']] ?? 0;
                                $locuri_ramase = $clasa['locuri_disponibile'] - $rezervari_existente;
                                echo htmlspecialchars($locuri_ramase) . ' / ' . htmlspecialchars($clasa['locuri_disponibile']);
                                ?>
                            </td>
                            <td>
                                <?php
                                if (isset($rezervarile_mele[$clasa['id']])) {
                                    echo '<a href="rezervari.php?actiune=anuleaza&id_program=' . $clasa['id'] . '" style="color:red;">Anulează Rezervarea</a>';
                                } else {
                                    if ($locuri_ramase > 0) {
                                        echo '<a href="rezervari.php?actiune=rezerva&id_program=' . $clasa['id'] . '" style="color:green;">Rezervă Loc</a>';
                                    } else {
                                        echo 'Locuri Epuizate';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
    <?php
        endif; 
    endforeach; 
    ?>

<?php endif; ?>

<?php
include 'footer.php'; 
?>