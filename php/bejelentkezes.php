<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - Projektértékelő</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.2">
    <link rel="stylesheet" href="../css2/reg.css?v=1.1">
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
    <div class="content">
        <div class="form-container">  <!-- bejel űrlap -->
            <h2>Bejelentkezés</h2>
            <form action="" method="post">
                <label for="username">Felhasználónév:</label>
                <input type="text" id="username" name="username" required>
                <label for="password">Jelszó:</label>
                <input type="password" id="password" name="password" required>
                <input type="submit" value="Bejelentkezem">
            </form> 
        </div>
    </div>
    <?php
    // ell., hogy POSTtal történik e
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "db_connect.php";

        // felhnév és jelszó bevitel adatbból (real escape string az sql injekciók ellen)
        $felhasznalonev = $conn->real_escape_string($_POST['username']);
        $jelszo = $_POST['password'];
        // sql a felhnév és jelszó ell.re
        $sql = "SELECT id, jelszo, admin, letiltva FROM felhasznalok WHERE felhasznalonev = '$felhasznalonev'";
        $result = $conn->query($sql);
        // ha van találat felhasznnévre
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // ell., hogy le van e tiltva
            if ($row['letiltva'] == 1) {
                // ha le van tiltva
                echo "<script>alert('Sajnáljuk, de a felhasználód le van tiltva. Nem tudsz bejelentkezni.');</script>";
            } else {
                // jelszo ell a hashelttel
                if (password_verify($jelszo, $row['jelszo'])) {
                    // ha sikerült bejel akkor session inditasa
                    session_start();
                    $_SESSION['felhasznalo_id'] = $row['id']; // felhaszn azonosítójának tárolása a session-ben
                    $_SESSION['felhasznalonev'] = $felhasznalonev; // felhasznnév tárolása a session-ben
                    // ha admin akkor admin oldalra menjen
                    if ($felhasznalonev == 'admin' && $jelszo == 'admin') {
                        echo "<script>alert('Sikeres bejelentkezés admin felhasználóként!');</script>";
                        echo "<script>window.location.href = 'osszesprojekt.php';</script>";
                        exit();
                    } else {
                        // ha nem admin akkor a projektjeim oldalra
                        echo "<script>alert('Sikeres bejelentkezés!');</script>";
                        echo "<script>window.location.href = 'projektjeim.php';</script>";
                        exit();
                    }
                } else {
                    // rossz jelszó
                    echo "<script>alert('Hibás jelszó.');</script>";
                }
            }
        } else {
            // nincs ilyen felhasznló
            echo "<script>alert('Nincs ilyen felhasználó.');</script>";
        }
        // adatb kapcsolat bezárása
        $conn->close();
    }
    ?>
</body>
</html>