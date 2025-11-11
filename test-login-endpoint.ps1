Write-Host "Testing ClearPay Login Endpoint" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Endpoint: https://clearpay.infinityfreeapp.com/payer/loginPost"
Write-Host "Method: POST"
Write-Host "Content-Type: application/json"
Write-Host ""
Write-Host "Sending request..." -ForegroundColor Yellow
Write-Host ""

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "X-Requested-With" = "XMLHttpRequest"
}

$body = @{
    payer_id = "12345"
    password = "Thirdy"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "https://clearpay.infinityfreeapp.com/payer/loginPost" -Method Post -Headers $headers -Body $body -ContentType "application/json" -ErrorAction Stop
    
    Write-Host "SUCCESS! Received JSON response:" -ForegroundColor Green
    Write-Host ($response | ConvertTo-Json -Depth 10) -ForegroundColor White
} catch {
    Write-Host "ERROR occurred:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host ""
        Write-Host "Response body:" -ForegroundColor Yellow
        Write-Host $responseBody -ForegroundColor White
    }
}

Write-Host ""
Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Test completed."
Write-Host ""
Write-Host "If you see JSON response, the endpoint works!"
Write-Host "If you see HTML or error, the endpoint might be blocked."
Write-Host ""
Read-Host "Press Enter to exit"

