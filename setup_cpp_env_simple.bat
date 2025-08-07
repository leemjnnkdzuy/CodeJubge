@echo off
echo =================================
echo  CodeJudge C++ Environment Setup
echo =================================
echo.

echo [INFO] Checking C++ compiler...
where g++ >nul 2>nul
if %ERRORLEVEL%==0 (
    echo [SUCCESS] g++ compiler found!
    g++ --version
    echo.
) else (
    echo [WARNING] g++ compiler not found in PATH
    echo Please install MinGW-w64 or MSYS2 first
    echo.
)

echo [INFO] Creating temp directory...
if not exist "%~dp0temp" (
    mkdir "%~dp0temp"
    echo [SUCCESS] Temp directory created.
) else (
    echo [INFO] Temp directory already exists.
)

echo [INFO] Testing C++ compilation...
if exist "%~dp0temp" (
    echo #include ^<iostream^> > "%~dp0temp\test.cpp"
    echo using namespace std; >> "%~dp0temp\test.cpp"
    echo int main^(^) { >> "%~dp0temp\test.cpp"
    echo     cout ^<^< "Hello CodeJudge C++!" ^<^< endl; >> "%~dp0temp\test.cpp"
    echo     return 0; >> "%~dp0temp\test.cpp"
    echo } >> "%~dp0temp\test.cpp"
    
    where g++ >nul 2>nul
    if %ERRORLEVEL%==0 (
        g++ -o "%~dp0temp\test.exe" "%~dp0temp\test.cpp" 2>nul
        if exist "%~dp0temp\test.exe" (
            echo [SUCCESS] Compilation test passed!
            "%~dp0temp\test.exe"
            del "%~dp0temp\test.cpp" "%~dp0temp\test.exe" 2>nul
        ) else (
            echo [ERROR] Compilation test failed
        )
    )
)

echo.
echo Setup complete! You can now:
echo 1. Start the server: php -S localhost:8000 -t public
echo 2. Visit: http://localhost:8000/problems
echo 3. Try solving C++ problems!
echo.
pause
