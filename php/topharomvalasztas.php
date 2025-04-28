<?php
session_start();

// Ellenőrizzük, hogy van-e projekt ID
$projekt_id = isset($_GET['projekt_id']) ? intval($_GET['projekt_id']) : null;
if ($projekt_id === null) {
    echo "Hiba: Nincs projekt azonosító!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>Értékelés befejezve</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background-image: url('../oldalra_kepek/hatterkep.jfif');
            /* Háttérkép elérési útvonala */
            background-size: cover;
            /* A kép lefedi az egész ablakot */
            background-position: center;
            /* Középre igazított kép */
            background-repeat: no-repeat;
            /* Nincs ismétlés */
            background-attachment: fixed;
            /* Fixált háttérkép */
        }

        .button-container {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <h1>Köszönjük az értékelést!</h1>
    <p>Válassz, hogy visszamész a kezdőlapra, vagy megnézed a top 3 legjobbra értékelt képet.</p>

    <div class="button-container">
        <a href="../html/kezdolap.html" class="button">Vissza a Kezdőlapra</a>
        <a href="top3.php?projekt_id=<?php echo $projekt_id; ?>" class="button">TOP 3 Kép Megtekintése</a>
    </div>

</body>

</html>