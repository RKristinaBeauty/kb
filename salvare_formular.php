<?php
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Ceva nu a mers bine!'];

try {
    // Validare date
    $nume = htmlspecialchars($_POST['nume'] ?? '');
    $prenume = htmlspecialchars($_POST['prenume'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $telefon = htmlspecialchars($_POST['telefon'] ?? '');
    $comentarii = htmlspecialchars($_POST['comentarii'] ?? '');
    $captcha = $_POST['captcha'] ?? '';
    $rezultat_captcha = 7; // Suma 4 + 3

    if ($captcha != $rezultat_captcha) {
        throw new Exception("Captcha incorect!");
    }

    // Procesare fișier atașat
    $fisierInfo = [];
    if (isset($_FILES['atasare']) && $_FILES['atasare']['error'] === UPLOAD_ERR_OK) {
        $fisierNume = basename($_FILES['atasare']['name']);
        $destinatie = 'uploads/' . uniqid() . '-' . $fisierNume;

        if (move_uploaded_file($_FILES['atasare']['tmp_name'], $destinatie)) {
            $fisierInfo = [
                'nume' => $fisierNume,
                'cale' => $destinatie,
            ];
        } else {
            throw new Exception("Eroare la salvarea fișierului.");
        }
    }

    // Pregătire date pentru salvare
    $data = [
        'nume' => $nume,
        'prenume' => $prenume,
        'email' => $email,
        'telefon' => $telefon,
        'comentarii' => $comentarii,
        'fisier' => $fisierInfo,
        'data_trimitere' => date('Y-m-d H:i:s'),
    ];

    // Salvare în fișier JSON
    $jsonFile = 'date_formular.json';
    $dateCurente = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    $dateCurente[] = $data;
    file_put_contents($jsonFile, json_encode($dateCurente, JSON_PRETTY_PRINT));

    $response = ['status' => 'success', 'message' => 'Datele au fost salvate cu succes!'];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
