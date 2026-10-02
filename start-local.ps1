$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path -LiteralPath $PSScriptRoot).Path
$xamppRoot = 'C:\xampp'
$siteLink = Join-Path $xamppRoot 'htdocs\paws_and_fur'
$mysqlExe = Join-Path $xamppRoot 'mysql\bin\mysqld.exe'
$apacheExe = Join-Path $xamppRoot 'apache\bin\httpd.exe'
$databaseConfig = Join-Path $projectRoot 'config\database.php'

if (-not (Test-Path -LiteralPath $databaseConfig)) {
    throw 'Create config\database.php from config\database.example.php before starting the app.'
}

foreach ($executable in @($mysqlExe, $apacheExe)) {
    if (-not (Test-Path -LiteralPath $executable)) {
        throw "XAMPP executable not found: $executable"
    }
}

if (-not (Test-Path -LiteralPath $siteLink)) {
    New-Item -ItemType Junction -Path $siteLink -Target $projectRoot | Out-Null
} else {
    $existingLink = Get-Item -LiteralPath $siteLink -Force
    if ($existingLink.LinkType -ne 'Junction' -or
        [IO.Path]::GetFullPath($existingLink.Target) -ne [IO.Path]::GetFullPath($projectRoot)) {
        throw "The XAMPP path already exists and does not point to this project: $siteLink"
    }
}

$mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue |
    Where-Object { $_.Path -eq $mysqlExe }
if (-not $mysqlRunning) {
    Start-Process -FilePath $mysqlExe `
        -ArgumentList "--defaults-file=$xamppRoot\mysql\bin\my.ini", '--console' `
        -WorkingDirectory (Split-Path $mysqlExe) -WindowStyle Hidden
}

$apacheRunning = Get-Process httpd -ErrorAction SilentlyContinue |
    Where-Object { $_.Path -eq $apacheExe }
if (-not $apacheRunning) {
    Start-Process -FilePath $apacheExe `
        -WorkingDirectory (Split-Path $apacheExe) -WindowStyle Hidden
}

$url = 'http://localhost/paws_and_fur/'
for ($attempt = 0; $attempt -lt 10; $attempt++) {
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 2
        if ($response.StatusCode -eq 200) {
            Write-Output "Paws and Fur is running at $url"
            exit 0
        }
    } catch {
        Start-Sleep -Milliseconds 500
    }
}

throw "The app did not respond at $url. Check that Apache and MySQL started in XAMPP."
