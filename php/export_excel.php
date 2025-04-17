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
$filesSheet->setCellValue('E1', 'Hiperhivatkozás');  // Új oszlop a hiperhivatkozáshoz
// Fájlok adatai kiírása
$row = 2;
while ($fileData = $filesResult->fetch_assoc()) {
    $filesSheet->setCellValue('A' . $row, $fileData['id']);
    $filesSheet->setCellValue('B' . $row, $fileData['fajl_nev']);
    $filesSheet->setCellValue('C' . $row, $fileData['tipus']);

    // Fájl elérési útja
    $filePath = "../feltoltesek/" . $fileData['fajl_nev'];  // Fájl elérési útja
    $fileExtension = pathinfo($fileData['fajl_nev'], PATHINFO_EXTENSION);  // Fájl kiterjesztése
    $fileUrl = "../feltoltesek/" . $fileData['fajl_nev']; // Alapértelmezett fájl URL

    if (file_exists($filePath)) {
        // Ha a fájl képfájl (pl. jpg, png, gif), akkor képet adunk hozzá
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $drawing = new Drawing();
            $drawing->setName('Kép');
            $drawing->setDescription('Kép a fájlhoz');
            $drawing->setPath($filePath);
            $drawing->setHeight(50);  // Kép magasságának beállítása (kicsi)
            $drawing->setCoordinates('D' . $row); // Kép pozíciója
            $drawing->setWorksheet($filesSheet);
        } else {
            // Ha nem képfájl, akkor hivatkozást adunk hozzá
            $filesSheet->setCellValue('D' . $row, 'Fájl link');
            $filesSheet->getCell('D' . $row)->getHyperlink()->setUrl($fileUrl);  // A fájl URL-je
        }

        // Hiperhivatkozás az új oszlopba
        $filesSheet->setCellValue('E' . $row, 'Megnyitás');
        $filesSheet->getCell('E' . $row)->getHyperlink()->setUrl($fileUrl); // A fájl URL-je
    } else {
        // Ha a fájl nem található, akkor hibaüzenet jelenik meg
        $filesSheet->setCellValue('D' . $row, 'Nincs elérhető fájl');
        $filesSheet->setCellValue('E' . $row, 'Nincs elérhető fájl');  // Ha nincs fájl
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

// 6. Kérdésekre adott válaszok adatai
$sqlAnswers = "SELECT * FROM kerdesekre_valasz WHERE projekt_id = '$projektId'";
$answersResult = $conn->query($sqlAnswers);
$answersSheet = $spreadsheet->createSheet(5);
$answersSheet->setTitle('kerdesekre_valasz');

// Fejlécek beállítása
$answersSheet->setCellValue('A1', 'ID');
$answersSheet->setCellValue('B1', 'Kérdés ID');
$answersSheet->setCellValue('C1', 'Válasz');
$answersSheet->setCellValue('D1', 'Kitöltő ID');

// Válaszok adatai kiírása
$row = 2;
while ($answerData = $answersResult->fetch_assoc()) {
    $answersSheet->setCellValue('A' . $row, $answerData['id']);
    $answersSheet->setCellValue('B' . $row, $answerData['kerdesek_id']);
    $answersSheet->setCellValue('C' . $row, $answerData['valasz']);
    $answersSheet->setCellValue('D' . $row, $answerData['kitolto_id']);
    $row++;
}

// Fejlécek szűrő beállítása
$answersSheet->setAutoFilter('A1:D1');

// 7. Kitöltők adatai
$sqlFillers = "SELECT * FROM kitoltok WHERE projekt_id = '$projektId'";
$fillersResult = $conn->query($sqlFillers);
$fillersSheet = $spreadsheet->createSheet(6);
$fillersSheet->setTitle('kitoltok');

// Fejlécek beállítása
$fillersSheet->setCellValue('A1', 'ID');
$fillersSheet->setCellValue('B1', 'Projekt ID');

// Kitöltők adatai kiírása
$row = 2;
while ($fillerData = $fillersResult->fetch_assoc()) {
    $fillersSheet->setCellValue('A' . $row, $fillerData['id']);
    $fillersSheet->setCellValue('B' . $row, $fillerData['projekt_id']);
    $row++;
}

// Fejlécek szűrő beállítása
$fillersSheet->setAutoFilter('A1:B1');

// Fájl ideiglenes mentése
$tempFile = tempnam(sys_get_temp_dir(), 'projekt_adatok_') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFile);

// Fájl méretének ellenőrzése
$fileSize = filesize($tempFile);

// Ha a fájl túl nagy, zipeljük be
if ($fileSize > 10 * 1024 * 1024) { // 10 MB
    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'projekt_adatok_') . '.zip';
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($tempFile, 'projekt_adatok.xlsx');
        $zip->close();

        // ZIP fájl letöltése
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="projekt_adatok_' . date('Y-m-d_H-i') . '.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        // Törlés
        unlink($zipFile);
    }
} else {
    // Excel fájl letöltése
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="projekt_adatok_' . date('Y-m-d_H-i') . '.xlsx"');
    header('Content-Length: ' . $fileSize);
    readfile($tempFile);
}

// Törlés
unlink($tempFile);

$conn->close();
exit;
?>