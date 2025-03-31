<?php
// salvare_formular.php

// Configurare baza de date SQLite
$db_path = __DIR__ . '/instance/contact.db';

try {
    // Conectare la baza de date SQLite
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creare tabel dacă nu există
    $db->exec("CREATE TABLE IF NOT EXISTS contacte (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nume TEXT NOT NULL,
        prenume TEXT NOT NULL,
        email TEXT NOT NULL,
        telefon TEXT NOT NULL,
        comentarii TEXT NOT NULL,
        nume_fisier TEXT,
        data_trimiteri TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Verificare CAPTCHA
    $captcha = $_POST['captcha'] ?? '';
    if ($captcha !== '7') {
        throw new Exception("CAPTCHA incorect. Suma 4 + 3 trebuie să fie 7.");
    }

    // Prelucrare date formular
    $nume = trim($_POST['nume']);
    $prenume = trim($_POST['prenume']);
    $email = trim($_POST['email']);
    $telefon = trim($_POST['telefon']);
    $comentarii = trim($_POST['comentarii']);
    $nume_fisier = '';

    // Validare date
    if (empty($nume) || empty($prenume) || empty($email) || empty($telefon) || empty($comentarii)) {
        throw new Exception("Toate câmpurile sunt obligatorii.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Adresa de email nu este validă.");
    }

    // Prelucrare fișier încărcat (dacă există)
    if (isset($_FILES['atasare']) && $_FILES['atasare']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['atasare'];
        
        // Verificare tip fișier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Tip de fișier neacceptat. Acceptăm doar JPEG, PNG, GIF sau PDF.");
        }
        
        // Verificare dimensiune fișier (max 2MB)
        if ($file['size'] > 2097152) {
            throw new Exception("Fișierul este prea mare. Dimensiunea maximă permisă este 2MB.");
        }
        
        // Generare nume unic pentru fișier
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nume_fisier = uniqid() . '.' . $ext;
        $upload_path = __DIR__ . '/uploads/' . $nume_fisier;
        
        // Mutare fișier în directorul de uploads
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("A apărut o eroare la încărcarea fișierului.");
        }
    }

    // Inserare în baza de date
    $stmt = $db->prepare("INSERT INTO contacte (nume, prenume, email, telefon, comentarii, nume_fisier) 
                          VALUES (:nume, :prenume, :email, :telefon, :comentarii, :nume_fisier)");
    
    $stmt->bindParam(':nume', $nume);
    $stmt->bindParam(':prenume', $prenume);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telefon', $telefon);
    $stmt->bindParam(':comentarii', $comentarii);
    $stmt->bindParam(':nume_fisier', $nume_fisier);
    
    $stmt->execute();

    // Răspuns succes
    echo json_encode([
        'status' => 'success',
        'message' => 'Mulțumim pentru mesaj! Vă vom contacta în cel mai scurt timp posibil.'
    ]);

} catch (Exception $e) {
    // Răspuns eroare
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
