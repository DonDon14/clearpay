# Install Android NDK Required for Flutter Build
# This is needed for building Flutter apps

Write-Host "=== Installing Android NDK ===" -ForegroundColor Cyan
Write-Host ""

# Set environment variables
$env:JAVA_HOME = "C:\Program Files\jdk-21.0.9"
$env:ANDROID_HOME = "C:\Android\Sdk"
$env:Path = "$env:JAVA_HOME\bin;C:\Android\Sdk\cmdline-tools\latest\bin;$env:Path"

# Ensure bin directory exists
if (-not (Test-Path "C:\Android\Sdk\cmdline-tools\latest\bin")) {
    New-Item -ItemType Directory -Path "C:\Android\Sdk\cmdline-tools\latest\bin" -Force | Out-Null
}

Write-Host "Installing Android NDK 27.0.12077973..." -ForegroundColor Yellow
Write-Host "This is a large download (~500MB) and will take 5-15 minutes..." -ForegroundColor Yellow
Write-Host "Please DO NOT cancel this process!" -ForegroundColor Red
Write-Host ""

$sdkmanager = "C:\Android\Sdk\cmdline-tools\latest\bin\sdkmanager.bat"
$sdkRoot = "C:\Android\Sdk"

# Install NDK
& $sdkmanager --sdk_root=$sdkRoot "ndk;27.0.12077973"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "=== NDK Installation Complete ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "You can now build your APK:" -ForegroundColor Yellow
    Write-Host "  flutter build apk --release" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "=== Installation Failed ===" -ForegroundColor Red
    Write-Host "Please check the error messages above and try again." -ForegroundColor Yellow
}

