<?php
$servername = env('AZURE_MYSQL_HOST');
$username = env('AZURE_MYSQL_USERNAME');
$password = env('AZURE_MYSQL_PASSWORD');
$dbname = env('AZURE_MYSQL_DBNAME');

echo $servername;
echo $username;
echo $password;
echo $szakdoga;

// Kapcsolódás az adatbázishoz
$conn = new mysqli($servername, $username, $password, $dbname);

print $conn

// Ellenőrizzük, hogy a kapcsolat sikerült-e
if ($conn->connect_error) {
    echo $conn->connect_error;
    die("Kapcsolódás hiba: " . $conn->connect_error);
}
?>
