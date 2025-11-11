@echo off
echo Testing with minimal request (no special headers)
echo ===================================
echo.

REM Try with just the format=json parameter, no special headers
curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost?format=json" ^
  -d "payer_id=12345&password=Thirdy"

echo.
echo.
echo ===================================
pause

