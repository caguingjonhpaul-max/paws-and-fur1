-- Apply once to an existing paws_and_fur_db database.
-- New installations already include this table in schema.sql.
CREATE TABLE IF NOT EXISTS vaccinations (
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
