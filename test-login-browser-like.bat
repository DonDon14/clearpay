@echo off
echo Testing with browser-like request (simpler headers)
echo ===================================
echo.

REM Try with minimal headers, like a browser would send
curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost?format=json" ^
  -H "Accept: application/json" ^
  -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36" ^
  -d "payer_id=12345&password=Thirdy&format=json"

echo.
echo.
echo ===================================
pause

