@echo off
echo =================================
echo  CodeJudge C/C++ Environment Setup
echo =================================
echo.

REM Check if g++ is already installed
where g++ >nul 2>nul
if %ERRORLEVEL%==0 (
    echo [INFO] g++ is already installed:
    g++ --version
    echo.
) else (
    echo [WARNING] g++ not found in PATH
    echo.
    echo Please install MinGW-w64 or MSYS2 to get g++ compiler:
    echo.
    echo Option 1 - MSYS2 (Recommended):
    echo   1. Download from: https://www.msys2.org/
    echo   2. Install MSYS2
    echo   3. Open MSYS2 terminal and run:
    echo      pacman -S mingw-w64-x86_64-gcc
    echo      pacman -S mingw-w64-x86_64-gdb
    echo   4. Add to PATH: C:\msys64\mingw64\bin
    echo.
    echo Option 2 - TDM-GCC:
    echo   1. Download from: https://jmeubank.github.io/tdm-gcc/
    echo   2. Install and add to PATH
    echo.
    echo Option 3 - Code::Blocks with MinGW:
    echo   1. Download from: http://www.codeblocks.org/downloads
    echo   2. Choose version with MinGW included
    echo   3. Add MinGW bin folder to PATH
    echo.
)

REM Check if temp directory exists
if not exist "%~dp0temp" (
    echo [INFO] Creating temp directory...
    mkdir "%~dp0temp"
    echo [SUCCESS] Temp directory created.
) else (
    echo [INFO] Temp directory already exists.
)

REM Test compilation with a simple program
echo [INFO] Testing C++ compilation...
echo.

set TEST_FILE=%~dp0temp\test_cpp_env.cpp
set TEST_EXE=%~dp0temp\test_cpp_env.exe

echo #include ^<iostream^> > "%TEST_FILE%"
echo using namespace std; >> "%TEST_FILE%"
echo int main() { >> "%TEST_FILE%"
echo     cout ^<^< "Hello from CodeJudge C++ Environment!" ^<^< endl; >> "%TEST_FILE%"
echo     return 0; >> "%TEST_FILE%"
echo } >> "%TEST_FILE%"

where g++ >nul 2>nul
if %ERRORLEVEL%==0 (
    echo [INFO] Compiling test program...
    g++ -std=c++17 -O2 -Wall -o "%TEST_EXE%" "%TEST_FILE%" 2>nul
    
    if exist "%TEST_EXE%" (
        echo [SUCCESS] Compilation successful!
        echo [INFO] Running test program...
        "%TEST_EXE%"
        echo.
        echo [SUCCESS] C++ environment is ready!
        
        REM Clean up test files
        del "%TEST_FILE%" 2>nul
        del "%TEST_EXE%" 2>nul
    ) else (
        echo [ERROR] Compilation failed.
        echo [INFO] Test file content:
        type "%TEST_FILE%"
    )
) else (
    echo [SKIP] Cannot test compilation - g++ not available.
)

echo.
echo =================================
echo  Setup Summary
echo =================================
if exist "%~dp0temp" (
    echo [✓] Temp directory ready
) else (
    echo [✗] Temp directory missing
)

where g++ >nul 2>nul
if %ERRORLEVEL%==0 (
    echo [✓] C++ compiler ready
) else (
    echo [✗] C++ compiler not available
)

echo.
echo To test the environment, run:
echo   php -S localhost:8000 -t public
echo Then visit: http://localhost:8000/problems
echo.

pause
