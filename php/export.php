<?php
require '../vendor/autoload.php'; // Autoload Composer packages

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


// Kapcsolódás az adatbázishoz
require_once "db_connect.php";

// Projekt ID lekérése URL-ből
$projektId = $_GET['id'];

// Új Spreadsheet objektum létrehozása
$spreadsheet = new Spreadsheet();

// 1. Projektek adatai
$sqlProject = "SELECT * FROM projektek WHERE id = '$projektId'";
$projectResult = $conn->query($sqlProject);
$projectData = $projectResult->fetch_assoc();
$projectSheet = $spreadsheet->getActiveSheet();
$projectSheet->setTitle('projektek');

// Fejlécek beállítása
$projectSheet->setCellValue('A1', 'ID');
$projectSheet->setCellValue('B1', 'Név');
$projectSheet->setCellValue('C1', 'Fő kép');
$projectSheet->setCellValue('D1', 'Leírás');
$projectSheet->setCellValue('E1', 'Eddigi kitöltések');
$projectSheet->setCellValue('F1', 'Kitöltési cél');

// Projekt adatok kiírása
$projectSheet->setCellValue('A2', $projectData['id']);
$projectSheet->setCellValue('B2', $projectData['nev']);
$projectSheet->setCellValue('C2', $projectData['fokep']);
$projectSheet->setCellValue('D2', $projectData['leiras']);
$projectSheet->setCellValue('E2', $projectData['eddigi_kitoltesek']);
$projectSheet->setCellValue('F2', $projectData['kitoltesi_cel']);

// Fejlécek szűrő beállítása
$projectSheet->setAutoFilter('A1:F1');

// 2. Fájlok adatai
$sqlFiles = "SELECT * FROM fajlok WHERE projekt_id = '$projektId'";
$filesResult = $conn->query($sqlFiles);
$filesSheet = $spreadsheet->createSheet(1);
$filesSheet->setTitle('fajlok');

// Fejlécek beállítása
$filesSheet->setCellValue('A1', 'ID');
$filesSheet->setCellValue('B1', 'Fájl név');
$filesSheet->setCellValue('C1', 'Típus');
$filesSheet->setCellValue('D1', 'Kép');
$filesSheet->setCellValue('E1', 'Hiperhivatkozás');

// Fájlok adatai kiírása
$row = 2;
while ($fileData = $filesResult->fetch_assoc()) {
    $filesSheet->setCellValue('A' . $row, $fileData['id']);
    $filesSheet->setCellValue('B' . $row, $fileData['fajl_nev']);
    $filesSheet->setCellValue('C' . $row, $fileData['tipus']);

    // Kép hozzáadása
    $imagePath = "../feltoltesek/" . $fileData['fajl_nev'];  // Kép elérési útja

    // Ha a fájl valóban létezik, akkor hozzáadjuk a képet
    if (file_exists($imagePath)) {
        // Kép betöltése
        $drawing = new Drawing();
        $drawing->setName('Kép');
        $drawing->setDescription('Kép a fájlhoz');
        $drawing->setPath($imagePath);
        $drawing->setHeight(50);  // Kép magasságának beállítása (kicsi)
        $drawing->setCoordinates('D' . $row); // Kép pozíciója
        $drawing->setWorksheet($filesSheet);

        // Fájlra mutató hiperhivatkozás beállítása
        $fileUrl = "../feltoltesek/" . $fileData['fajl_nev']; // A teljes URL

        // Hiperhivatkozás az új oszlopba
        $filesSheet->setCellValue('E' . $row, 'Megnyitás');
        $filesSheet->getCell('E' . $row)->getHyperlink()->setUrl($fileUrl); // A fájl URL-je
    } else {
        $filesSheet->setCellValue('D' . $row, 'Nincs elérhető kép');
        $filesSheet->setCellValue('E' . $row, 'Nincs elérhető fájl');  // Ha nincs kép, ezt írja ki
    }

    $row++;
}

// Fejlécek szűrő beállítása
$filesSheet->setAutoFilter('A1:E1');

// 3. Értékelések adatai
$sqlRatings = "SELECT * FROM ertekelt_fajlok WHERE projekt_id = '$projektId'";
$ratingsResult = $conn->query($sqlRatings);
$ratingsSheet = $spreadsheet->createSheet(2);
$ratingsSheet->setTitle('ertekelt_fajlok');

// Fejlécek beállítása
$ratingsSheet->setCellValue('A1', 'ID');
$ratingsSheet->setCellValue('B1', 'Kitöltő ID');
$ratingsSheet->setCellValue('C1', 'Fájl ID');
$ratingsSheet->setCellValue('D1', 'Pontszám');

// Értékelések adatai kiírása
$row = 2;
while ($ratingData = $ratingsResult->fetch_assoc()) {
    $ratingsSheet->setCellValue('A' . $row, $ratingData['id']);
    $ratingsSheet->setCellValue('B' . $row, $ratingData['kitolto_id']);
    $ratingsSheet->setCellValue('C' . $row, $ratingData['fajl_id']);
    $ratingsSheet->setCellValue('D' . $row, $ratingData['pontszam']);
    $row++;
}

// Fejlécek szűrő beállítása
$ratingsSheet->setAutoFilter('A1:D1');

// 4. Felhasználók adatai
$sqlUsers = "SELECT * FROM felhasznalok WHERE id IN (SELECT felhasznalok_id FROM projektek WHERE id = '$projektId')";
$usersResult = $conn->query($sqlUsers);
$usersSheet = $spreadsheet->createSheet(3);
$usersSheet->setTitle('felhasznalok');

// Fejlécek beállítása
$usersSheet->setCellValue('A1', 'ID');
$usersSheet->setCellValue('B1', 'Felhasználónév');
$usersSheet->setCellValue('C1', 'Email');
$usersSheet->setCellValue('D1', 'Regisztráció Dátum');

// Felhasználók adatai kiírása
$row = 2;
while ($userData = $usersResult->fetch_assoc()) {
    $usersSheet->setCellValue('A' . $row, $userData['id']);
    $usersSheet->setCellValue('B' . $row, $userData['felhasznalonev']);
    $usersSheet->setCellValue('C' . $row, $userData['email']);
    $usersSheet->setCellValue('D' . $row, $userData['regisztracio_datum']);
    $row++;
}

// Fejlécek szűrő beállítása
$usersSheet->setAutoFilter('A1:D1');

// 5. Kérdések adatai
$sqlQuestions = "SELECT * FROM kerdesek WHERE projekt_id = '$projektId'";
$questionsResult = $conn->query($sqlQuestions);
$questionsSheet = $spreadsheet->createSheet(4);
$questionsSheet->setTitle('kerdesek');

// Fejlécek beállítása
$questionsSheet->setCellValue('A1', 'ID');
$questionsSheet->setCellValue('B1', 'Kérdés');
$questionsSheet->setCellValue('C1', 'Válasz Típus');
$questionsSheet->setCellValue('D1', 'Lehetséges Válaszok');

// Kérdések adatai kiírása
$row = 2;
while ($questionData = $questionsResult->fetch_assoc()) {
    $questionsSheet->setCellValue('A' . $row, $questionData['id']);
    $questionsSheet->setCellValue('B' . $row, $questionData['kerdes']);
    $questionsSheet->setCellValue('C' . $row, $questionData['valasz_tipus']);
    $questionsSheet->setCellValue('D' . $row, $questionData['lehetseges_valaszok']);
    $row++;
}

// Fejlécek szűrő beállítása
$questionsSheet->setAutoFilter('A1:D1');

// 6. Kitöltők adatai
$sqlFillers = "SELECT * FROM kerdesekre_valasz WHERE projekt_id = '$projektId'";
$fillersResult = $conn->query($sqlFillers);
$fillersSheet = $spreadsheet->createSheet(5);
$fillersSheet->setTitle('kerdesekre_valasz');

// Fejlécek beállítása
$fillersSheet->setCellValue('A1', 'ID');
$fillersSheet->setCellValue('B1', 'Kérdés ID');
$fillersSheet->setCellValue('C1', 'Válasz');
$fillersSheet->setCellValue('D1', 'Kitölő i');

// Kitöltők adatai kiírása
$row = 2;
while ($fillerData = $fillersResult->fetch_assoc()) {
    $fillersSheet->setCellValue('A' . $row, $fillerData['id']);
    $fillersSheet->setCellValue('B' . $row, $fillerData['kitolto_nev']);
    $fillersSheet->setCellValue('C' . $row, $fillerData['kitoltes_datum']);
    $fillersSheet->setCellValue('D' . $row, $fillerData['kitoltesi_ido']);
    $row++;
}

// Fejlécek szűrő beállítása
$fillersSheet->setAutoFilter('A1:D1');

// Fájl letöltése
$tempExcelFile = sys_get_temp_dir() . '/projekt_adatok_' . $projektId . '.xlsx'; // Ideiglenes fájl elérési út

// Írás a fájlba
$writer = new Xlsx($spreadsheet);
$writer->save($tempExcelFile);

// Ellenőrizzük a fájl méretét
$fileSize = filesize($tempExcelFile);
$maxFileSize = 10 * 1024 * 1024; // 10 MB

if ($fileSize > $maxFileSize) {
    // Ha a fájl túl nagy, tömörítsük ZIP fájlba
    $zipFile = sys_get_temp_dir() . '/projekt_adatok_' . $projektId . '.zip';

    // Zip fájl létrehozása
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($tempExcelFile, basename($tempExcelFile)); // Hozzáadjuk az Excel fájlt
        $zip->close();
    }

    // ZIP fájl küldése a felhasználónak
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="projekt_adatok_' . date('Y-m-d_H-i') . '_' . $projektId . '.zip"');
    header('Content-Length: ' . filesize($zipFile));

    // A ZIP fájl tartalmának kiírása
    readfile($zipFile);

    // Ideiglenes fájlok törlése
    unlink($tempExcelFile);
    unlink($zipFile);
} else {
    // Ha a fájl nem túl nagy, egyszerűen küldjük el Excel fájlt
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="projekt_adatok_' . date('Y-m-d_H-i') . '_' . $projektId . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Az Excel fájl tartalmának kiírása
    readfile($tempExcelFile);

    // Ideiglenes fájl törlése
    unlink($tempExcelFile);
}

$conn->close();
exit;
?>