<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - Projektértékelő</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.1">
    <link rel="stylesheet" href="../css2/reg.css?v=1.2">
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
        <div class="form-container">
            <h2>Regisztráció</h2>
            <form action="" method="post">
                <label for="username">Felhasználónév:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Jelszó:</label>
                <input type="password" id="password" name="password" required minlength="4"
                    title="A jelszónak legalább 4 karakterből kell állnia.">


                <input type="submit" value="Regisztrálok">
            </form>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "db_connect.php";

        // Regisztrációs űrlap adatok
        $felhasznalonev = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $jelszo = password_hash($_POST['password'], PASSWORD_DEFAULT); // Jelszó titkosítása
    
        // Ellenőrizd, hogy a felhasználónév már létezik-e
        $checkUsernameSql = "SELECT * FROM felhasznalok WHERE felhasznalonev = '$felhasznalonev'";
        $checkUsernameResult = $conn->query($checkUsernameSql);

        // Ellenőrizd, hogy az e-mail cím már regisztrálva van-e
        $checkEmailSql = "SELECT * FROM felhasznalok WHERE email = '$email'";
        $checkEmailResult = $conn->query($checkEmailSql);

        if ($checkUsernameResult->num_rows > 0) {
            // Ha a felhasználónév foglalt
            echo "<script>alert('A felhasználónév már foglalt. Kérlek válassz másikat!');</script>";
        } elseif ($checkEmailResult->num_rows > 0) {
            // Ha az e-mail cím már regisztrálva van
            echo "<script>alert('Ez az e-mail cím már regisztrálva van!');</script>";
        } else {
            // Ha nincs probléma, akkor végrehajtjuk a regisztrációt
            $sql = "INSERT INTO felhasznalok (felhasznalonev, email, jelszo) 
                VALUES ('$felhasznalonev', '$email', '$jelszo')";

            if ($conn->query($sql) === TRUE) {
                // Ha sikeres volt, átirányítjuk a felhasználót a bejelentkezéshez
                header("Location: bejelentkezes.php");
                exit();
            } else {
                echo "<p>Hiba: " . $conn->error . "</p>";
            }
        }



        // Kapcsolat lezárása
        $conn->close();
    }
    ?>

</body>

</html>