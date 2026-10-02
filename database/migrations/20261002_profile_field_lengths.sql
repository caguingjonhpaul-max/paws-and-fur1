-- Apply once after backing up paws_and_fur_db.
-- Match the limits accepted by the owner and pet forms.
ALTER TABLE users
    MODIFY full_name VARCHAR(150) NOT NULL,
    MODIFY email VARCHAR(255) NOT NULL;

ALTER TABLE pets
    MODIFY color VARCHAR(100) DEFAULT NULL;
