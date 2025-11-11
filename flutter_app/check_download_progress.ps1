# Monitor Android SDK Download Progress
# Run this in a separate PowerShell window while the installation script is running

$zipFile = "$env:TEMP\android-cmdline-tools.zip"
$expectedSize = 150 * 1024 * 1024  # ~150 MB

Write-Host "=== Monitoring Download Progress ===" -ForegroundColor Cyan
Write-Host "File: $zipFile" -ForegroundColor Yellow
Write-Host ""

while ($true) {
    if (Test-Path $zipFile) {
        $file = Get-Item $zipFile
        $sizeMB = [math]::Round($file.Length / 1MB, 2)
        $percent = [math]::Round(($file.Length / $expectedSize) * 100, 1)
        $lastModified = $file.LastWriteTime
        
        Write-Host "Downloaded: $sizeMB MB ($percent%) - Last updated: $lastModified" -ForegroundColor Green
        
        if ($file.Length -ge $expectedSize * 0.95) {
            Write-Host ""
            Write-Host "✅ Download appears complete!" -ForegroundColor Green
            break
        }
    } else {
        Write-Host "Waiting for download to start..." -ForegroundColor Yellow
    }
    
    Start-Sleep -Seconds 2
}

