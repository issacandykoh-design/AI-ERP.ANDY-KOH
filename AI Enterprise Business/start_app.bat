@echo off
echo Starting Craveva AI Enterprise Business...
echo.

echo Installing Python dependencies...
pip install -r requirements.txt

echo.
echo Installing Node.js dependencies...
cd frontend
call npm install

echo.
echo Starting backend server...
cd ..
start "Backend Server" python web_api.py

echo.
echo Waiting for backend to start...
timeout /t 3 /nobreak > nul

echo Starting frontend server...
cd frontend
start "Frontend Server" npm start

echo.
echo Both servers are starting...
echo Backend: http://localhost:5000
echo Frontend: http://localhost:3000
echo.
echo Press any key to exit...
pause > nul