#!/bin/bash

echo "================================="
echo "  CodeJudge C/C++ Environment Setup"
echo "================================="
echo

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check if g++ is installed
if command_exists g++; then
    echo "[INFO] g++ is already installed:"
    g++ --version
    echo
else
    echo "[WARNING] g++ not found"
    echo
    echo "Please install g++ compiler:"
    echo
    echo "Ubuntu/Debian:"
    echo "  sudo apt update"
    echo "  sudo apt install build-essential"
    echo
    echo "CentOS/RHEL/Fedora:"
    echo "  sudo yum install gcc-c++ (CentOS/RHEL)"
    echo "  sudo dnf install gcc-c++ (Fedora)"
    echo
    echo "macOS:"
    echo "  xcode-select --install"
    echo "  or install via Homebrew: brew install gcc"
    echo
fi

# Check if temp directory exists
if [ ! -d "temp" ]; then
    echo "[INFO] Creating temp directory..."
    mkdir -p temp
    chmod 755 temp
    echo "[SUCCESS] Temp directory created."
else
    echo "[INFO] Temp directory already exists."
fi

# Test compilation with a simple program
echo "[INFO] Testing C++ compilation..."
echo

TEST_FILE="temp/test_cpp_env.cpp"
TEST_EXE="temp/test_cpp_env"

cat > "$TEST_FILE" << 'EOF'
#include <iostream>
using namespace std;

int main() {
    cout << "Hello from CodeJudge C++ Environment!" << endl;
    return 0;
}
EOF

if command_exists g++; then
    echo "[INFO] Compiling test program..."
    if g++ -std=c++17 -O2 -Wall -o "$TEST_EXE" "$TEST_FILE" 2>/dev/null; then
        echo "[SUCCESS] Compilation successful!"
        echo "[INFO] Running test program..."
        ./"$TEST_EXE"
        echo
        echo "[SUCCESS] C++ environment is ready!"
        
        # Clean up test files
        rm -f "$TEST_FILE" "$TEST_EXE"
    else
        echo "[ERROR] Compilation failed."
        echo "[INFO] Test file content:"
        cat "$TEST_FILE"
    fi
else
    echo "[SKIP] Cannot test compilation - g++ not available."
fi

echo
echo "================================="
echo "  Setup Summary"
echo "================================="

if [ -d "temp" ]; then
    echo "[✓] Temp directory ready"
else
    echo "[✗] Temp directory missing"
fi

if command_exists g++; then
    echo "[✓] C++ compiler ready"
else
    echo "[✗] C++ compiler not available"
fi

echo
echo "To test the environment, run:"
echo "  php -S localhost:8000 -t public"
echo "Then visit: http://localhost:8000/problems"
echo
