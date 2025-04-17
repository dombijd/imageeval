<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekt Részletek - Projektértékelő</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.1">
    <link rel="stylesheet" href="../css2/nyilvanos_reszletek.css?v=1.5">
    <style>

    </style>
</head>

<body>

    <header>
        <h1>Projektértékelő</h1>
        <div class="auth-links">
            <a href="../php/regisztracio.php">Regisztráció</a>
            <a href="../php/bejelentkezes.php">Bejelentkezés</a>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="../html/kezdolap.html">Kezdőlap</a></li>
            <li><a href="../php/projektek.php">Projektek</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php
        require_once "db_connect.php";

        // Ellenőrizzük, hogy van-e projekt_id
        if (isset($_GET['projekt_id'])) {
            $projekt_id = intval($_GET['projekt_id']);

            // Lekérdezés a kiválasztott projektre
            $sql = "SELECT * FROM projektek WHERE id = $projekt_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Projekt részletei
                echo '<h2>Az adott projekt részletei:</h2>';
                echo '<p><strong>A projekt neve:</strong> ' . htmlspecialchars($row['nev']) . '</p>';
                echo '<p><strong>A projekt leírása:</strong> ' . nl2br(htmlspecialchars($row['leiras'])) . '</p>'; // nl2br a sorok megtartásához
        
                // Képek számának lekérdezése
                $sql_images = "SELECT COUNT(*) as image_count FROM fajlok WHERE projekt_id = $projekt_id";
                $result_images = $conn->query($sql_images);
                $image_count = 0;

                if ($result_images->num_rows > 0) {
                    $image_row = $result_images->fetch_assoc();
                    $image_count = $image_row['image_count'];
                }

                // Idő szükséglete a képekhez
                $total_time = min($image_count / 2, 10); // Maximum 20 fájlhoz számítunk 20 percet
                echo '<p><strong>Értékelendő fájlok száma:</strong> ' . ($image_count > 20 ? 20 : $image_count) . '</p>';
                echo '<p><strong>Kb ennyi időt vesz igénybe:</strong> ' . $total_time . ' perc</p>';

                // Eddigi kitöltések számának lekérdezése
                $eddigi_kitoltesek = htmlspecialchars($row['eddigi_kitoltesek']); // Itt módosítva
                echo '<p><strong>Eddigi kitöltések száma:</strong> ' . $eddigi_kitoltesek . '</p>'; // Közvetlenül kiírva
        
                // Gomb a kitöltéshez
                echo '<a class="button" href="ertekeles_kitoltok.php?projekt_id=' . urlencode($row['id']) . '">Kitöltés</a>';
            } else {
                echo "<p>Nincs ilyen projekt.</p>";
            }
        } else {
            echo "<p>Nincs projekt kiválasztva.</p>";
        }

        // Kapcsolat lezárása
        $conn->close();
        ?>

        <!-- Vissza gomb -->
        <a class="button back-button" onclick="window.history.back()">Vissza</a>

    </div>

</body>

</html>