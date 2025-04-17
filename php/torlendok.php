<?php
session_start(); // Session indítása

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev'])) {
    header("Location: bejelentkezes.php"); // Ha nem, átirányítjuk a bejelentkezés oldalra
    exit();
}

// Adatbázis kapcsolat beállítása
require_once "db_connect.php";

// Médiafájlok lekérdezése
$sqlMedia = "SELECT * FROM fajlok WHERE projekt_id = ?";
$stmt = $conn->prepare($sqlMedia);
$stmt->bind_param("i", $_GET['id']); // Projekt ID lekérdezése a GET paraméterből
$stmt->execute();
$resultMedia = $stmt->get_result();

// Ha a formot elküldjük
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_files'])) {
    if (!empty($_POST['delete_files'])) {
        // Törlés
        foreach ($_POST['delete_files'] as $fileId) {
            // Fájl nevének lekérdezése törlés előtt
            $sqlGetFileName = "SELECT fajl_nev FROM fajlok WHERE id = ?";
            $stmtGetFileName = $conn->prepare($sqlGetFileName);
            $stmtGetFileName->bind_param("i", $fileId);
            $stmtGetFileName->execute();
            $resultFileName = $stmtGetFileName->get_result();

            if ($resultFileName->num_rows > 0) {
                $fileName = $resultFileName->fetch_assoc()['fajl_nev'];

                // Fájl törlése a feltöltések mappából
                $filePath = "../feltoltesek/" . $fileName;
                if (file_exists($filePath)) {
                    unlink($filePath); // Fájl törlése
                }

                // Fájl törlése az adatbázisból
                $sqlDelete = "DELETE FROM fajlok WHERE id = ?";
                $stmtDelete = $conn->prepare($sqlDelete);
                $stmtDelete->bind_param("i", $fileId);
                $stmtDelete->execute();
            }
        }
        // Frissítjük a fő ablakot, majd bezárjuk a törlő ablakot
        echo "<script>alert('A kiválasztott fájlok törölve lettek!'); 
         window.opener.refreshMedia(); // Hívja a módosítás oldalán lévő függvényt
        window.close();</script>";

    } else {
        // Ha nem volt kijelölve törlés
        echo "<script>alert('Nem választott fájlokat a törléshez!'); window.close();</script>";
    }
    exit; // Kilépünk a scriptből
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médiafájlok Törlése</title>
    <link rel="stylesheet" href="../css2/kezdolap.css"> <!-- Külső CSS fájlok -->
   <!-- <link rel="stylesheet" href="../css/modositas.css">-->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .media-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .media-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #fff;
            width: calc(30% - 20px);
            box-sizing: border-box;
            text-align: center;
        }

        .media-item img,
        .media-item video {
            max-width: 100%;
            height: auto;
        }

        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 50%;
        }

        input[type="button"] {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 50%;
        }

        input[type="submit"]:hover {
            background-color: #218838;
            /* Sötétebb zöld */
        }

        input[type="button"]:hover {
            background-color: #c82333;
            /* Sötétebb piros */
        }

        h2 {
            text-align: center;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Gombok közötti távolság */
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div>
        <h2>Összes Médiafájl Törlése</h2>
        <form method="POST" action="torlendok.php?id=<?php echo htmlspecialchars($_GET['id']); ?>"
            onsubmit="return confirmDeletion();">
            <div class="media-preview">
                <?php while ($media = $resultMedia->fetch_assoc()): ?>
                    <div class="media-item">
                        <?php if (strpos($media['fajl_nev'], '.jpg') !== false || strpos($media['fajl_nev'], '.jpeg') !== false || strpos($media['fajl_nev'], '.png') !== false): ?>
                            <img src="../feltoltesek/<?php echo htmlspecialchars($media['fajl_nev']); ?>"
                                alt="<?php echo htmlspecialchars($media['fajl_nev']); ?>">
                        <?php elseif (strpos($media['fajl_nev'], '.mp4') !== false || strpos($media['fajl_nev'], '.webm') !== false): ?>
                            <video controls>
                                <source src="../feltoltesek/<?php echo htmlspecialchars($media['fajl_nev']); ?>"
                                    type="video/<?php echo pathinfo($media['fajl_nev'], PATHINFO_EXTENSION); ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <p><?php echo htmlspecialchars($media['fajl_nev']); ?></p>
                        <?php endif; ?>
                        <input type="checkbox" name="delete_files[]" value="<?php echo $media['id']; ?>"> Törlés
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="button-container">
                <input type="submit" value="Fájlok törlése">
                <input type="button" value="Mégse" onclick="window.close();">
            </div>
        </form>
    </div>

    <script>
        function confirmDeletion() {
            var checkboxes = document.querySelectorAll('input[name="delete_files[]"]:checked');
            if (checkboxes.length === 0) {
                alert("Nincs kiválasztott fájl a törléshez.");
                return false;
            }

            var confirmation = confirm("Biztosan törölni akarja a kiválasztott fájlokat?");
            return confirmation; // Ha OK, akkor tovább küldi a formot
        }
    </script>

</body>

</html>