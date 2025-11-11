@echo off
REM Simple single-line curl test for Windows CMD
curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost" -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" -d "{\"payer_id\":\"12345\",\"password\":\"Thirdy\"}"
echo.
pause

