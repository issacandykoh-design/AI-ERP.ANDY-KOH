@echo off
echo ==========================================
echo   Craveva Hub - BMad Agile Dev Environment
echo ==========================================
echo.
echo Starting Laravel Server...
start "Laravel Core" php artisan serve --port=8000

echo Starting Python AI Service...
cd "AI Enterprise Business"
start "AI Service" python main.py
cd ..

echo Starting Frontend for AI Service...
cd "public/ai"
start "AI Frontend" node server.js
cd ../..

echo.
echo Services are running!
echo - Laravel: http://localhost:8000
echo - AI Backend: http://localhost:5000 (or defined port)
echo - AI Frontend: http://localhost:8080
echo.
pause
