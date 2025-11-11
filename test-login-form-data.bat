@echo off
REM Test with form-data instead of JSON (might bypass security)
echo Testing with form-data (application/x-www-form-urlencoded)
echo ===================================
echo.

curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost?format=json" -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" -d "payer_id=12345&password=Thirdy&format=json"

echo.
echo.
echo ===================================
echo If this works, we can update Flutter app to use form-data instead of JSON
echo.
pause

