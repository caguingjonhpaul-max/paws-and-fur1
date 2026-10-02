# Paws and Fur Clinic

## Run locally with XAMPP on Windows

The project lives in this Git checkout. `start-local.ps1` creates a persistent
junction at `C:\xampp\htdocs\paws_and_fur` pointing to the checkout, then starts
XAMPP Apache and MariaDB if needed.

For a fresh checkout, copy `config/database.example.php` to
`config/database.php`, edit the credentials if needed, and import
`database/schema.sql` into MariaDB. Then, from PowerShell in the project
directory, run:

```powershell
.\start-local.ps1
```

Open [http://localhost/paws_and_fur/](http://localhost/paws_and_fur/).
If creating the junction is denied, run PowerShell as Administrator once and
run the script again. After a reboot, run the script or start Apache and MySQL
from the XAMPP Control Panel; the junction remains in place.

Database setup and migrations are documented in [database/README.md](database/README.md).
The local `config/database.php` connection file is excluded from Git.
