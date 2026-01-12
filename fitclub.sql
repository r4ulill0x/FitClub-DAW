-- ===================================================
-- SCRIPT COMPLET BAZĂ DE DATE FITCLUB
-- Include: Utilizatori, Abonamente, Clase, Rezervări, Statistici și Contact
-- ===================================================

USE your_db;

-- 1. TABELA ABONAMENTE
CREATE TABLE IF NOT EXISTS Abonamente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    descriere TEXT,
    pret DECIMAL(10, 2) NOT NULL,
    durata_zile INT NOT NULL
);

-- 2. TABELA UTILIZATORI 
CREATE TABLE IF NOT EXISTS Utilizatori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    parola VARCHAR(255) NOT NULL,
    rol ENUM('client', 'antrenor', 'admin') NOT NULL DEFAULT 'client',
    id_abonament INT DEFAULT NULL,
    data_expirare DATE DEFAULT NULL,
    2fa_code VARCHAR(6) DEFAULT NULL,
    2fa_expiry DATETIME DEFAULT NULL,
    data_inregistrare TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_abonament) REFERENCES Abonamente(id) ON DELETE SET NULL
);

-- 3. TABELA CLASE FITNESS
CREATE TABLE IF NOT EXISTS Clase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume_clasa VARCHAR(100) NOT NULL,
    descriere TEXT,
    id_antrenor INT,
    FOREIGN KEY (id_antrenor) REFERENCES Utilizatori(id) ON DELETE SET NULL
);

-- 4. TABELA ORAR 
CREATE TABLE IF NOT EXISTS Program_Clase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_clasa INT NOT NULL,
    zi_saptamana ENUM('Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica') NOT NULL,
    ora_inceput TIME NOT NULL,
    ora_sfarsit TIME NOT NULL,
    locuri_disponibile INT NOT NULL,
    FOREIGN KEY (id_clasa) REFERENCES Clase(id) ON DELETE CASCADE
);

-- 5. TABELA REZERVĂRI
CREATE TABLE IF NOT EXISTS Rezervari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    id_program_clasa INT NOT NULL,
    data_rezervare TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmata', 'anulata') NOT NULL DEFAULT 'confirmata',
    FOREIGN KEY (id_client) REFERENCES Utilizatori(id) ON DELETE CASCADE,
    FOREIGN KEY (id_program_clasa) REFERENCES Program_Clase(id) ON DELETE CASCADE,
    UNIQUE KEY (id_client, id_program_clasa)
);

-- 6. TABELA CONTACT
CREATE TABLE IF NOT EXISTS Mesaje_Contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subiect VARCHAR(200),
    mesaj TEXT NOT NULL,
    data_trimitere TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
