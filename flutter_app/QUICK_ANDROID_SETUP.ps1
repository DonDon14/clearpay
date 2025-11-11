# Quick Android SDK Setup Script for Flutter
# Run this script as Administrator if needed

Write-Host "=== Flutter Android SDK Setup ===" -ForegroundColor Cyan
Write-Host ""

# Check Java
Write-Host "Checking Java installation..." -ForegroundColor Yellow
$javaInstalled = Get-Command java -ErrorAction SilentlyContinue
if (-not $javaInstalled) {
    Write-Host "❌ Java is NOT installed" -ForegroundColor Red
    Write-Host "Please install Java JDK 17+ from: https://adoptium.net/" -ForegroundColor Yellow
    Write-Host "Then run this script again." -ForegroundColor Yellow
    exit 1
} else {
    $javaVersion = java -version 2>&1 | Select-Object -First 1
    Write-Host "✅ Java found: $javaVersion" -ForegroundColor Green
}

# Check Android SDK
Write-Host ""
Write-Host "Checking Android SDK..." -ForegroundColor Yellow

$sdkPaths = @(
    "$env:LOCALAPPDATA\Android\Sdk",
    "$env:USERPROFILE\AppData\Local\Android\Sdk",
    "C:\Android\Sdk",
    "$env:ANDROID_HOME"
)

$sdkFound = $false
$sdkPath = $null

foreach ($path in $sdkPaths) {
    if ($path -and (Test-Path $path)) {
        $sdkPath = $path
        $sdkFound = $true
        break
    }
}

if (-not $sdkFound) {
    Write-Host "❌ Android SDK NOT found" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Android Studio from:" -ForegroundColor Yellow
    Write-Host "https://developer.android.com/studio" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Or install command-line tools only:" -ForegroundColor Yellow
    Write-Host "https://developer.android.com/studio#command-tools" -ForegroundColor Cyan
    exit 1
} else {
    Write-Host "✅ Android SDK found at: $sdkPath" -ForegroundColor Green
    
    # Set ANDROID_HOME if not set
    if (-not $env:ANDROID_HOME) {
        Write-Host ""
        Write-Host "Setting ANDROID_HOME environment variable..." -ForegroundColor Yellow
        [System.Environment]::SetEnvironmentVariable('ANDROID_HOME', $sdkPath, 'User')
        $env:ANDROID_HOME = $sdkPath
        Write-Host "✅ ANDROID_HOME set to: $sdkPath" -ForegroundColor Green
    }
    
    # Configure Flutter
    Write-Host ""
    Write-Host "Configuring Flutter..." -ForegroundColor Yellow
    flutter config --android-sdk $sdkPath
    Write-Host "✅ Flutter configured" -ForegroundColor Green
}

# Check Flutter
Write-Host ""
Write-Host "Running flutter doctor..." -ForegroundColor Yellow
flutter doctor

Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. If Android licenses are needed, run: flutter doctor --android-licenses" -ForegroundColor White
Write-Host "2. Build your APK: flutter build apk --release" -ForegroundColor White

