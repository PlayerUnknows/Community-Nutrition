# Error checking script for PHP project
Write-Host "=== Checking PHP Files ===" -ForegroundColor Cyan

$phpErrors = 0
Get-ChildItem -Path "src" -Recurse -Filter "*.php" | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR in $($_.FullName)" -ForegroundColor Red
        Write-Host $result
        $phpErrors++
    }
}

if ($phpErrors -eq 0) {
    Write-Host "OK - All PHP files are valid" -ForegroundColor Green
} else {
    Write-Host "ERROR - Found $phpErrors PHP file(s) with errors" -ForegroundColor Red
}

Write-Host "`n=== Checking JavaScript Files ===" -ForegroundColor Cyan

$jsErrors = 0
Get-ChildItem -Path "src/script" -Filter "*.js" | ForEach-Object {
    $result = node --check $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR in $($_.FullName)" -ForegroundColor Red
        Write-Host $result
        $jsErrors++
    }
}

if ($jsErrors -eq 0) {
    Write-Host "OK - All JavaScript files are valid" -ForegroundColor Green
} else {
    Write-Host "ERROR - Found $jsErrors JavaScript file(s) with errors" -ForegroundColor Red
}

Write-Host "`n=== Checking Composer ===" -ForegroundColor Cyan
if (Test-Path "composer.json") {
    composer validate
} else {
    Write-Host "No composer.json found" -ForegroundColor Yellow
}

Write-Host "`n=== Summary ===" -ForegroundColor Cyan
$totalErrors = $phpErrors + $jsErrors
if ($totalErrors -eq 0) {
    Write-Host "OK - No errors found!" -ForegroundColor Green
    exit 0
} else {
    Write-Host "ERROR - Total errors: $totalErrors" -ForegroundColor Red
    exit 1
}
