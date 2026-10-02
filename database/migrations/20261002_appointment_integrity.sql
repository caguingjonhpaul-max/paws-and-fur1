-- Apply once to paws_and_fur_db after taking a backup.
-- Existing appointment rows must reference their pet's owner and have no
-- duplicate active appointment date/time slots.

ALTER TABLE pets
    ADD UNIQUE KEY uq_pets_id_owner (pet_id, user_id);

ALTER TABLE appointments
    ADD INDEX idx_appointments_pet_owner (pet_id, user_id),
    ADD INDEX idx_appointments_staff (staff_id),
    ADD INDEX idx_appointments_slot_lookup (appointment_date, appointment_time, status),
    ADD INDEX idx_appointments_user_schedule (user_id, appointment_date, appointment_time),
    ADD COLUMN active_slot TINYINT
        GENERATED ALWAYS AS (
            CASE
                WHEN status IN ('Pending', 'Approved', 'Confirmed') THEN 1
                ELSE NULL
            END
        ) PERSISTENT,
    ADD UNIQUE KEY uq_appointments_active_slot
        (appointment_date, appointment_time, active_slot);

ALTER TABLE appointments
    ADD CONSTRAINT fk_appointments_pet_owner
        FOREIGN KEY (pet_id, user_id)
        REFERENCES pets (pet_id, user_id)
        ON DELETE RESTRICT,
    ADD CONSTRAINT fk_appointments_staff
        FOREIGN KEY (staff_id)
        REFERENCES users (user_id)
        ON DELETE SET NULL;
