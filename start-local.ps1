param(
    [string]$XamppRoot = $(if ($env:XAMPP_ROOT) { $env:XAMPP_ROOT } else { 'C:\xampp' })
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $XamppRoot -PathType Container)) {
    throw "XAMPP was not found at $XamppRoot. Run .\start-local.ps1 -XamppRoot 'C:\path\to\xampp'."
}

$xamppRoot = (Resolve-Path -LiteralPath $XamppRoot).Path
$projectRoot = (Resolve-Path -LiteralPath $PSScriptRoot).Path
$sitePath = Join-Path $xamppRoot 'htdocs\paws_and_fur'
$mysqlServer = Join-Path $xamppRoot 'mysql\bin\mysqld.exe'
$mysqlClient = Join-Path $xamppRoot 'mysql\bin\mysql.exe'
$mysqlConfig = Join-Path $xamppRoot 'mysql\bin\my.ini'
$apacheExe = Join-Path $xamppRoot 'apache\bin\httpd.exe'
$databaseConfig = Join-Path $projectRoot 'config\database.php'
$databaseExample = Join-Path $projectRoot 'config\database.example.php'
$schemaFile = Join-Path $projectRoot 'database\schema.sql'
$vaccinationMigration = Join-Path $projectRoot 'database\migrations\20261002_vaccinations.sql'

foreach ($file in @($mysqlServer, $mysqlClient, $mysqlConfig, $apacheExe, $databaseExample, $schemaFile, $vaccinationMigration)) {
    if (-not (Test-Path -LiteralPath $file -PathType Leaf)) {
        throw "Required file not found: $file"
    }
}

if (-not (Test-Path -LiteralPath $databaseConfig)) {
    Copy-Item -LiteralPath $databaseExample -Destination $databaseConfig
    Write-Output 'Created config\database.php with the default XAMPP database settings.'
}

function Get-NormalizedPath([string]$path) {
    return [IO.Path]::GetFullPath($path).TrimEnd('\').ToLowerInvariant()
}

if (Test-Path -LiteralPath $sitePath) {
    $siteItem = Get-Item -LiteralPath $sitePath -Force
    $sameDirectory = (Get-NormalizedPath $sitePath) -eq (Get-NormalizedPath $projectRoot)
    $sameJunction = $siteItem.LinkType -eq 'Junction' -and
        (Get-NormalizedPath $siteItem.Target) -eq (Get-NormalizedPath $projectRoot)
    if (-not ($sameDirectory -or $sameJunction)) {
        throw "The XAMPP site path is already used by another directory: $sitePath"
    }
} else {
    New-Item -ItemType Junction -Path $sitePath -Target $projectRoot | Out-Null
    Write-Output "Linked $sitePath to this checkout."
}

$mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue |
    Where-Object { $_.Path -eq $mysqlServer }
if (-not $mysqlRunning) {
    Start-Process -FilePath $mysqlServer `
        -ArgumentList "--defaults-file=`"$mysqlConfig`"", '--console' `
        -WorkingDirectory (Split-Path $mysqlServer) -WindowStyle Hidden
}

$mysqlReady = $false
$mysqlError = ''
for ($attempt = 0; $attempt -lt 20; $attempt++) {
    # Windows PowerShell treats native stderr as a terminating error when
    # ErrorActionPreference is Stop. A refused connection is expected while
    # MariaDB starts, so capture it and keep retrying.
    $previousErrorActionPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $probe = & $mysqlClient --user=root --batch --skip-column-names `
            --connect-timeout=1 --execute='SELECT 1' 2>&1
        $probeExitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }
    if ($probeExitCode -eq 0 -and $probe -eq '1') {
        $mysqlReady = $true
        break
    }
    $mysqlError = ($probe | Out-String).Trim()
    Start-Sleep -Milliseconds 500
}
if (-not $mysqlReady) {
    $mysqlLog = Join-Path $xamppRoot 'mysql\data\mysql_error.log'
    $logHint = if (Test-Path -LiteralPath $mysqlLog) { " Check $mysqlLog for the startup error." } else { '' }
    throw "MariaDB did not start or accept the default XAMPP root login. $mysqlError$logHint Open the XAMPP Control Panel and try starting MySQL there."
}

$databaseExists = & $mysqlClient --user=root --batch --skip-column-names `
    --execute="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'paws_and_fur_db'"
if ($LASTEXITCODE -ne 0) {
    throw 'Could not inspect the local MariaDB databases.'
}
if (-not $databaseExists) {
    Get-Content -LiteralPath $schemaFile -Raw | & $mysqlClient --user=root
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not import database\schema.sql. Check MariaDB in the XAMPP Control Panel.'
    }
    Write-Output 'Created the empty paws_and_fur_db database.'
} else {
    $tableCount = & $mysqlClient --user=root --batch --skip-column-names `
        --execute="SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'paws_and_fur_db' AND TABLE_NAME IN ('users', 'pets', 'appointments')"
    if ($LASTEXITCODE -ne 0 -or $tableCount -ne '3') {
        throw 'paws_and_fur_db exists but is missing application tables. See database\README.md before changing existing data.'
    }
}

$vaccinationsTable = & $mysqlClient --user=root --batch --skip-column-names `
    --execute="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'paws_and_fur_db' AND TABLE_NAME = 'vaccinations'"
if ($LASTEXITCODE -ne 0) {
    throw 'Could not inspect the vaccination table in the local database.'
}
if (-not $vaccinationsTable) {
    Get-Content -LiteralPath $vaccinationMigration -Raw |
        & $mysqlClient --user=root paws_and_fur_db
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not add the vaccinations table. Existing accounts and appointments were not replaced.'
    }
    Write-Output 'Added the vaccinations table to the existing database.'
}

$apacheRunning = Get-Process httpd -ErrorAction SilentlyContinue |
    Where-Object { $_.Path -eq $apacheExe }
if (-not $apacheRunning) {
    Start-Process -FilePath $apacheExe `
        -WorkingDirectory (Split-Path $apacheExe) -WindowStyle Hidden
}

$url = 'http://localhost/paws_and_fur/auth/login.php'
for ($attempt = 0; $attempt -lt 20; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 2
        if ($response.StatusCode -eq 200 -and $response.Content -match 'PAWS AND FUR') {
            Write-Output 'Paws and Fur is running at http://localhost/paws_and_fur/'
            exit 0
        }
    } catch {
        Start-Sleep -Milliseconds 500
    }
}

$apacheLog = Join-Path $xamppRoot 'apache\logs\error.log'
$logHint = if (Test-Path -LiteralPath $apacheLog) { " See $apacheLog for the Apache error." } else { '' }
throw "The site did not respond at $url. Check Apache in the XAMPP Control Panel and whether port 80 is in use.$logHint"
