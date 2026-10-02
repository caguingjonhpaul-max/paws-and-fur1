# Paws and Fur Clinic

## Run locally with XAMPP on Windows

Install XAMPP with Apache, PHP, and MariaDB. Clone this repository anywhere,
including directly into `C:\xampp\htdocs\paws_and_fur`. The setup script creates
a junction into XAMPP's `htdocs` folder when the checkout is elsewhere.

From PowerShell in the project directory, run:

```powershell
.\start-local.ps1
```

If XAMPP is installed elsewhere, pass its directory:

```powershell
.\start-local.ps1 -XamppRoot 'D:\xampp'
```

If PowerShell blocks script execution, use
`powershell -ExecutionPolicy Bypass -File .\start-local.ps1`.

The script starts MariaDB and Apache, creates `config/database.php` with default
XAMPP settings if it is missing, and imports `database/schema.sql` only when the
application database does not exist. It checks the login page before reporting
success. Open [http://localhost/paws_and_fur/](http://localhost/paws_and_fur/).
If it reports that Apache did not respond, check the XAMPP Control Panel and
whether another program is using port 80. If creating the junction is denied,
run PowerShell as Administrator once and rerun the script.
If it reports that MariaDB did not start, open the XAMPP Control Panel and
start MySQL there to see its error. The MariaDB startup log is usually at
`C:\xampp\mysql\data\mysql_error.log`; a different installation root changes
that path.

Git contains the database structure but no account, pet, or appointment records.
For the same records on another PC, export `paws_and_fur_db` from the first PC
and import it on the second PC. Do that before starting with an empty database.

Database setup and migrations are documented in [database/README.md](database/README.md).
The local `config/database.php` connection file is excluded from Git. If your
MariaDB root account has a password or uses a different database name, configure
the connection and import the schema manually as described in the database guide.
