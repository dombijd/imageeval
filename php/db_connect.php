<?php
$servername = "imageeval.mysql.database.azure.com";
$username = "annAdmin";
$password = "FvhrFnjHzgF32!";
$dbname = "szakdoga";

print $servername
print $username
print $password
print $szakdoga

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $dbname);

print $conn

// Ellenőrizzük, hogy a kapcsolat sikerült-e
if ($conn->connect_error) {
    print $conn->connect_error
    die("Kapcsolódás hiba: " . $conn->connect_error);
}
?>
