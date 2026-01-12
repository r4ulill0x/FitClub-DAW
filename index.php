<?php
include 'config.php'; 
include 'header.php';

// lista de antrenori
try {
    $interogare_antrenori = $pdo->query("SELECT id, nume FROM Utilizatori WHERE rol = 'antrenor' ORDER BY nume ASC");
    $antrenori = $interogare_antrenori->fetchAll();
} catch (PDOException $e) {
    $antrenori = []; 
}

// lista de abonamente 
try {
    $interogare_abonamente = $pdo->query("SELECT nume, pret, durata_zile, descriere FROM Abonamente ORDER BY pret ASC");
    $abonamente = $interogare_abonamente->fetchAll();
} catch (PDOException $e) {
    $abonamente = [];
}
?>

<div style="text-align: center; padding: 40px 20px;">
    <h1>Bun Venit la <span style="color:var(--accent-secundar);">FitClub</span></h1>
    <p style="font-size: 1.2rem; max-width: 800px; margin: 10px auto;">
        Sanctuarul tău digital pentru fitness. Aici, tehnologia se întâlnește cu performanța.
        Indiferent dacă ești la început sau un veteran al antrenamentelor, platforma noastră îți oferă
        instrumentele necesare pentru a-ți urmări progresul, a rezerva clase și a-ți depăși limitele.
    </p>
    <p style="max-width: 800px; margin: 10px auto;">
        La FitClub, credem într-un mediu curat, sigur și motivant. Echipamentele noastre de ultimă generație
        și antrenorii noștri certificați sunt gata să te ghideze în călătoria ta spre o versiune mai puternică a ta.
    </p>
    
    <div style="margin-top: 30px;">
            <a href="rezervari.php" class="cta-button" style="background-color: var(--accent-principal); color: var(--culoare-fundal); padding: 15px 30px; text-decoration: none; font-family: 'Orbitron', sans-serif; font-size: 1.2rem; text-transform: uppercase;">Rezervă o Clasă</a>
            <a href="register.php" class="cta-button" style="background-color: var(--accent-principal); color: var(--culoare-fundal); padding: 15px 30px; text-decoration: none; font-family: 'Orbitron', sans-serif; font-size: 1.2rem; text-transform: uppercase;">Alătură-te Acum</a>
    </div>
</div>

<hr style="border-color: var(--accent-principal); margin: 40px 0;">

<div>
    <h2>Antrenorii Noștri</h2>
    <?php if (empty($antrenori)): ?>
        <p>Echipa noastră de antrenori este în formare. Reveniți în curând!</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <?php foreach ($antrenori as $antrenor): ?>
                <div style="background-color: var(--culoare-dark-ui); border: 1px solid var(--accent-secundar); padding: 20px;">
                    <h3 style="color:var(--accent-secundar);"><?php echo htmlspecialchars($antrenor['nume']); ?></h3>
                    <p>
                        Un membru dedicat al echipei FitClub. Cu o pasiune pentru performanță și
                        o experiență vastă, <strong><?php echo htmlspecialchars(explode(' ', $antrenor['nume'])[0]); ?></strong> 
                        este specializat în a ajuta clienții să-și atingă obiectivele de fitness,
                        combinând tehnici moderne cu o atenție deosebită la detalii.
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<hr style="border-color: var(--accent-principal); margin: 40px 0;">

<div>
    <h2>Abonamente</h2>
    <p>Alege planul care ți se potrivește. Flexibilitate totală, acces digital.</p>
    
    <?php if (empty($abonamente)): ?>
        <p>Planurile noastre de abonament vor fi afișate în curând.</p>
    <?php else: ?>
        <table style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Tip Abonament</th>
                    <th>Durată</th>
                    <th>Descriere</th>
                    <th>Preț</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($abonamente as $abon): ?>
                    <tr>
                        <td style="color:var(--accent-principal); font-weight:bold;"><?php echo htmlspecialchars($abon['nume']); ?></td>
                        <td><?php echo htmlspecialchars($abon['durata_zile']); ?> zile</td>
                        <td><?php echo htmlspecialchars($abon['descriere'] ? $abon['descriere'] : '-'); ?></td>
                        <td style="font-size: 1.2rem;"><?php echo htmlspecialchars($abon['pret']); ?> RON</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
include 'footer.php'; 
?>