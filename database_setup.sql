-- OMSYSTEM V2 Database Setup
CREATE DATABASE IF NOT EXISTS omsystem_v2;
USE omsystem_v2;

-- Users table (no password hashing as requested)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Stored in plaintext per requirements
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'doctor', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    identification VARCHAR(20) NOT NULL, -- RUT/CI/DNI
    birth_date DATE NOT NULL,
    age INT GENERATED ALWAYS AS (TIMESTAMPDIFF(YEAR, birth_date, CURDATE())) STORED,
    city VARCHAR(50) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    insurance VARCHAR(50), -- Previsión
    company VARCHAR(100), -- Empresa
    position VARCHAR(100), -- Cargo
    drug_allergies TEXT,
    food_allergies TEXT,
    current_medications TEXT,
    family_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (identification)
);

-- Medical Diagnoses
CREATE TABLE diagnoses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    cie10_code VARCHAR(10),
    cie10_description VARCHAR(255),
    diagnosis_details TEXT NOT NULL,
    date_created DATE NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Medications
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    medication_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    prescribed_by INT NOT NULL,
    minsal_code VARCHAR(20),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (prescribed_by) REFERENCES users(id)
);

-- Treatments
CREATE TABLE treatments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    treatment_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled') NOT NULL,
    created_by INT NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Patient Documents
CREATE TABLE patient_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    document_name VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Insert initial admin user (Oliver/Moana)
INSERT INTO users (username, password, full_name, role) 
VALUES ('oliver', 'Moana', 'Oliver Admin', 'admin');