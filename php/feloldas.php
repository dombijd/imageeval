<?php
session_start();

if (!isset($_SESSION['felhasznalonev']) || $_SESSION['felhasznalonev'] !== 'admin') {
    header('Location: bejelentkezes.php');
    exit();
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    require_once "db_connect.php";

    $sql = "UPDATE felhasznalok SET letiltva = FALSE WHERE id = '$userId'";
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