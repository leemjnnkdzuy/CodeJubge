# Hướng dẫn sử dụng môi trường C/C++ - CodeJudge

## Mục lục
1. [Cài đặt môi trường](#cài-đặt-môi-trường)
2. [Kiểm tra môi trường](#kiểm-tra-môi-trường)
3. [Cách viết code](#cách-viết-code)
4. [Tính năng bảo mật](#tính-năng-bảo-mật)
5. [Giới hạn và ràng buộc](#giới-hạn-và-ràng-buộc)
6. [Xử lý sự cố](#xử-lý-sự-cố)

---

## Cài đặt môi trường

### Windows

#### Tự động (Khuyến nghị)
```batch
# Chạy script tự động
setup_cpp_env.bat
```

#### Thủ công

**Tùy chọn 1: MSYS2 (Khuyến nghị)**
1. Tải MSYS2 từ: https://www.msys2.org/
2. Cài đặt MSYS2
3. Mở MSYS2 terminal và chạy:
   ```bash
   pacman -S mingw-w64-x86_64-gcc
   pacman -S mingw-w64-x86_64-gdb
   ```
4. Thêm vào PATH: `C:\msys64\mingw64\bin`

**Tùy chọn 2: TDM-GCC**
1. Tải từ: https://jmeubank.github.io/tdm-gcc/
2. Cài đặt và thêm vào PATH

**Tùy chọn 3: Code::Blocks với MinGW**
1. Tải từ: http://www.codeblocks.org/downloads
2. Chọn phiên bản có kèm MinGW
3. Thêm thư mục MinGW bin vào PATH

### Linux/macOS

#### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install build-essential

# Hoặc chạy script tự động
chmod +x setup_cpp_env.sh
./setup_cpp_env.sh
```

#### CentOS/RHEL/Fedora:
```bash
# CentOS/RHEL
sudo yum install gcc-c++

# Fedora
sudo dnf install gcc-c++
```

#### macOS:
```bash
# Cài đặt Xcode Command Line Tools
xcode-select --install

# Hoặc dùng Homebrew
brew install gcc
```

---

## Kiểm tra môi trường

### Kiểm tra compiler
```bash
g++ --version
```

### Test compilation
```cpp
// test.cpp
#include <iostream>
using namespace std;

int main() {
    cout << "Hello CodeJudge!" << endl;
    return 0;
}
```

```bash
g++ -o test test.cpp
./test
```

---

## Cách viết code

### Template cơ bản
```cpp
#include <iostream>
#include <vector>
#include <string>
using namespace std;

int main() {
    // Đọc input
    int n;
    cin >> n;
    
    // Xử lý logic
    // ...
    
    // Xuất output
    cout << result << endl;
    
    return 0;
}
```

### Thư viện được phép sử dụng
```cpp
// STL Containers
#include <vector>
#include <string>
#include <array>
#include <deque>
#include <list>
#include <set>
#include <map>
#include <unordered_set>
#include <unordered_map>
#include <queue>
#include <stack>

// Algorithms
#include <algorithm>
#include <numeric>
#include <iterator>
#include <functional>

// I/O
#include <iostream>
#include <sstream>
#include <iomanip>

// Math
#include <cmath>
#include <climits>
#include <cfloat>

// Utilities
#include <utility>
#include <tuple>
#include <memory>
#include <limits>
#include <random>
#include <bitset>

// C libraries
#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <cctype>
#include <cassert>
#include <ctime>
```

### Ví dụ giải bài toán
```cpp
#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);
    
    int n;
    cin >> n;
    vector<int> arr(n);
    
    for(int i = 0; i < n; i++) {
        cin >> arr[i];
    }
    
    sort(arr.begin(), arr.end());
    
    for(int i = 0; i < n; i++) {
        cout << arr[i];
        if(i < n-1) cout << " ";
    }
    cout << endl;
    
    return 0;
}
```

---

## Tính năng bảo mật

### Code được kiểm tra tự động
- **File operations**: Không thể sử dụng `fopen`, `fwrite`, `remove`...
- **System calls**: Không thể gọi `system()`, `exec()`, `popen()`...
- **Network operations**: Không thể tạo socket, kết nối mạng
- **Memory operations**: Kiểm tra `malloc`, `free` nguy hiểm
- **Headers nguy hiểm**: Chặn include `windows.h`, `winapi.h`...
- **Local files**: Không thể include file local (`#include "..."`)

### Giới hạn compile
- **Standard**: C++17
- **Flags**: `-O2 -Wall -Wextra -fstack-protector-strong`
- **Size**: Code tối đa 50KB
- **Loop depth**: Tối đa 3 vòng lặp lồng nhau

---

## Giới hạn và ràng buộc

### Thời gian và bộ nhớ
- **Time limit**: Tối đa 5 giây/testcase
- **Memory limit**: Tối đa 256MB
- **Compile timeout**: 30 giây

### Định dạng input/output
- Đọc từ `stdin`, xuất ra `stdout`
- Kết thúc mỗi dòng bằng `\n` hoặc `endl`
- Không in thêm ký tự thừa

### File tạm thời
- Tự động xóa sau khi chạy
- Tên file unique để tránh conflict
- Cleanup sau 1 giờ nếu có lỗi

---

## Xử lý sự cố

### Lỗi compile
```
Compilation Error: 'cout' was not declared in this scope
```
**Giải pháp**: Thêm `using namespace std;` hoặc dùng `std::cout`

### Lỗi runtime
```
Runtime Error: Exit code 1
```
**Nguyên nhân**: Có thể do:
- Array out of bounds
- Division by zero
- Stack overflow
- Segmentation fault

### Lỗi time limit
```
Time Limit Exceeded
```
**Giải pháp**:
- Tối ưu algorithm (từ O(n²) xuống O(n log n))
- Sử dụng `ios_base::sync_with_stdio(false)`
- Tránh string concatenation trong loop

### Lỗi memory limit
```
Memory Limit Exceeded
```
**Giải pháp**:
- Giảm kích thước array/vector
- Sử dụng efficient data structures
- Tránh tạo object không cần thiết

### Lỗi wrong answer
```
Wrong Answer: Đã qua 3/5 test cases
```
**Cách debug**:
- Kiểm tra edge cases (n=0, n=1)
- Đảm bảo output format chính xác
- Test với sample input/output

---

## Tips và Best Practices

### Performance Optimization
```cpp
// Fast I/O
ios_base::sync_with_stdio(false);
cin.tie(NULL);

// Use references for large objects
void process(const vector<int>& arr) { }

// Reserve vector capacity
vector<int> v;
v.reserve(1000000);
```

### Memory Optimization
```cpp
// Use appropriate data types
int vs long long
vector<bool> vs vector<char>

// Clear unused containers
vector<int>().swap(v); // Free memory
```

### Common Patterns
```cpp
// Reading until EOF
int x;
while(cin >> x) {
    // process x
}

// Reading multiple test cases
int t;
cin >> t;
while(t--) {
    // solve each test case
}
```

---

## Ví dụ hoàn chỉnh

### Bài toán: Tìm số lớn nhất trong mảng
```cpp
#include <iostream>
#include <vector>
#include <algorithm>
using namespace std;

int main() {
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);
    
    int n;
    cin >> n;
    
    if(n <= 0) {
        cout << "Invalid input" << endl;
        return 1;
    }
    
    vector<int> arr(n);
    for(int i = 0; i < n; i++) {
        cin >> arr[i];
    }
    
    int maxVal = *max_element(arr.begin(), arr.end());
    cout << maxVal << endl;
    
    return 0;
}
```

---

## Liên hệ hỗ trợ

Nếu gặp vấn đề với môi trường C++:
1. Kiểm tra lại compiler installation
2. Chạy script setup_cpp_env
3. Xem log errors trong console
4. Liên hệ admin qua trang contact

**Chúc bạn code vui vẻ! 🚀**
