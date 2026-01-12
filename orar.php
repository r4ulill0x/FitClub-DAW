<?php
include 'config.php'; 
include 'header.php'; 

$zile_saptamana = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];
$orar_grupat = [];
foreach ($zile_saptamana as $zi) {
    $orar_grupat[$zi] = [];
}

$total_rezervari_clasa = []; 
try {
    $sql_count = "SELECT id_program_clasa, COUNT(*) AS numar_rezervari FROM Rezervari GROUP BY id_program_clasa";
    $stmt_count = $pdo->query($sql_count);
    $count_raw = $stmt_count->fetchAll();
    foreach ($count_raw as $count) {
        $total_rezervari_clasa[$count['id_program_clasa']] = $count['numar_rezervari'];
    }
} catch (PDOException $e) {
    echo '<p style="color:red;">Eroare la calcularea locurilor: ' . $e->getMessage() . '</p>';
}


// extragere orar din db
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
    echo '<p style="color:red;">Eroare la încărcarea orarului: ' . $e->getMessage() . '</p>';
    $programari = []; 
}
?>

<h2>Orarul Săptămânal al Claselor</h2>

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
                        <th style="width: 20%;">Interval Orar</th>
                        <th style="width: 30%;">Clasa</th>
                        <th style="width: 30%;">Antrenor</th>
                        <th style="width: 20%;">Locuri Rămase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clase_zi as $clasa): ?>
                        <tr>
                            <td>
                                <?php 
                                echo date('H:i', strtotime($clasa['ora_inceput'])) . ' - ' . date('H:i', strtotime($clasa['ora_sfarsit'])); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($clasa['nume_clasa']); ?></td>
                            <td><?php echo htmlspecialchars($clasa['nume_antrenor'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                // calculare locuri ramase
                                $rezervari_existente = $total_rezervari_clasa[$clasa['id']] ?? 0;
                                $locuri_ramase = $clasa['locuri_disponibile'] - $rezervari_existente;
                                
                                echo htmlspecialchars($locuri_ramase) . ' / ' . htmlspecialchars($clasa['locuri_disponibile']);
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