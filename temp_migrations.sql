-- Migration 2: Create appointments table
CREATE TABLE appointments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    therapist_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    duration INT NOT NULL DEFAULT 60,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'session',
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no-show') NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    color VARCHAR(255) NOT NULL DEFAULT '#A1966B',
    consultation_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT appointments_therapist_id_foreign FOREIGN KEY (therapist_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT appointments_patient_id_foreign FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT appointments_consultation_id_foreign FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration 3: Add calendar_token to users
ALTER TABLE users 
ADD COLUMN calendar_token VARCHAR(64) UNIQUE NULL AFTER remember_token;

-- Generate tokens for existing admins and therapists
UPDATE users 
SET calendar_token = CONCAT(
    SUBSTRING(MD5(RAND()) FROM 1 FOR 16),
    SUBSTRING(MD5(RAND()) FROM 1 FOR 16),
    SUBSTRING(MD5(RAND()) FROM 1 FOR 16),
    SUBSTRING(MD5(RAND()) FROM 1 FOR 16)
)
WHERE role IN ('admin', 'therapist');
