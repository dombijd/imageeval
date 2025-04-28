<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projektek - Projektértékelő</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.2">
    <link rel="stylesheet" href="../css2/projektek.css?v=1.4">
    <style>

    </style>
</head>

<body>

    <header>
        <h1>Projektértékelő</h1>
        <div class="auth-links">
            <a href="regisztracio.php">Regisztráció</a>
            <a href="bejelentkezes.php">Bejelentkezés</a>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="../html/kezdolap.html">Kezdőlap</a></li>
            <li><a href="projektek.php">Projektek</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php
        require_once "db_connect.php";

        // Frissített lekérdezés, hogy az eddigi_kitoltesek mezőt is kiválassza
        $sql = "SELECT id, nev, leiras, fokep, eddigi_kitoltesek FROM projektek";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="project-box" id="project-box-small">';
                echo '<a href="nyilvanos_reszletek.php?projekt_id=' . urlencode($row['id']) . '">';
                echo '<img src="../feltoltesek/' . htmlspecialchars($row['fokep']) . '" alt="' . htmlspecialchars($row['nev']) . '">';

                // Projekt neve rövidítése, ha túl hosszú
                $projectName = htmlspecialchars($row['nev']);
                if (strlen($projectName) > 17) {  // 17 karakterre rövidítjük, ez állítható igény szerint
                    $projectName = substr($projectName, 0, 17) . '...';
                }

                echo '<div class="project-name"><a href="nyilvanos_reszletek.php?projekt_id=' . urlencode($row['id']) . '">' . $projectName . '</a></div>'; // Linkként visszaállítva
        

                $leiras = htmlspecialchars($row['leiras']);
                if (strlen($leiras) > 50) {
                    $leiras = substr($leiras, 0, 50) . '...';
                }
                echo '<div class="project-description">' . $leiras . '</div>';

                // Eddigi kitöltések számának megjelenítése
                echo '<div class="project-kitoltesek">Kitöltések száma: ' . htmlspecialchars($row['eddigi_kitoltesek']) . '</div>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo "<p>Nincs megjeleníthető projekt.</p>";
        }

        $conn->close();
        ?>
    </div>

</body>

</html>