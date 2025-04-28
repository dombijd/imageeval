<?php
session_start(); // Session indítása

// Ellenőrzés, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalonev'])) {
    header("Location: bejelentkezes.php"); // Ha nem, átirányítjuk a bejelentkezés oldalra
    exit();
}

// Kapcsolódás az adatbázishoz
require_once "db_connect.php";

// Projekt ID lekérése
$projektId = $_GET['id'];

// Először töröljük a hivatkozott fájlokat az 'ertekelt_fajlok' táblából
$sqlDeleteHivatkozott = "DELETE FROM ertekelt_fajlok WHERE projekt_id = ?";
$stmtDeleteHivatkozott = $conn->prepare($sqlDeleteHivatkozott);
$stmtDeleteHivatkozott->bind_param("i", $projektId);
$stmtDeleteHivatkozott->execute();

// Töröljük a fájlokat a 'fajlok' táblából
$sqlDeleteFajlok = "DELETE FROM fajlok WHERE projekt_id = ?";
$stmtDeleteFajlok = $conn->prepare($sqlDeleteFajlok);
$stmtDeleteFajlok->bind_param("i", $projektId);
$stmtDeleteFajlok->execute();

// Végül töröljük a projektet a 'projektek' táblából
$sqlDeleteProjekt = "DELETE FROM projektek WHERE id = ?";
$stmtDeleteProjekt = $conn->prepare($sqlDeleteProjekt);
$stmtDeleteProjekt->bind_param("i", $projektId);
if ($stmtDeleteProjekt->execute()) {
    // Sikeres törlés után visszairányítás
    header("Location: projektjeim.php");
} else {
    echo "Hiba a törlés során: " . $conn->error;
}

// Adatbázis kapcsolat lezárása
$conn->close();
?>