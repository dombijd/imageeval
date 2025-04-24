<?php
@session_start();

// Ellenőrzés, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev'])) {
    header("Location: bejelentkezes.php");
    exit();
}

// Kapcsolódás az adatbázishoz
require_once "db_connect.php";

$messages = []; // Hiba és sikerüzenetek tárolása

// Kérdések mentése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $projectId = isset($_POST['project_id']) ? $_POST['project_id'] : null; // A projekt ID

    if (!empty($_POST['questions'])) {
        foreach ($_POST['questions'] as $question) {
            $kerdes = isset($question['kerdes']) ? $conn->real_escape_string($question['kerdes']) : '';
            $valaszTipus = isset($question['valasz_tipus']) ? $conn->real_escape_string($question['valasz_tipus']) : '';
            $lehetsegesValaszok = isset($question['lehetseges_valaszok']) ? $conn->real_escape_string($question['lehetseges_valaszok']) : '';

            // Kérdés beszúrása
            if (!empty($kerdes) && !empty($valaszTipus)) {
                // Csak akkor ellenőrizzük a lehetseges_valaszok mezőt, ha a válasz típusa enum
                if ($valaszTipus === 'enum' && empty($lehetsegesValaszok)) {
                    $messages[] = "A 'lehetseges_valaszok' mező kitöltése kötelező, ha a válasz típusa 'enum'.";
                    continue; // Ugrás a következő kérdésre
                }

                $sqlQuestion = "INSERT INTO kerdesek (projekt_id, kerdes, valasz_tipus, lehetseges_valaszok) 
                VALUES ($projectId, '$kerdes', '$valaszTipus', '$lehetsegesValaszok')";

                if ($conn->query($sqlQuestion) === FALSE) {
                    $messages[] = "Hiba a kérdés mentésekor: " . $conn->error;
                } else {
                    $messages[] = "Kérdés hozzáadva: " . htmlspecialchars($kerdes);
                }
            } else {
                if (empty($kerdes)) {
                    $messages[] = "A kérdés mező kitöltése kötelező.";
                }
                if (empty($valaszTipus)) {
                    $messages[] = "A válasz típus megadása kötelező.";
                }
            }
        }
        if (empty($messages)) {
            $messages[] = "Kérdések sikeresen mentve!";
        }
    } else {
        $messages[] = "Nincsenek kérdések megadva.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kérdések hozzáadása</title>
    <link rel="stylesheet" href="../css/kezdolap.css">
    <link rel="stylesheet" href="../css/reg.css">
    <style>
        /* Ide jöhetnek a stílusok */
        .message {
            color: red;
            /* Vagy bármilyen más szín a hibaüzenetekhez */
            margin: 10px 0;
        }

        .success {
            color: green;
            /* A sikerüzenetekhez */
        }
    </style>
    <script>
        const existingQuestions = <?php echo json_encode($letezoKerdesek); ?>
        function addQuestion() {
            const questionContainer = document.createElement('div');
            questionContainer.classList.add('question-container');

            questionContainer.innerHTML = `
                <label for="question">Kérdés:</label>
                <input type="text" name="questions[][kerdes]" required>
                <label for="type">Típus:</label>
                <select name="questions[][valasz_tipus]" required>
                    <option value="int">Int</option>
                    <option value="boolean">Boolean</option>
                    <option value="enum">Enum</option>
                    <option value="szoveg">Szöveg</option>
                </select>
                <div class="enum-options" style="display: none;">
                    <label for="options">Választék (válaszos enum esetén):</label>
                    <input type="text" name="questions[][lehetseges_valaszok]" placeholder="Példa: Igen, Nem" />
                </div>
                <span class="remove-question" onclick="removeQuestion(this)">X</span>
            `;

            questionContainer.querySelector('select[name="questions[][valasz_tipus]"]').addEventListener('change', function () {
                const enumOptions = questionContainer.querySelector('.enum-options');
                if (this.value === 'enum') {
                    enumOptions.style.display = 'block';
                } else {
                    enumOptions.style.display = 'none';
                }
            });

            document.getElementById('questions').appendChild(questionContainer);
        }

        function removeQuestion(element) {
            const questionContainer = element.parentElement;
            questionContainer.remove();
        }
    </script>
</head>

<body>
    <header>
        <h1>Kérdések Hozzáadása</h1>
        <div class="auth-links">
            <a href="regisztracio.php">Kijelentkezés</a>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="projektjeim.php">Projektjeim</a></li>
            <li><a href="ujprojekt.php">Új projekt</a></li>
        </ul>
    </nav>

    <div class="content">
        <div class="form-container">
            <h2>Kérdések hozzáadása a projekthez</h2>

            <!-- Itt jelenítjük meg az üzeneteket -->
            <div id="message-container">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message"><?= htmlspecialchars($message) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form action="kerdesek.php" method="post">
                <input type="hidden" name="project_id" value="1"> <!-- Állítsd be a megfelelő projekt ID-t -->
                <h3>Kérdések:</h3>
                <div id="questions"></div>
                <button type="button" onclick="addQuestion()">Új kérdés hozzáadása</button>
                <input type="submit" value="Kérdések mentése">
            </form>
        </div>
    </div>
</body>

</html>

<?php
$conn->close(); // Kapcsolat bezárása
?>