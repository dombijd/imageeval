<?php
session_start(); // Session indítása
ob_start(); // Kimenet pufferelése

?>

<!DOCTYPE html>
<html lang="hu"> <!-- A dokumentum magyar nyelvű -->
<head>
    <meta charset="UTF-8"> <!-- Az oldal karakterkódolása UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Értékelés - Projektértékelő</title> <!-- Az oldal címe -->
    <link rel="stylesheet" href="../css2/ertekeles_kitoltok.css?v=1.6"> <!-- Külső CSS fájl hozzáadása -->
    <script>
        // Ellenőrzés a "Tovább" gomb előtt
        function validateForm() {
            // Ellenőrizzük, hogy a checkbox be van pipálva
            var acceptAszf = document.getElementById("accept-aszf").checked;
            if (!acceptAszf) {
                alert("Az ÁSZF-et el kell fogadni a továbblépéshez!");
                return false;
            }
            
            // Ellenőrizzük, hogy minden kötelező válasz meg van adva
            var inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].value === "") {
                    alert("Minden kötelező kérdésre válaszolni kell!");
                    return false;
                }
            }
            return true;  // Ha minden oké, tovább léphet
        }

        function showDateInput(selectElem, questionId) {
            const questionContainer = selectElem.closest('.question-container');
            let dateInput = questionContainer.querySelector('input[type="date"]');  // Keresünk egy meglévő dátum mezőt

            // Ha a választás 'date', akkor megjelenítjük a dátum inputot
            if (selectElem.value === 'date') {
                // Ha még nincs dátum input mező, akkor létrehozzuk
                if (!dateInput) {
                    dateInput = document.createElement('input');
                    dateInput.setAttribute('type', 'date');
                    dateInput.name = 'valasz[' + questionId + ']'; // Az input mező neve kérdés ID alapján
                    dateInput.required = true; // A mező kötelező
                    questionContainer.appendChild(dateInput); // Hozzáadjuk a kérdés konténeréhez
                }
            } else {
                // Ha a választás nem 'date', eltávolítjuk a dátum inputot
                if (dateInput) {
                    dateInput.remove();
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Keresés a legördülő menük között, és minden esetben frissítjük a dátum mezőt
            const selects = document.querySelectorAll('select');
            selects.forEach(function(select) {
                const questionId = select.name.match(/\d+/)[0];  // Kivesszük az ID-t a kérdéshez
                // Megjelenítjük a megfelelő input mezőt (dátum inputot, ha 'date' típusú választ választottak)
                showDateInput(select, questionId);
                
                // Hozzáadjuk az 'onchange' eseményt a legördülő menühöz
                select.addEventListener('change', function() {
                    showDateInput(select, questionId);
                });
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Az értékelés elkezdése előtt:</h1> <!-- Fő cím -->
    </header>
    <div class="container"> <!-- A tartalom konténer -->
        <div class="instructions">Kérjük, töltse ki a kitöltés előtt az alábbiakat:</div>

        <!-- ÁSZF link és elfogadás -->
        <div class="checkbox-container">
            <label>
                <a href="../html/aszf.html" target="_blank" style="padding: 10px;;">Általános Szerződési Feltételek (ÁSZF) elolvasása</a>
            </label><br>
            <input type="checkbox" id="accept-aszf" name="accept-aszf" required > 
            <label for="accept-aszf">Elfogadom az ÁSZF-et</label>
        </div>

        <!-- Utasítások a felhasználónak -->
        <?php
        // Ellenőrizzük, hogy a projekt_id paraméter létezik-e a GET kérésben
        $projekt_id = isset($_GET['projekt_id']) ? intval($_GET['projekt_id']) : null;  // Projekt azonosító lekérése
        if ($projekt_id === null) {
            echo "Nincs projekt kiválasztva."; // Ha nincs projekt kiválasztva, üzenet jelenik meg
            exit(); // Kilépés a scriptből
        }
        // Kapcsolódás az adatbázishoz
        require_once "db_connect.php";

        // Ellenőrizzük, hogy van-e már kitolto_id a session-ben
        if (!isset($_SESSION['kitolto_id_' . $projekt_id])) {
            // Ha nincs, beszúrjuk az új kitöltőt az adatbázisba
            $insertKitolto = $conn->prepare("INSERT INTO kitoltok (projekt_id) VALUES (?)");
            $insertKitolto->bind_param("i", $projekt_id);  // Projekt id hozzárendelése
            if (!$insertKitolto->execute()) {
                echo "Hiba a kitoltok beszúrása során: " . $insertKitolto->error; // Hiba esetén üzenet
                exit();
            }
            // Az új kitolto_id lekérése
            $kitolto_id = $conn->insert_id;
            // Mentjük a kitolto_id-t a session-be
            $_SESSION['kitolto_id_' . $projekt_id] = $kitolto_id;
            $insertKitolto->close();
        } else {
            // Ha már létezik, a session-ből vesszük ki
            $kitolto_id = $_SESSION['kitolto_id_' . $projekt_id];
        }
        // Kérdések lekérdezése az adott projekt_id alapján
        $sqlKerdezsek = "SELECT * FROM kerdesek WHERE projekt_id = ?";  // SQL lekérdezés a kérdésekhez
        $stmt = $conn->prepare($sqlKerdezsek);
        $stmt->bind_param("i", $projekt_id);  // Projekt id hozzárendelése
        $stmt->execute();  // Lekérdezés végrehajtása
        $result = $stmt->get_result();  // Eredmény lekérése
        // Ellenőrizzük, hogy vannak-e kérdések
        if ($result->num_rows == 0) {
            echo '<p>Itt nincs kérdés.</p>';  // Ha nincsenek kérdések, üzenet megjelenítése
        }
        // Kérdések és válaszok megjelenítése
        echo '<form action="" method="post" onsubmit="return validateForm()">';  // Form létrehozása
        while ($row = $result->fetch_assoc()) {  // Minden kérdés végigolvasása
            $kerdes = htmlspecialchars($row['kerdes']);  // Kérdés kinyerése
            $required = $row['required'];  // Kötelező kérdés-e (0 vagy 1)
            // Kérdés megjelenítése
            echo '<label>' . $kerdes;
            // Ha kötelező, adjunk hozzá egy csillagot
            if ($required == 1) {
                echo ' <span style="color: red;">*</span>';  // Csillag a kötelező kérdésekhez
            }
            echo '</label>';
            // Válasz típus ellenőrzése
            $valasz_tipus = $row['valasz_tipus'];
            echo '<div class="question-container">';  // Konténer a kérdéshez
            if ($valasz_tipus == 'enum') {
                // Legördülő menü
                $lehetseges_valaszok = explode(',', $row['lehetseges_valaszok']);  // Lehetséges válaszok
                echo '<select name="valasz[' . $row['id'] . ']" onchange="showDateInput(this, ' . $row['id'] . ')"' . ($required == 1 ? ' required' : '') . '>';
                echo '<option value="">-- Válassz --</option>';
                foreach ($lehetseges_valaszok as $valasz) {
                    echo '<option value="' . htmlspecialchars(trim($valasz)) . '">' . htmlspecialchars(trim($valasz)) . '</option>';
                }
                echo '</select>';
            } elseif ($valasz_tipus == 'text') {
                // Szöveges válasz
                echo '<input type="text" name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a válaszodat...">';
            } elseif ($valasz_tipus == 'int') {
                // Szám bevitel
                echo '<input type="number" name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a számot...">';
            } elseif ($valasz_tipus == 'string') {
                // Szöveges válasz, ami lehet hosszabb
                echo '<textarea name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a válaszodat..."></textarea>';
            } elseif ($valasz_tipus == 'date') {
                echo '<input type="date" name="valasz[' . $row['id'] . ']" min="1900-01-01" max="2020-01-01"' . ($required == 1 ? ' required' : '') . '>';

            }
            echo '</div>';
        }
        // Gombok elhelyezése
        echo '<div class="button-container">';
        echo '<a class="back-button" href="projektek.php">Vissza a projektekhez</a>';  // Vissza link
        echo '<button class="continue-button" type="submit">Tovább</button>';  // Tovább gomb
        echo '</div>';
        echo '</form>';  // Form bezárása
        // Adatok session-be mentése a válaszokkal
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['valasz'] as $kerdes_id => $valasz) {  // A válaszok mentése
                // Mentés session-be
                $_SESSION['valaszok_' . $projekt_id][$kerdes_id] = $valasz;
            }
            // Átirányítás a fájlok értékeléséhez
            header("Location: fajlok_ertekelese.php?projekt_id=" . $projekt_id . "&current_file=1");
            exit();  // Kilépés
        }
        // Zárjuk le a kapcsolatot
        $stmt->close();  // A kérdések lekérdezése lezárása
        $conn->close();  // Adatbázis kapcsolat lezárása
        ?>
    </div>
</body>
</html>

<?php
ob_end_flush(); // Pufferelt tartalom kiküldése
?>
