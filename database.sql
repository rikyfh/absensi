-- Opsional: tabel juga dibuat otomatis oleh api.php saat pertama dibuka.
CREATE TABLE IF NOT EXISTS students(id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(30) UNIQUE NOT NULL, name VARCHAR(100) NOT NULL, descriptor MEDIUMTEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS attendance(id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, date DATE NOT NULL, time_in TIME, time_out TIME NULL, UNIQUE(student_id,date), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS settings(k VARCHAR(50) PRIMARY KEY, v TEXT);
