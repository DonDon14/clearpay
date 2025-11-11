@echo off
echo Testing ClearPay Login Endpoint
echo ===================================
echo.
echo Endpoint: https://clearpay.infinityfreeapp.com/payer/loginPost
echo Method: POST
echo Content-Type: application/json
echo.
echo Sending request...
echo.

curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost" -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" -d "{\"payer_id\":\"12345\",\"password\":\"Thirdy\"}"

echo.
echo.
echo ===================================
echo Test completed.
echo.
echo If you see JSON response with "success": true/false, it works!
echo If you see HTML or error, the endpoint might be blocked.
echo.
pause

