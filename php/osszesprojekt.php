<?php
@session_start();

// Ellenőrizzük, hogy az admin be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev']) || $_SESSION['felhasznalonev'] !== 'admin') {
    // Ha nem admin van bejelentkezve, átirányítjuk a bejelentkezési oldalra
    header('Location: bejelentkezes.php');
    exit();
}

// Adatbázis kapcsolat beállítása
require_once "db_connect.php";

// SQL lekérdezés a projektek lekérésére
$sql = "SELECT 
            projektek.id AS projekt_id, 
            projektek.nev AS projekt_nev, 
            projektek.eddigi_kitoltesek, 
            projektek.kitoltesi_cel, 
            felhasznalok.felhasznalonev AS felhasznalo_nev,
            felhasznalok.letiltva AS letiltva
        FROM projektek
        JOIN felhasznalok ON projektek.felhasznalok_id = felhasznalok.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Összes projekt - Admin</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.1">
    <link rel="stylesheet" href="../css2/osszesprojekt.css?v=1.2">
    <style>

    </style>
</head>

<body>

    <header>
        <h1>Projektértékelő</h1>
        <div class="auth-links">
            <a href="../html/kezdolap.html">Kijelentkezés</a>
        </div>
    </header>

    <!-- Navigációs menü -->
    <nav>
        <ul>
            <li><a href="osszesprojekt.php">Összes projekt</a></li>
            <li><a href="felhasznalok.php">Felhasználók</a></li>
        </ul>
    </nav>

    <!-- Container a projektek megjelenítéséhez -->
    <div class="container">
        <h2>Összes projekt</h2>
        <!-- Exportálás összes projekt gomb -->
        <div class="export-container">
            <?php
            if (isset($_SESSION['felhasznalonev']) && $_SESSION['felhasznalonev'] === 'admin') {
                echo '<a href="export_osszes.php" class="export-button">Összes projekt exportálása</a>';
            }
            ?>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Projekt név</th>
                    <th>Projekt létrehozója</th>
                    <th>Eddigi kitöltések</th>
                    <th>Kitöltési cél</th>
                    <th>Exportálás</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ha van eredmény, akkor végigmegyünk a projektek listáján és kiírjuk őket
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $projectName = $row['projekt_nev'];
                        $creator = htmlspecialchars($row['felhasznalo_nev']);
                        $status = $row['letiltva'] ? " (Letiltva)" : ""; // Ellenőrzés a logikai értékkel
                        $shortName = (strlen($projectName) > 20) ? substr($projectName, 0, 20) . "..." : $projectName;
                        $projectId = $row['projekt_id'];

                        $exportUrl = "export_excel.php?id=" . $projectId;

                        echo "<tr>
                            <td><span class='project-name' onclick='showModal(\"$projectName\")'>" . htmlspecialchars($shortName) . "</span></td>
                            <td>{$creator}{$status}</td>
                            <td>" . htmlspecialchars($row['eddigi_kitoltesek']) . "</td>
                            <td>" . htmlspecialchars($row['kitoltesi_cel']) . "</td>
                            <td><a href='$exportUrl' class='export-button'>Exportálás Excelbe</a></td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nincs projekt az adatbázisban.</td></tr>";
                }

                // Adatbázis kapcsolat lezárása
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Projekt név:</h2>
            <p id="fullProjectName"></p>
        </div>
    </div>

    <!-- JavaScript a modal működéséhez -->
    <script>
        function showModal(projectName) {
            document.getElementById("fullProjectName").innerText = projectName;
            document.getElementById("myModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == document.getElementById("myModal")) {
                closeModal();
            }
        }
    </script>

</body>

</html>