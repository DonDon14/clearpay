# Install Android SDK Command-Line Tools Only (No Android Studio)
# Run this script as Administrator

Write-Host "=== Installing Android SDK (Command-Line Tools Only) ===" -ForegroundColor Cyan
Write-Host ""

# Reload PATH to ensure Java is found
$env:Path = [System.Environment]::GetEnvironmentVariable('Path', 'Machine') + ';' + [System.Environment]::GetEnvironmentVariable('Path', 'User')

# Check if Java is installed
Write-Host "Checking Java..." -ForegroundColor Yellow

# Try multiple ways to find Java
$javaPath = $null

# Method 1: Check if java command is available
$javaInstalled = Get-Command java -ErrorAction SilentlyContinue
if ($javaInstalled) {
    $javaPath = $javaInstalled.Source
}

# Method 2: Check common installation paths
if (-not $javaPath) {
    $commonPaths = @(
        "C:\Program Files\Java\jdk-21\bin\java.exe",
        "C:\Program Files\Java\jdk-17\bin\java.exe",
        "C:\Program Files\jdk-21.0.9\bin\java.exe",
        "C:\Program Files\Eclipse Adoptium\jdk-21.0.9.10-hotspot\bin\java.exe"
    )
    
    foreach ($path in $commonPaths) {
        if (Test-Path $path) {
            $javaPath = $path
            # Add to PATH for this session
            $javaDir = Split-Path $path
            $env:Path = "$javaDir;$env:Path"
            break
        }
    }
}

if (-not $javaPath) {
    Write-Host "❌ Java is NOT found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Java JDK 17+ first:" -ForegroundColor Yellow
    Write-Host "1. Download from: https://adoptium.net/" -ForegroundColor White
    Write-Host "2. Install JDK 17 or 21 for Windows x64" -ForegroundColor White
    Write-Host "3. Make sure to check 'Add to PATH' during installation" -ForegroundColor White
    Write-Host "4. Restart PowerShell and run this script again" -ForegroundColor White
    exit 1
}

# Verify Java works
try {
    $javaVersion = & $javaPath -version 2>&1 | Select-Object -First 1
    Write-Host "✅ Java found: $javaVersion" -ForegroundColor Green
    Write-Host "   Location: $javaPath" -ForegroundColor Gray
} catch {
    Write-Host "❌ Java found but cannot execute!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Set installation directory
$androidHome = "C:\Android"
$sdkPath = "$androidHome\Sdk"
$cmdlineToolsPath = "$sdkPath\cmdline-tools\latest"

Write-Host "Installation directory: $androidHome" -ForegroundColor Yellow
Write-Host ""

# Create directory
if (-not (Test-Path $androidHome)) {
    Write-Host "Creating directory: $androidHome" -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $androidHome -Force | Out-Null
}

# Check if already installed
if (Test-Path "$cmdlineToolsPath\bin\sdkmanager.bat") {
    Write-Host "⚠️  Android SDK command-line tools already exist at: $cmdlineToolsPath" -ForegroundColor Yellow
    $overwrite = Read-Host "Do you want to reinstall? (y/N)"
    if ($overwrite -ne "y" -and $overwrite -ne "Y") {
        Write-Host "Skipping download..." -ForegroundColor Yellow
        $skipDownload = $true
    } else {
        Write-Host "Removing existing installation..." -ForegroundColor Yellow
        Remove-Item -Path "$sdkPath\cmdline-tools" -Recurse -Force -ErrorAction SilentlyContinue
        $skipDownload = $false
    }
} else {
    $skipDownload = $false
}

# Download command-line tools
if (-not $skipDownload) {
    Write-Host ""
    Write-Host "Downloading Android SDK Command-Line Tools..." -ForegroundColor Yellow
    Write-Host "This may take a few minutes..." -ForegroundColor Yellow
    
    $downloadUrl = "https://dl.google.com/android/repository/commandlinetools-win-11076708_latest.zip"
    $zipFile = "$env:TEMP\android-cmdline-tools.zip"
    
    try {
        # Download with progress indicator
        Write-Host "Download URL: $downloadUrl" -ForegroundColor Gray
        Write-Host "Saving to: $zipFile" -ForegroundColor Gray
        Write-Host ""
        Write-Host "Downloading... (This may take 2-5 minutes depending on your connection)" -ForegroundColor Yellow
        Write-Host "Tip: Open another PowerShell window and run: .\check_download_progress.ps1" -ForegroundColor Cyan
        Write-Host ""
        
        # Show progress by checking file size periodically
        $job = Start-Job -ScriptBlock {
            param($url, $file)
            Invoke-WebRequest -Uri $url -OutFile $file -UseBasicParsing
        } -ArgumentList $downloadUrl, $zipFile
        
        # Monitor progress
        while ($job.State -eq 'Running') {
            if (Test-Path $zipFile) {
                $sizeMB = [math]::Round((Get-Item $zipFile).Length / 1MB, 2)
                Write-Host "`rDownloaded: $sizeMB MB" -NoNewline -ForegroundColor Green
            }
            Start-Sleep -Milliseconds 500
        }
        
        Receive-Job $job
        Remove-Job $job
        Write-Host ""
        Write-Host "✅ Download complete" -ForegroundColor Green
        
        # Extract
        Write-Host "Extracting..." -ForegroundColor Yellow
        $extractPath = "$env:TEMP\android-cmdline-tools"
        if (Test-Path $extractPath) {
            Remove-Item -Path $extractPath -Recurse -Force
        }
        Expand-Archive -Path $zipFile -DestinationPath $extractPath -Force
        
        # Move to correct location
        Write-Host "Installing..." -ForegroundColor Yellow
        if (-not (Test-Path "$sdkPath\cmdline-tools")) {
            New-Item -ItemType Directory -Path "$sdkPath\cmdline-tools" -Force | Out-Null
        }
        Move-Item -Path "$extractPath\cmdline-tools\*" -Destination "$sdkPath\cmdline-tools\latest" -Force
        
        # Cleanup
        Remove-Item -Path $zipFile -Force -ErrorAction SilentlyContinue
        Remove-Item -Path $extractPath -Recurse -Force -ErrorAction SilentlyContinue
        
        Write-Host "✅ Installation complete" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error downloading: $_" -ForegroundColor Red
        Write-Host ""
        Write-Host "Manual download:" -ForegroundColor Yellow
        Write-Host "1. Go to: https://developer.android.com/studio#command-tools" -ForegroundColor White
        Write-Host "2. Download 'Command line tools only' for Windows" -ForegroundColor White
        Write-Host "3. Extract to: $sdkPath\cmdline-tools\latest" -ForegroundColor White
        exit 1
    }
}

# Set environment variables
Write-Host ""
Write-Host "Setting environment variables..." -ForegroundColor Yellow

# Set ANDROID_HOME
[System.Environment]::SetEnvironmentVariable('ANDROID_HOME', $sdkPath, 'User')
$env:ANDROID_HOME = $sdkPath
Write-Host "✅ ANDROID_HOME = $sdkPath" -ForegroundColor Green

# Add to PATH
$currentPath = [System.Environment]::GetEnvironmentVariable('Path', 'User')
$pathsToAdd = @(
    "$sdkPath\platform-tools",
    "$sdkPath\tools\bin",
    "$cmdlineToolsPath\bin"
)

foreach ($path in $pathsToAdd) {
    if ($currentPath -notlike "*$path*") {
        $currentPath = "$currentPath;$path"
        Write-Host "✅ Added to PATH: $path" -ForegroundColor Green
    }
}

[System.Environment]::SetEnvironmentVariable('Path', $currentPath, 'User')
$env:Path = [System.Environment]::GetEnvironmentVariable('Path', 'Machine') + ";" + [System.Environment]::GetEnvironmentVariable('Path', 'User')

# Install required SDK components
Write-Host ""
Write-Host "Installing required SDK components..." -ForegroundColor Yellow
Write-Host "This will take several minutes..." -ForegroundColor Yellow

$sdkmanager = "$cmdlineToolsPath\bin\sdkmanager.bat"

# Accept licenses first
Write-Host "Accepting licenses..." -ForegroundColor Yellow
& $sdkmanager --licenses | ForEach-Object {
    if ($_ -match "\(y/N\)") {
        Write-Host "y" | & $sdkmanager --licenses
    }
}

# Install required packages
$packages = @(
    "platform-tools",
    "platforms;android-33",
    "build-tools;33.0.2",
    "cmdline-tools;latest"
)

foreach ($package in $packages) {
    Write-Host "Installing $package..." -ForegroundColor Yellow
    & $sdkmanager $package --sdk_root=$sdkPath
}

# Configure Flutter
Write-Host ""
Write-Host "Configuring Flutter..." -ForegroundColor Yellow
flutter config --android-sdk $sdkPath

# Verify
Write-Host ""
Write-Host "=== Installation Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Please RESTART your PowerShell/terminal for environment variables to take effect." -ForegroundColor Yellow
Write-Host ""
Write-Host "Then run:" -ForegroundColor Yellow
Write-Host "  flutter doctor" -ForegroundColor White
Write-Host ""
Write-Host "If Android toolchain shows [X], run:" -ForegroundColor Yellow
Write-Host "  flutter doctor --android-licenses" -ForegroundColor White
Write-Host "  (Press 'y' for each license)" -ForegroundColor White
Write-Host ""
Write-Host "Then build your APK:" -ForegroundColor Yellow
Write-Host "  cd flutter_app" -ForegroundColor White
Write-Host "  flutter build apk --release" -ForegroundColor White

