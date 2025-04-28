<?php
session_start();
// Ellenőrzi, hogy az admin be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev']) || $_SESSION['felhasznalonev'] !== 'admin') {
    header('Location: bejelentkezes.php');// Ha nincs bejelentkezve admin, átirányítja a bejelentkezési oldalra
    exit();
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];// A felhasználó ID-jának tárolása a GET paraméterből

    require_once "db_connect.php";

    $sql = "UPDATE felhasznalok SET letiltva = FALSE WHERE id = '$userId'"; // SQL lekérdezés a felhasználó letiltásának feloldásához
    if ($conn->query($sql) === TRUE) {
        header("Location: felhasznalok.php");
        exit();
    } else {
        echo "Hiba történt a feloldás során: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Nem adtál meg felhasználó ID-t.";
}
?>