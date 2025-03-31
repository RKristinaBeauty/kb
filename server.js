// server.js
const express = require('express');
const bodyParser = require('body-parser');
const multer = require('multer');
const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const fs = require('fs');

const app = express();
const upload = multer({ dest: 'uploads/' });

// Configurare middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(express.static('public'));

// Conectare la baza de date SQLite
const db = new sqlite3.Database('./instance/contact.db', (err) => {
    if (err) {
        console.error('Eroare la conectarea la baza de date:', err.message);
    } else {
        console.log('Conectat la baza de date SQLite');
        // Crează tabela dacă nu există
        db.run(`CREATE TABLE IF NOT EXISTS contacte (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nume TEXT NOT NULL,
            prenume TEXT NOT NULL,
            email TEXT NOT NULL,
            telefon TEXT NOT NULL,
            comentarii TEXT,
            nume_fisier TEXT,
            cale_fisier TEXT,
            data_trimiterii DATETIME DEFAULT CURRENT_TIMESTAMP
        )`);
    }
});

// Endpoint pentru trimiterea formularului
app.post('/submit-form', upload.single('atasare'), (req, res) => {
    // Verificare CAPTCHA simplă
    if (req.body.captcha !== '7') {
        return res.status(400).json({ error: 'CAPTCHA incorect. Suma 4 + 3 este 7.' });
    }

    const { nume, prenume, email, telefon, comentarii } = req.body;
    let numeFisier = null;
    let caleFisier = null;

    // Procesare fișier încărcat (dacă există)
    if (req.file) {
        numeFisier = req.file.originalname || req.file.filename;
        caleFisier = req.file.path;
    }

    // Inserare în baza de date
    const sql = `INSERT INTO contacte (nume, prenume, email, telefon, comentarii, nume_fisier, cale_fisier) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)`;
    
    db.run(sql, [nume, prenume, email, telefon, comentarii, numeFisier, caleFisier], function(err) {
        if (err) {
            console.error('Eroare la inserare:', err);
            return res.status(500).json({ error: 'Eroare la salvarea datelor.' });
        }
        
        console.log(`Date salvate cu ID: ${this.lastID}`);
        res.json({ 
            message: 'Mulțumim pentru mesaj! Vă vom contacta în cel mai scurt timp posibil.',
            status: 'success'
        });
    });
});

// Endpoint pentru a obține toate contactele (pentru administrare)
app.get('/api/contacte', (req, res) => {
    db.all('SELECT * FROM contacte ORDER BY data_trimiterii DESC', [], (err, rows) => {
        if (err) {
            return res.status(500).json({ error: err.message });
        }
        res.json(rows);
    });
});

// Pornire server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server rulează pe portul ${PORT}`);
});

// Închidere conexiune la baza de date la oprirea serverului
process.on('SIGINT', () => {
    db.close((err) => {
        if (err) {
            console.error('Eroare la închiderea bazei de date:', err.message);
        } else {
            console.log('Conexiunea la baza de date închisă.');
        }
        process.exit(0);
    });
});