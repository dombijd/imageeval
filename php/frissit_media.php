<?php
// Az adatbázis kapcsolat beállítása
require_once "db_connect.php";

// Projekt ID lekérdezése
$projekt_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($projekt_id === null) {
    die("Hiba: Nincs megadva projekt ID!");
}

// Képek és videók lekérdezése
$query = "SELECT * FROM fajlok WHERE projekt_id = ? ORDER BY id ASC LIMIT 5"; // Az első 5 fájl lekérdezése
$stmt = $conn->prepare($query); // Helyes változó használata
$stmt->bind_param("i", $projekt_id);
$stmt->execute();
$resultMedia = $stmt->get_result();
?>

<div class="media-preview">
    <?php
    $mediaCount = 0; // Számláló a médiafájlok számára
    while ($media = $resultMedia->fetch_assoc()):
        if ($mediaCount < 5): // Csak az első 5 fájl megjelenítése
            ?>
            <div class="media-item">
                <?php
                $fileName = $media['fajl_nev'];
                // Képek kezelése
                if (strpos($fileName, '.jpg') !== false || strpos($fileName, '.png') !== false): ?>
                    <img src="../feltoltesek/<?php echo htmlspecialchars($fileName); ?>"
                        alt="<?php echo htmlspecialchars($fileName); ?>">
                <?php
                    // Videók kezelése
                elseif (strpos($fileName, '.mp4') !== false || strpos($fileName, '.webm') !== false): ?>
                    <video controls>
                        <source src="../feltoltesek/<?php echo htmlspecialchars($fileName); ?>"
                            type="video/<?php echo pathinfo($fileName, PATHINFO_EXTENSION); ?>">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <p><?php echo htmlspecialchars($fileName); ?></p>
                <?php endif; ?>
            </div>
        <?php
        endif;
        $mediaCount++;
    endwhile;
    ?>
</div>

<?php
// Adatbázis kapcsolat lezárása
$stmt->close();
$conn->close();
?>