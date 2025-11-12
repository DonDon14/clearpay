# Android SDK Setup Guide for Flutter

## Prerequisites

Before installing Android SDK, you need:

1. **Java JDK** (required for Android SDK)
2. **Android SDK** (can be installed via Android Studio or standalone)

## Step 1: Install Java JDK

### Download and Install Java:

1. **Download Java JDK 17 or higher:**
   - Go to: https://adoptium.net/ (recommended) or https://www.oracle.com/java/technologies/downloads/
   - Download JDK 17 or 21 for Windows x64
   - Run the installer
   - **Important:** Check "Add to PATH" during installation

2. **Verify Java Installation:**
   ```powershell
   java -version
   ```
   Should show Java version (e.g., "openjdk version 17.0.x")

3. **Set JAVA_HOME (if not automatic):**
   ```powershell
   # Find Java installation (usually in Program Files)
   # Then set:
   [System.Environment]::SetEnvironmentVariable('JAVA_HOME', 'C:\Program Files\Java\jdk-17', 'User')
   ```

## Step 2: Install Android SDK

### Option 1: Install Android Studio (Easiest - Recommended)

1. **Download Android Studio:**
   - Go to: https://developer.android.com/studio
   - Download the Windows installer
   - Run the installer

2. **During Installation:**
   - Install Android SDK
   - Install Android SDK Platform
   - Install Android Virtual Device (optional, for emulator)

3. **After Installation:**
   - Open Android Studio
   - Go through the setup wizard
   - It will automatically install the Android SDK

4. **Verify Installation:**
   ```powershell
   flutter doctor
   ```
   Should show Android toolchain as ✅

### Option 2: Command-Line Tools Only (Lighter - Recommended if you don't want Android Studio)

**Easiest way - Use the automated script:**

1. **Run the installation script:**
   ```powershell
   cd flutter_app
   .\install_android_sdk.ps1
   ```
   
   The script will:
   - Check for Java
   - Download Android SDK command-line tools
   - Install required components
   - Set environment variables
   - Configure Flutter

2. **Restart PowerShell** after installation

3. **Verify:**
   ```powershell
   flutter doctor
   ```

**Manual Installation (if script doesn't work):**

1. **Download Command-Line Tools:**
   - Go to: https://developer.android.com/studio#command-tools
   - Download "Command line tools only" for Windows
   - Extract to: `C:\Android\Sdk\cmdline-tools\latest`

2. **Set Environment Variables:**
   ```powershell
   # Set ANDROID_HOME
   [System.Environment]::SetEnvironmentVariable('ANDROID_HOME', 'C:\Android\Sdk', 'User')
   
   # Add to PATH
   $currentPath = [System.Environment]::GetEnvironmentVariable('Path', 'User')
   $newPath = "$currentPath;C:\Android\Sdk\platform-tools;C:\Android\Sdk\cmdline-tools\latest\bin"
   [System.Environment]::SetEnvironmentVariable('Path', $newPath, 'User')
   ```

3. **Install SDK Components:**
   ```powershell
   # Restart PowerShell first, then:
   sdkmanager "platform-tools" "platforms;android-33" "build-tools;33.0.2"
   
   # Accept licenses
   flutter doctor --android-licenses
   ```

## Step 3: Verify Setup

After installing Java and Android SDK:

1. **Restart your terminal/PowerShell** (to load new environment variables)

2. **Check Flutter setup:**
   ```powershell
   flutter doctor
   ```

3. **Should show:**
   ```
   [√] Android toolchain - develop for Android devices
   ```

4. **If Android toolchain still shows [X]:**
   ```powershell
   # Find your Android SDK location
   # Usually: C:\Users\Administrator\AppData\Local\Android\Sdk
   
   # Set it manually
   flutter config --android-sdk "C:\Users\Administrator\AppData\Local\Android\Sdk"
   ```

5. **Accept Android Licenses:**
   ```powershell
   flutter doctor --android-licenses
   ```
   Press `y` for each license prompt

### Build APK

Once Android SDK is installed:

```powershell
cd flutter_app
flutter build apk --release
```

The APK will be in: `flutter_app\build\app\outputs\flutter-apk\app-release.apk`

## Troubleshooting

### If ANDROID_HOME is not set automatically:

1. **Find your Android SDK location:**
   - Usually: `C:\Users\YourName\AppData\Local\Android\Sdk`
   - Or: `C:\Android\Sdk` (if custom location)

2. **Set it manually:**
   ```powershell
   flutter config --android-sdk "C:\Users\Administrator\AppData\Local\Android\Sdk"
   ```

3. **Or set environment variable:**
   ```powershell
   [System.Environment]::SetEnvironmentVariable('ANDROID_HOME', 'C:\Users\Administrator\AppData\Local\Android\Sdk', 'User')
   ```

### Accept Android Licenses

```powershell
flutter doctor --android-licenses
```

Press `y` to accept all licenses.

## Quick Commands

```powershell
# Check Flutter setup
flutter doctor

# Check detailed info
flutter doctor -v

# Accept Android licenses
flutter doctor --android-licenses

# Build release APK
flutter build apk --release

# Build debug APK (faster, larger)
flutter build apk --debug
```

## Minimum Requirements

- Android SDK Platform 33 or higher
- Android SDK Build-Tools
- Android SDK Command-line Tools

All of these are included when you install Android Studio.

