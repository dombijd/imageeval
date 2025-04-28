<?php
session_start(); // session indítása
ob_start(); // kimenet pufferelése
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Értékelés - Projektértékelő</title> 
    <link rel="stylesheet" href="../css2/ertekeles_kitoltok.css?v=1.6">
    <script>
        // form ellenőrzés a tovább gomb előtt
        function validateForm() {
            var acceptAszf = document.getElementById("accept-aszf").checked; // ászf pipa ellenőrzés
            if (!acceptAszf) {
                alert("Az ÁSZF-et el kell fogadni a továbblépéshez!");
                return false;
            }

            // kötelező mezők ellenőrzése
            var inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].value === "") {
                    alert("Minden kötelező kérdésre válaszolni kell!");
                    return false;
                }
            }
            return true;
        }

        // dátum mező megjelenítés kezelése legördülő alapján
        function showDateInput(selectElem, questionId) {
            const questionContainer = selectElem.closest('.question-container');
            let dateInput = questionContainer.querySelector('input[type="date"]'); // dátum keres

            if (selectElem.value === 'date') {
                // ha date-t választ
                if (!dateInput) {
                    dateInput = document.createElement('input');
                    dateInput.setAttribute('type', 'date');
                    dateInput.name = 'valasz[' + questionId + ']';
                    dateInput.required = true;
                    questionContainer.appendChild(dateInput);
                }
            } else {
                // ha nem date eltávolítjuk
                if (dateInput) {
                    dateInput.remove();
                }
            }
        }

        // oldal betöltéskor események hozzárendelése
        document.addEventListener("DOMContentLoaded", function() {
            const selects = document.querySelectorAll('select'); // összes select keresése
            selects.forEach(function(select) {
                const questionId = select.name.match(/\d+/)[0]; // kérdés id kiszedése
                showDateInput(select, questionId); // kezdeti megjelenítés
                select.addEventListener('change', function() {
                    showDateInput(select, questionId); // változás figyelés
                });
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Az értékelés elkezdése előtt:</h1> 
    </header>

    <div class="container"> 

        <div class="instructions">Kérjük, töltse ki a kitöltés előtt az alábbiakat:</div>

        <div class="checkbox-container"> <!-- ászf elfogadása -->
            <label>
                <a href="../html/aszf.html" target="_blank" style="padding: 10px;">ÁSZF elolvasása</a>
            </label><br>
            <input type="checkbox" id="accept-aszf" name="accept-aszf" required> 
            <label for="accept-aszf">Elfogadom az ÁSZF-et</label>
        </div>

        <?php
        // projekt_id ellenőrzése
        $projekt_id = isset($_GET['projekt_id']) ? intval($_GET['projekt_id']) : null;
        if ($projekt_id === null) {
            echo "Nincs projekt kiválasztva.";
            exit(); // kilépés ha nincs projekt
        }

        // adatb kapcsolódás
        require_once "db_connect.php";

        // ellenőrzés van-e már kitöltő session-ben
        if (!isset($_SESSION['kitolto_id_' . $projekt_id])) {
            // ha nincs, új kitöltő beszúrása
            $insertKitolto = $conn->prepare("INSERT INTO kitoltok (projekt_id) VALUES (?)");
            $insertKitolto->bind_param("i", $projekt_id);
            if (!$insertKitolto->execute()) {
                echo "Hiba a kitöltő beszúrása során: " . $insertKitolto->error;
                exit();
            }
            $kitolto_id = $conn->insert_id; // új kitöltő id lekérése
            $_SESSION['kitolto_id_' . $projekt_id] = $kitolto_id; // mentés session-be
            $insertKitolto->close();
        } else {
            // ha már létezik, onnan vesszük
            $kitolto_id = $_SESSION['kitolto_id_' . $projekt_id];
        }

        // kérdések lekérése az adott projekthez
        $sqlKerdezsek = "SELECT * FROM kerdesek WHERE projekt_id = ?";
        $stmt = $conn->prepare($sqlKerdezsek);
        $stmt->bind_param("i", $projekt_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // ha nincs kérdés
        if ($result->num_rows == 0) {
            echo '<p>Itt nincs kérdés.</p>';
        }

        // űrlap megjelenítése
        echo '<form action="" method="post" onsubmit="return validateForm()">';

        while ($row = $result->fetch_assoc()) {
            $kerdes = htmlspecialchars($row['kerdes']);
            $required = $row['required']; // kötelező-e
            echo '<label>' . $kerdes;
            if ($required == 1) {
                echo ' <span style="color: red;">*</span>'; // ha kötelező, csillag
            }
            echo '</label>';

            $valasz_tipus = $row['valasz_tipus']; // válasz típusa
            echo '<div class="question-container">';

            // válasz típusok kezelése
            if ($valasz_tipus == 'enum') {
                // legördülő menü
                $lehetseges_valaszok = explode(',', $row['lehetseges_valaszok']);
                echo '<select name="valasz[' . $row['id'] . ']" onchange="showDateInput(this, ' . $row['id'] . ')"' . ($required == 1 ? ' required' : '') . '>';
                echo '<option value="">-- Válassz --</option>';
                foreach ($lehetseges_valaszok as $valasz) {
                    echo '<option value="' . htmlspecialchars(trim($valasz)) . '">' . htmlspecialchars(trim($valasz)) . '</option>';
                }
                echo '</select>';
            } elseif ($valasz_tipus == 'text') {
                // rövid szöveg
                echo '<input type="text" name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a válaszodat...">';
            } elseif ($valasz_tipus == 'int') {
                // szám
                echo '<input type="number" name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a számot...">';
            } elseif ($valasz_tipus == 'string') {
                // hosszabb szöveg
                echo '<textarea name="valasz[' . $row['id'] . ']"' . ($required == 1 ? ' required' : '') . ' placeholder="Írd be a válaszodat..."></textarea>';
            } elseif ($valasz_tipus == 'date') {
                // dátum beírás
                echo '<input type="date" name="valasz[' . $row['id'] . ']" min="1900-01-01" max="2020-01-01"' . ($required == 1 ? ' required' : '') . '>';
            }

            echo '</div>'; // question-container vége
        }

        // gombok
        echo '<div class="button-container">';
        echo '<a class="back-button" href="projektek.php">Vissza a projektekhez</a>'; // vissza link
        echo '<button class="continue-button" type="submit">Tovább</button>'; // tovább gomb
        echo '</div>';

        echo '</form>'; // form vége

        // ha elküldték a formot
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST['valasz'] as $kerdes_id => $valasz) {
                $_SESSION['valaszok_' . $projekt_id][$kerdes_id] = $valasz; // válaszok mentése session-be
            }
            // átirányítás fájlok értékeléséhez
            header("Location: fajlok_ertekelese.php?projekt_id=" . $projekt_id . "&current_file=1");
            exit();
        }

        $stmt->close(); // lekérdezés lezárása
        $conn->close(); // adatb kapcsolat zárása
        ?>
    </div>
</body>
</html>

<?php
ob_end_flush(); // puffer lezárása
?>
