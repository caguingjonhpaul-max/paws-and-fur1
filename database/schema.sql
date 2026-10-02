-- Schema for a fresh installation. Do not run this over an existing database.
CREATE DATABASE IF NOT EXISTS paws_and_fur_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE paws_and_fur_db;

CREATE TABLE users (
    user_id INT NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Administrator', 'Staff', 'Client') NOT NULL DEFAULT 'Client',
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pets (
    pet_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    pet_name VARCHAR(100) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(100) DEFAULT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    birth_date DATE DEFAULT NULL,
    color VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pet_id),
    UNIQUE KEY uq_pets_id_owner (pet_id, user_id),
    KEY idx_pets_user (user_id),
    CONSTRAINT fk_pets_owner FOREIGN KEY (user_id)
        REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointments (
    appointment_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    staff_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    rejection_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active_slot TINYINT GENERATED ALWAYS AS (
        CASE WHEN status IN ('Pending', 'Approved', 'Confirmed') THEN 1 ELSE NULL END
    ) PERSISTENT,
    PRIMARY KEY (appointment_id),
    UNIQUE KEY uq_appointments_active_slot (appointment_date, appointment_time, active_slot),
    KEY idx_appointments_user (user_id),
    KEY idx_appointments_pet_owner (pet_id, user_id),
    KEY idx_appointments_staff (staff_id),
    KEY idx_appointments_slot_lookup (appointment_date, appointment_time, status),
    KEY idx_appointments_user_schedule (user_id, appointment_date, appointment_time),
    CONSTRAINT fk_appointments_user FOREIGN KEY (user_id)
        REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_pet_owner FOREIGN KEY (pet_id, user_id)
        REFERENCES pets (pet_id, user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_staff FOREIGN KEY (staff_id)
        REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vaccinations (
    vaccination_id INT NOT NULL AUTO_INCREMENT,
    pet_id INT NOT NULL,
    vaccine_name VARCHAR(120) NOT NULL,
    administered_on DATE NOT NULL,
    next_due_on DATE DEFAULT NULL,
    administered_by VARCHAR(150) DEFAULT NULL,
    notes VARCHAR(1000) DEFAULT NULL,
    recorded_by INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (vaccination_id),
    KEY idx_vaccinations_pet_vaccine_date (pet_id, vaccine_name, administered_on),
    KEY idx_vaccinations_due (next_due_on),
    CONSTRAINT fk_vaccinations_pet FOREIGN KEY (pet_id)
        REFERENCES pets (pet_id) ON DELETE CASCADE,
    CONSTRAINT fk_vaccinations_recorder FOREIGN KEY (recorded_by)
        REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
