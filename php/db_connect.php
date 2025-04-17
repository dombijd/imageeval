<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "szakdoga";

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $dbname);

// Ellenőrizzük, hogy a kapcsolat sikerült-e
if ($conn->connect_error) {
    die("Kapcsolódás hiba: " . $conn->connect_error);
}
?>
