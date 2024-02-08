@echo off
setlocal

REM Check if git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Git is not installed. Please install Git and try again.
    timeout /t 10 >nul
    exit /b 1
)

REM Check if Docker is installed
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Docker is not installed. Please install Docker and try again.
    timeout /t 10 >nul
    exit /b 1
)

REM Prompt user for installation directory
set /p install_dir="Enter installation directory (press Enter for current directory '%cd%\'): "
if "%install_dir%"=="" set "install_dir=%cd%"

REM Clone repository or update if folder exists
cd %install_dir%
if exist linguacafe (
    cd linguacafe
    git pull
    docker-compose pull
    docker-compose up -d --force-recreate
) else (
    git clone -b deploy https://github.com/simjanos-dev/LinguaCafe.git linguacafe
    cd linguacafe
    docker-compose up -d
)

REM Wait for 20 seconds
echo "Waiting 20 seconds before opening a browser to access LingaCafe at the url http://localhost:9191"
timeout /t 20 >nul

REM Open browser on localhost:9191
start http://localhost:9191

:end
endlocal