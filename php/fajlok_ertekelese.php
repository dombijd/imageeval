<?php
session_start();

// Projekt ID ellenőrzése
$projekt_id = isset($_GET['projekt_id']) ? intval($_GET['projekt_id']) : null;
if ($projekt_id === null) {
    echo "Nincs projekt kiválasztva.";
    exit();
}

// Kitöltő azonosító ellenőrzése
$kitolto_id = isset($_SESSION['kitolto_id_' . $projekt_id]) ? $_SESSION['kitolto_id_' . $projekt_id] : null;
if ($kitolto_id === null) {
    echo "Nincs kitöltő azonosító.";
    exit();
}

// Adatbázis kapcsolat
require_once "db_connect.php";

// Fájlok betöltése session-be, ha még nincs
if (!isset($_SESSION['files_images_' . $projekt_id]) || !isset($_SESSION['files_videos_' . $projekt_id])) {
    // Képek lekérése
    $sql_images = "SELECT * FROM fajlok WHERE projekt_id = ? AND tipus = 'kep' ORDER BY ertekelesek_szama ASC, RAND() LIMIT 20";
    $stmt = $conn->prepare($sql_images);
    $stmt->bind_param("i", $projekt_id);
    $stmt->execute();
    $result_images = $stmt->get_result();
    $_SESSION['files_images_' . $projekt_id] = $result_images->fetch_all(MYSQLI_ASSOC);

    // Videók lekérése
    $sql_videos = "SELECT * FROM fajlok WHERE projekt_id = ? AND tipus = 'video' ORDER BY ertekelesek_szama ASC, RAND() LIMIT 20";
    $stmt = $conn->prepare($sql_videos);
    $stmt->bind_param("i", $projekt_id);
    $stmt->execute();
    $result_videos = $stmt->get_result();
    $_SESSION['files_videos_' . $projekt_id] = $result_videos->fetch_all(MYSQLI_ASSOC);
}

// Fájlok és teljes számuk
$files_images = $_SESSION['files_images_' . $projekt_id];
$files_videos = $_SESSION['files_videos_' . $projekt_id];
$total_files = count($files_images) + count($files_videos);

// Aktuális fájl index
$current_file = isset($_GET['current_file']) ? intval($_GET['current_file']) : 1;
if ($current_file > $total_files) {
    echo "Mindent értékeltél!";
    unset($_SESSION['files_' . $projekt_id], $_SESSION['pontozasok_' . $projekt_id]);
    exit();
}

// Aktuális fájl adatainak lekérése
if ($current_file <= count($files_images)) {
    $rowFajl = $files_images[$current_file - 1]; // 1-alapú index miatt -1
} else {
    $rowFajl = $files_videos[$current_file - count($files_images) - 1]; // Videó fájlok
}

// Pontozás mentése POST kérés esetén
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pontszam'])) {
    $pontszam = intval($_POST['pontszam']);
    $fajl_id = intval($_POST['fajl_id']);

    // Ellenőrzés, hogy a fájl ID helyes-e
    if ($fajl_id <= 0) {
        echo "Hibás fájl azonosító!";
        exit();
    }

    if (!isset($_SESSION['pontozasok_' . $projekt_id])) {
        $_SESSION['pontozasok_' . $projekt_id] = [];
    }

    $_SESSION['pontozasok_' . $projekt_id][] = [
        'fajl_id' => $fajl_id,
        'pontszam' => $pontszam
    ];

    // Értékelések számának frissítése minden fájlnál
    $update_fajl = $conn->prepare("UPDATE fajlok SET ertekelesek_szama = ertekelesek_szama + 1 WHERE id = ?");
    $update_fajl->bind_param("i", $fajl_id);
    $update_fajl->execute();
    $update_fajl->close();

    // Ha az utolsó fájlt értékeljük, mentjük az értékeléseket az adatbázisba
    if ($current_file == $total_files) {

        // Mentjük a válaszokat az adatbázisba
        if (isset($_SESSION['valaszok_' . $projekt_id])) {
            $valaszok = $_SESSION['valaszok_' . $projekt_id];

            // SQL beszúrás a válaszok táblába
            $stmt = $conn->prepare("INSERT INTO kerdesekre_valasz (projekt_id, kerdesek_id, valasz, kitolto_id) VALUES (?, ?, ?, ?)");
            foreach ($valaszok as $kerdes_id => $valasz) {
                $stmt->bind_param("iisi", $projekt_id, $kerdes_id, $valasz, $kitolto_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Töröljük a session adatokat
        unset($_SESSION['valaszok_' . $projekt_id]);


        $pontozasok = $_SESSION['pontozasok_' . $projekt_id];
        $stmt = $conn->prepare("INSERT INTO ertekelt_fajlok (kitolto_id, fajl_id, projekt_id, pontszam) VALUES (?, ?, ?, ?)");

        foreach ($pontozasok as $pontozas) {
            // Ellenőrzés, hogy a pontozás valóban különböző fájlokhoz tartozik
            if ($pontozas['fajl_id'] <= 0 || !in_array($pontozas['fajl_id'], array_column($files_images, 'id')) && !in_array($pontozas['fajl_id'], array_column($files_videos, 'id'))) {
                echo "Hibás fájl azonosító a pontozás során!";
                exit();
            }
            $stmt->bind_param("iiii", $kitolto_id, $pontozas['fajl_id'], $projekt_id, $pontozas['pontszam']);
            $stmt->execute();
        }
        $stmt->close();

        // Kitöltések frissítése
        $update_stmt = $conn->prepare("UPDATE projektek SET eddigi_kitoltesek = eddigi_kitoltesek + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $projekt_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Session törlése
        unset($_SESSION['pontozasok_' . $projekt_id], $_SESSION['files_images_' . $projekt_id], $_SESSION['files_videos_' . $projekt_id]);


    }

    // Számoljuk ki a fájlok felét
$felso_hatar = ceil($total_files / 2);

// Ha a fájlok száma legalább 10, és épp a felénél vagyunk, akkor jelenítsük meg az üzenetet
if ($total_files >= 10 && $current_file == $felso_hatar) {
    echo '<!DOCTYPE html>
    <html lang="hu">
    <head>
        <meta charset="UTF-8">
        <title>Félúton jársz</title>
        <link rel="stylesheet" href="../css2/ertekeles_fajlok.css?v=1.1">
        <style>
            #tovabbi-ertekeles {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-size: 1.2em;
                margin-top: 50px;
                text-align: center;
            }
            #tovabbi-ertekeles p {
                font-weight: bold;
                margin-bottom: 20px;
            }
            #tovabbi-ertekeles .button-container {
                display: flex;
                gap: 20px;
                justify-content: center;
            }
            #tovabbi-ertekeles .button {
                background-color: #007BFF;
                color: white;
                padding: 12px 20px;
                font-size: 1.1em;
                text-decoration: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            #tovabbi-ertekeles .button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>Köszönöm, hogy értékelésével segíti a szakdolgozatomat</h1>
        </header>
        <div id="tovabbi-ertekeles">
            <p>A fájlok felénél jársz, tarts ki!</p>
            <img src="../oldalra_kepek/thank you memes.jpg" alt="Motiváló üzenet" style="max-width: 400px; height: auto; padding: 20px; margin-bottom:20px;">
            <div class="button-container">
                <a href="fajlok_ertekelese.php?projekt_id=' . $projekt_id . '&current_file=' . ($current_file + 1) . '" class="button">Tovább a következő fájlhoz</a>
            </div>
        </div>
    </body>
    </html>';
    exit(); 
}

    // Következő fájlra lépés
    header("Location: fajlok_ertekelese.php?projekt_id=$projekt_id&current_file=" . ($current_file + 1));
    if ($current_file == $total_files) {
        header("Location: topharomvalasztas.php?projekt_id=" . $projekt_id);
        exit();
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>Értékelés - Projektértékelő</title>
    <link rel="stylesheet" href="../css2/ertekeles_fajlok.css?v=1.4">
</head>

<body>
    <header>
        <h1>Köszönöm, hogy értékelésével segíti a szakdolgozatomat</h1>
    </header>
    <div class="container">
        <p>Kérjük, értékelje a fájlt!</p> <strong>1-es a legrosszabb értékelés, 5-ös a legjobb értékelés.</strong> 
        <h3><?php echo "$current_file / $total_files"; ?></h3>

        <?php
        // Ellenőrzés, hogy a fájl videó-e
        $file_extension = pathinfo($rowFajl['fajl_nev'], PATHINFO_EXTENSION);
        $video_extensions = ['mp4', 'webm', 'ogg']; // Támogatott videó kiterjesztések
        
        if (in_array(strtolower($file_extension), $video_extensions)) {
            // Ha videó, akkor <video> tag
            echo '<video width="600" controls>
                <source src="../feltoltesek/' . htmlspecialchars($rowFajl['fajl_nev']) . '" type="video/' . $file_extension . '">
                Your browser does not support the video tag.
              </video>';
        } else {
            // Ha kép, akkor <img> tag
            echo '<img src="../feltoltesek/' . htmlspecialchars($rowFajl['fajl_nev']) . '" alt="Fájl kép" width="600">';
        }
        ?>

        <form method="post">
            <input type="hidden" name="fajl_id" value="<?php echo $rowFajl['id']; ?>">
            <input type="hidden" name="pontszam" id="pontszam-hidden" value="" />
            <div class="pontozas-container">
                <?php for ($i = 1; $i <= 5; $i++): ?>   <!-- A pontozás gombok megjelenítése 1-től 5-ig -->
                    <button type="button" class="pontozas-kor"
                        onclick="selectRating(<?php echo $i; ?>)"><?php echo $i; ?></button>
                <?php endfor; ?>
            </div>
            <button type="submit" id="tovabb-gomb">Tovább</button> <!-- Tovább gomb, csak akkor jelenik meg, ha ki van választva egy pontszámot -->
        </form>
    </div>
    <script>
        function selectRating(rating) {
            document.getElementById('pontszam-hidden').value = rating; // A kiválasztott pontszám értékének beállítása a rejtett input mezőben
            document.querySelectorAll('.pontozas-kor').forEach(circle => circle.classList.remove('selected')); // Az összes pontozás gomb állapotának resetelése
            document.querySelector('.pontozas-kor:nth-child(' + rating + ')').classList.add('selected'); // A kiválasztott gomb kiemelése
            document.getElementById('tovabb-gomb').style.display = 'block'; // A Tovább gomb megjelenítése, ha pontszámot választottak
        }
    </script>
</body>

</html>