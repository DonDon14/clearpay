# Install Required Android SDK Components
# Run this after Android SDK command-line tools are installed

Write-Host "=== Installing Android SDK Components ===" -ForegroundColor Cyan
Write-Host ""

# Set environment variables
$env:JAVA_HOME = "C:\Program Files\jdk-21.0.9"
$env:ANDROID_HOME = "C:\Android\Sdk"
$env:Path = "$env:JAVA_HOME\bin;C:\Android\Sdk\cmdline-tools\latest\bin;$env:Path"

# Ensure bin directory exists
if (-not (Test-Path "C:\Android\Sdk\cmdline-tools\latest\bin")) {
    New-Item -ItemType Directory -Path "C:\Android\Sdk\cmdline-tools\latest\bin" -Force | Out-Null
    Write-Host "Created bin directory" -ForegroundColor Yellow
}

Write-Host "Installing required components..." -ForegroundColor Yellow
Write-Host "This will take 5-10 minutes..." -ForegroundColor Yellow
Write-Host ""

# Install components
$sdkmanager = "C:\Android\Sdk\cmdline-tools\latest\bin\sdkmanager.bat"
$sdkRoot = "C:\Android\Sdk"

# Accept licenses first (non-interactive)
Write-Host "Accepting licenses..." -ForegroundColor Yellow
$yes = "y`n" * 10
$yes | & $sdkmanager --sdk_root=$sdkRoot --licenses 2>&1 | Out-Null

# Install required packages
Write-Host ""
Write-Host "Installing platform-tools..." -ForegroundColor Yellow
& $sdkmanager --sdk_root=$sdkRoot "platform-tools"

Write-Host "Installing Android SDK Platform 33..." -ForegroundColor Yellow
& $sdkmanager --sdk_root=$sdkRoot "platforms;android-33"

Write-Host "Installing Build Tools 33.0.2..." -ForegroundColor Yellow
& $sdkmanager --sdk_root=$sdkRoot "build-tools;33.0.2"

Write-Host ""
Write-Host "=== Installation Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "Configuring Flutter..." -ForegroundColor Yellow
flutter config --android-sdk $sdkRoot

Write-Host ""
Write-Host "Verifying installation..." -ForegroundColor Yellow
flutter doctor

Write-Host ""
Write-Host "If Android toolchain shows [X], run:" -ForegroundColor Yellow
Write-Host "  flutter doctor --android-licenses" -ForegroundColor White
Write-Host "  (Press 'y' for each license)" -ForegroundColor White

