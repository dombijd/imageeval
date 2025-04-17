<?php
session_start(); // Itt kezdődik a session, csak egyszer!

// Ellenőrizzük, hogy az admin be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev']) || $_SESSION['felhasznalonev'] !== 'admin') {
    // Ha nem admin van bejelentkezve, átirányítjuk a bejelentkezési oldalra
    header('Location: bejelentkezes.php');
    exit();
}

// Adatbázis kapcsolat beállítása
require_once "db_connect.php";

// SQL lekérdezés a felhasználók lekérésére, az admin felhasználó kiszűrésével
$sql = "SELECT id, felhasznalonev, email, letiltva FROM felhasznalok WHERE felhasznalonev != 'admin'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználók - Admin</title>
    <link rel="stylesheet" href="../css2/kezdolap.css?v=1.1">

    <link rel="stylesheet" href="../css2/felhasznalok.css?v=1.1">
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

    <!-- Container a felhasználók megjelenítéséhez -->
    <div class="container">
        <h2>Felhasználók</h2>
        <table>
            <thead>
                <tr>
                    <th>Felhasználónév</th>
                    <th>Email cím</th>
                    <th>Letiltás</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ha van eredmény, akkor végigmegyünk a felhasználók listáján és kiírjuk őket
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Felhasználó adatok
                        $username = htmlspecialchars($row['felhasznalonev']);
                        $email = htmlspecialchars($row['email']);
                        $userId = $row['id'];
                        $isDeactivated = $row['letiltva'];

                        // Akciók URL-je és gomb szövege
                        $actionUrl = $isDeactivated ? "feloldas.php?id=$userId" : "deactivate_user.php?id=$userId";
                        $buttonClass = $isDeactivated ? "activate" : "deactivate";
                        $buttonText = $isDeactivated ? "Feloldás" : "Letiltás";

                        echo "<tr>
                            <td>$username</td>
                            <td>$email</td>
                            <td><a href='$actionUrl' class='action-button $buttonClass'>$buttonText</a></td>
                          </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Nincs felhasználó az adatbázisban.</td></tr>";
                }

                // Adatbázis kapcsolat lezárása
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>