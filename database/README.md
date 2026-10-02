# Database setup

This application uses MariaDB through PHP `mysqli` and expects a database named
`paws_and_fur_db`. Connection settings are local to `config/database.php`, which
is excluded from Git.

- For a new database, import `schema.sql` once.
- For an existing database, import `migrations/20261002_vaccinations.sql` once
  to add vaccination records without changing existing pet or appointment data.
- For a database created before October 2026, back up the data first, then import
  `migrations/20261002_appointment_integrity.sql` and
  `migrations/20261002_profile_field_lengths.sql` once, in that order. Do not
  run `schema.sql` over existing data.

The migration links appointments to their pet and assigned staff member, adds
indexes for record and schedule queries, and allows only one active appointment
per date and time. `Pending`, `Approved`, and `Confirmed` occupy a slot;
`Rejected`, `Completed`, and `Cancelled` do not.

Vaccinations store a dose date and an optional next due date. Schedule views
show only the latest dose for each pet and vaccine; older doses remain in the
history. Staff and administrators can record and edit doses. Clients can view
records for their own pets.

Before applying the migration, resolve any appointments whose pet is missing,
whose owner differs from the pet's owner, or whose assigned staff user is
missing. Also resolve any date/time slot with more than one active appointment.
