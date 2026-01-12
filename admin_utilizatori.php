<?php
include 'config.php'; 

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    include 'header.php';
    echo '<h2>🚫 Acces Interzis</h2>';
    echo '<p style="color:var(--culoare-eroare); font-weight:bold; font-family: \'Orbitron\', sans-serif;">Nu aveți permisiunea necesară pentru a accesa această resursă.</p>';
    include 'footer.php';
    exit;
}

$mesaj = '';

// stergere useri
if (isset($_GET['actiune']) && $_GET['actiune'] == 'sterge') {
    $id_de_sters = $_GET['id'] ?? 0;
    
    // adminul nu se poate sterge
    if ($id_de_sters == $_SESSION['user_id']) {
        $mesaj = '<p style="color:var(--culoare-eroare);">Eroare: Nu îți poți șterge propriul cont de administrator!</p>';
    } elseif ($id_de_sters > 0) {
        try {
            $sql = "DELETE FROM Utilizatori WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_de_sters]);
            
            if ($stmt->rowCount() > 0) {
                $mesaj = '<p style="color:var(--accent-principal);">Utilizatorul a fost șters cu succes!</p>';
            } else {
                $mesaj = '<p style="color:var(--culoare-eroare);">Eroare: Utilizatorul nu a fost găsit.</p>';
            }
        } catch (PDOException $e) {
            $mesaj = '<p style="color:var(--culoare-eroare);">Eroare la ștergere: ' . $e->getMessage() . '</p>';
        }
    }
}
//afisare useri
try {
    $stmt_users = $pdo->query("SELECT id, nume, email, rol, data_inregistrare FROM Utilizatori ORDER BY rol, nume ASC");
    $utilizatori = $stmt_users->fetchAll();
} catch (PDOException $e) {
    $utilizatori = [];
    $mesaj .= '<p style="color:var(--culoare-eroare);">Eroare la citirea utilizatorilor: ' . $e->getMessage() . '</p>';
}

include 'header.php';
?>

<div style="margin-bottom: 20px;"><a href="profil.php">&larr; Înapoi la Profil</a></div>

<h2>Management Utilizatori (Admin)</h2>
<p>Aici poți vedea și șterge utilizatori (clienți și antrenori).</p>

<?php echo $mesaj; ?>

<table style="width: 100%; margin-top: 20px;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nume</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Data Înreg.</th>
            <th>Acțiuni</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($utilizatori)): ?>
            <tr><td colspan="6">Nu există utilizatori în baza de date.</td></tr>
        <?php else: ?>
            <?php foreach ($utilizatori as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['nume']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php 
                        if ($user['rol'] == 'admin') {
                            echo '<strong style="color:var(--culoare-eroare);">' . htmlspecialchars($user['rol']) . '</strong>';
                        } elseif ($user['rol'] == 'antrenor') {
                            echo '<strong style="color:var(--accent-secundar);">' . htmlspecialchars($user['rol']) . '</strong>';
                        } else {
                            echo htmlspecialchars($user['rol']);
                        }
                        ?>
                    </td>
                    <td><?php echo date('d-m-Y', strtotime($user['data_inregistrare'])); ?></td>
                    <td>
                        <?php
                        if ($user['id'] == $_SESSION['user_id']):
                        ?>
                            (Acesta ești tu)
                        <?php else: ?>
                            <a href="admin_utilizatori.php?actiune=sterge&id=<?php echo $user['id']; ?>"
                               style="color:var(--culoare-eroare);"
                               onclick="return confirm('Ești absolut sigur că vrei să ștergi utilizatorul <?php echo htmlspecialchars(addslashes($user['nume'])); ?>? Această acțiune este ireversibilă.');">
                                Șterge
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php
include 'footer.php';
?>