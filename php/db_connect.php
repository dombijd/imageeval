<?php
$servername = "imageeval.mysql.database.azure.com";
$username = "annaAdmin";
$password = "FvhrFnjHzgF32!";
$dbname = "szakdoga2";


// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $dbname);

// Ellenőrizzük, hogy a kapcsolat sikerült-e
if ($conn->connect_error) {
    die("Kapcsolódás hiba: " . $conn->connect_error);
}
?>
