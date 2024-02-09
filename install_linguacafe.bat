@echo off
setlocal EnableDelayedExpansion

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

REM Check if Docker Engine is running
docker info > nul 2>&1
if %errorlevel% neq 0 (
    echo Docker Engine is not running. Please start Docker Desktop and try again.
    timeout /t 10 >nul
    exit /b 1
) 

REM If the folder already exists, update and close
if exist linguacafe (
    echo Installation of LingaCafe detected, updating...
    cd linguacafe
    git pull
    docker-compose pull
    docker-compose up -d --force-recreate
    timeout /t 10 >nul
    exit /b 0
)

REM Display suggested installation directory including "linguacafe" folder
set "install_dir=%cd%\linguacafe"
echo The installation directory will be: !install_dir!

REM Ask user to confirm or cancel installation
set /p confirm_installation=Do you want to proceed with the installation? (Y/N): 

REM Check user's response
if /i "!confirm_installation!"=="Y" (
    echo Proceeding with installation...
    REM Clone repository or update if folder exists
    git clone -b deploy https://github.com/simjanos-dev/LinguaCafe.git linguacafe
    cd linguacafe
    docker-compose up -d

    echo Your LinguaCafe server has started, you can soon open it in your browser on this url: http://localhost:9191. Sometimes this can take a few minutes on slower computers.
    set /p close="Press Enter to close this window."
) else (
    echo Installation canceled. Exiting...
    exit /b 1
)

:end
endlocal