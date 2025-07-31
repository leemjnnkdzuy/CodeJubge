<?php
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'code_judge');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

if (!defined('SITE_NAME')) define('SITE_NAME', 'CodeJudge');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost:8000');
if (!defined('SITE_DESCRIPTION')) define('SITE_DESCRIPTION', 'Platform for automatic code evaluation and problem creation');

if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('APP_PATH')) define('APP_PATH', ROOT_PATH . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', ROOT_PATH . '/public');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/views');
if (!defined('CONTROLLER_PATH')) define('CONTROLLER_PATH', APP_PATH . '/controllers');
if (!defined('MODEL_PATH')) define('MODEL_PATH', APP_PATH . '/models');
if (!defined('CORE_PATH')) define('CORE_PATH', APP_PATH . '/core');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', PUBLIC_PATH . '/assets');
if (!defined('PUBLIC_ASSETS_PATH')) define('PUBLIC_ASSETS_PATH', SITE_URL . '/assets/');

if (!defined('SECRET_KEY')) define('SECRET_KEY', 'your-secret-key-here');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600);

if (!defined('CODE_TIMEOUT')) define('CODE_TIMEOUT', 5);
if (!defined('MEMORY_LIMIT')) define('MEMORY_LIMIT', '128M');
if (!defined('TEMP_DIR')) define('TEMP_DIR', ROOT_PATH . '/temp');

$SUPPORTED_LANGUAGES = [
    'python' => [
        'name' => 'Python 3',
        'extension' => '.py',
        'command' => 'python3',
        'template' => "def solution():\n    # Your code here\n    pass\n\n# Test your solution\nprint(solution())"
    ],
    'javascript' => [
        'name' => 'JavaScript',
        'extension' => '.js',
        'command' => 'node',
        'template' => "function solution() {\n    // Your code here\n}\n\n// Test your solution\nconsole.log(solution());"
    ],
    'cpp' => [
        'name' => 'C/C++',
        'extension' => '.cpp',
        'command' => 'g++',
        'template' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Your code here\n    return 0;\n}"
    ],
    'java' => [
        'name' => 'Java',
        'extension' => '.java',
        'command' => 'java',
        'template' => "public class Solution {\n    public static void main(String[] args) {\n        // Your code here\n    }\n}"
    ]
];

$DIFFICULTY_LEVELS = [
    'easy' => ['name' => 'Easy', 'color' => '#00b894'],
    'medium' => ['name' => 'Medium', 'color' => '#fdcb6e'],
    'hard' => ['name' => 'Hard', 'color' => '#e17055']
];

$BADGES = [
    "1_Year_on_CodeJudge" => [
        "title" => "1 Năm trên CodeJudge",
        "description" => "Hoạt động trên CodeJudge hơn 1 năm.",
        "File" => "badges_1_1_Year_on_Kaggle.svg"
    ],
    "2_Years_on_CodeJudge" => [
        "title" => "2 Năm trên CodeJudge",
        "description" => "Hoạt động trên CodeJudge hơn 2 năm.",
        "File" => "badges_2_2_Years_on_Kaggle.svg"
    ],
    "5_Years_on_CodeJudge" => [
        "title" => "5 Năm trên CodeJudge",
        "description" => "Hoạt động trên CodeJudge hơn 5 năm.",
        "File" => "badges_3_5_Years_on_Kaggle.svg"
    ],
    "10_Years_on_CodeJudge" => [
        "title" => "10 Năm trên CodeJudge",
        "description" => "Hoạt động trên CodeJudge hơn 10 năm.",
        "File" => "badges_4_10_Years_on_Kaggle.svg"
    ],
    "15_Years_on_CodeJudge" => [
        "title" => "15 Năm trên CodeJudge",
        "description" => "Hoạt động trên CodeJudge hơn 15 năm.",
        "File" => "badges_5_15_Years_on_Kaggle.svg"
    ],
    "Competitor" => [
        "title" => "Người Thi Đấu",
        "description" => "Đã nộp bài trong một cuộc thi lập trình trên CodeJudge. Các cuộc thi này là thử thách lập trình đầy đủ với các bài toán khó từ thực tế.",
        "File" => "badges_6_Competitor.svg"
    ],
    "Getting_Started_Competitor" => [
        "title" => "Người Mới Thi Đấu",
        "description" => "Đã nộp bài trong một cuộc thi dành cho người mới bắt đầu. Đây là các cuộc thi đơn giản, phù hợp để làm quen với lập trình thi đấu.",
        "File" => "badges_7_Getting_Started_Competitor.svg"
    ],
    "Advanced_Competitor" => [
        "title" => "Người Thi Đấu Nâng Cao",
        "description" => "Đã nộp bài trong một cuộc thi nâng cao. Các cuộc thi này yêu cầu kỹ năng lập trình cao và giải quyết các bài toán phức tạp.",
        "File" => "badges_8_Research_Competitor.svg"
    ],
    "Community_Competitor" => [
        "title" => "Người Thi Đấu Cộng Đồng",
        "description" => "Đã nộp bài trong một cuộc thi do cộng đồng tổ chức. Các cuộc thi này được tạo bởi thành viên CodeJudge hoặc các nhóm lập trình viên.",
        "File" => "badges_9_Community_Competitor.svg"
    ],
    "Weekly_Competitor" => [
        "title" => "Người Thi Đấu Hàng Tuần",
        "description" => "Đã nộp bài trong một cuộc thi hàng tuần. Đây là các cuộc thi thường xuyên để rèn luyện kỹ năng lập trình.",
        "File" => "badges_10_Playground_Competitor.svg"
    ],
    "Algorithm_Specialist" => [
        "title" => "Chuyên Gia Thuật Toán",
        "description" => "Đã nộp bài trong một cuộc thi tập trung vào thuật toán. Các cuộc thi này đòi hỏi tối ưu hóa giải thuật và độ phức tạp tính toán.",
        "File" => "badges_11_Simulation_Competitor.svg"
    ],
    "Holiday_Competitor" => [
        "title" => "Người Thi Đấu Ngày Lễ",
        "description" => "Đã nộp bài trong một cuộc thi đặc biệt vào dịp lễ. Đây là truyền thống hàng năm của CodeJudge với các bài toán vui nhộn.",
        "File" => "badges_12_Santa_Competitor.svg"
    ],
    "Marathon_Coder" => [
        "title" => "Lập Trình Viên Marathon",
        "description" => "Đã nộp bài trong một cuộc thi marathon kéo dài nhiều ngày. Đây là thử thách sức bền và kỹ năng lập trình.",
        "File" => "badges_13_March_Mania_Competitor.svg"
    ],
    "Code_Submitter" => [
        "title" => "Người Nộp Code",
        "description" => "Đã nộp code hoàn chỉnh để giải quyết bài toán. Code cần chạy đúng và tối ưu để vượt qua các test case.",
        "File" => "badges_14_Code_Submitter.svg"
    ],
    "Submission_Streak" => [
        "title" => "Chuỗi Nộp Bài",
        "description" => "Đã nộp bài liên tục trong 7 ngày.",
        "File" => "badges_15_Submission_Streak.svg"
    ],
    "Super_Submission_Streak" => [
        "title" => "Chuỗi Nộp Bài Siêu Cấp",
        "description" => "Đã nộp bài liên tục trong 30 ngày.",
        "File" => "badges_16_Super_Submission_Streak.svg"
    ],
    "Mega_Submission_Streak" => [
        "title" => "Chuỗi Nộp Bài Khủng",
        "description" => "Đã nộp bài liên tục trong 100 ngày.",
        "File" => "badges_17_Mega_Submission_Streak.svg"
    ],
    "Python_Coder" => [
        "title" => "Lập Trình Viên Python",
        "description" => "Đã giải bài bằng Python. Python là một trong những ngôn ngữ lập trình phổ biến nhất trong các cuộc thi lập trình.",
        "File" => "badges_18_Python_Coder.svg"
    ],
    "C++_Coder" => [
        "title" => "Lập Trình Viên C++",
        "description" => "Đã giải bài bằng C++. C++ là ngôn ngữ mạnh mẽ, thường được dùng trong các cuộc thi lập trình cần hiệu năng cao.",
        "File" => "badges_19_R_Coder.svg"
    ],
    "Java_Coder" => [
        "title" => "Lập Trình Viên Java",
        "description" => "Đã giải bài bằng Java. Java là ngôn ngữ lập trình hướng đối tượng phổ biến trong các cuộc thi.",
        "File" => "badges_20_R_Markdown_Coder.svg"
    ],
    "Code_Uploader" => [
        "title" => "Người Tải Code Lên",
        "description" => "Đã tải lên code từ máy tính cá nhân. Tính năng này cho phép bạn làm việc offline và tải lên khi cần.",
        "File" => "badges_21_Code_Uploader.svg"
    ],
    "Git_Coder" => [
        "title" => "Lập Trình Viên Git",
        "description" => "Đã sử dụng Git để quản lý code. Bạn có thể đồng bộ code với kho lưu trữ Git của mình.",
        "File" => "badges_23_Github_Coder.svg"
    ],
    "Code_Tagger" => [
        "title" => "Người Gắn Thẻ Code",
        "description" => "Đã thêm thẻ cho code. Gắn thẻ giúp code dễ được tìm thấy hơn trên CodeJudge.",
        "File" => "badges_25_Code_Tagger.svg"
    ],
    "Code_Forker" => [
        "title" => "Người Fork Code",
        "description" => "Đã fork code của người khác và chỉnh sửa. Fork code là cách hay để học hỏi và phát triển từ code có sẵn.",
        "File" => "badges_26_Code_Forker.svg"
    ],
    "Problem_Solver" => [
        "title" => "Người Giải Quyết Vấn Đề",
        "description" => "Đã giải quyết thành công nhiều bài toán khác nhau. Mỗi bài toán là một thử thách lập trình thú vị.",
        "File" => "badges_27_Notebook_Modeler.svg"
    ],
    "Problem_Creator" => [
        "title" => "Người Tạo Bài Toán",
        "description" => "Đã tạo một bài toán mới trên CodeJudge. Đóng góp bài toán là cách tuyệt vời để xây dựng cộng đồng lập trình.",
        "File" => "badges_29_Dataset_Creator.svg"
    ],
    "Test_Case_Designer" => [
        "title" => "Người Thiết Kế Test Case",
        "description" => "Đã tạo test case cho bài toán. Test case tốt giúp đánh giá chính xác code của người dùng.",
        "File" => "badges_30_Dataset_Pipeline_Creator.svg"
    ],
    "Problem_Tagger" => [
        "title" => "Người Gắn Thẻ Bài Toán",
        "description" => "Đã thêm thẻ cho bài toán. Gắn thẻ giúp bài toán dễ được tìm thấy hơn trên CodeJudge.",
        "File" => "badges_34_Dataset_Tagger.svg"
    ],
    "Stylish" => [
        "title" => "Phong Cách",
        "description" => "Đã hoàn thiện hồ sơ cá nhân. Hồ sơ CodeJudge là nơi thể hiện thành tích lập trình của bạn.",
        "File" => "badges_43_Stylish.svg"
    ],
    "Bookmarker" => [
        "title" => "Người Đánh Dấu",
        "description" => "Đã đánh dấu bài toán hoặc code yêu thích. Bạn có thể xem lại các đánh dấu trong mục 'Công việc của bạn'.",
        "File" => "badges_45_Bookmarker.svg"
    ],
    "Dark_Mode_User" => [
        "title" => "Người Dùng Chế Độ Tối",
        "description" => "Đã bật chế độ tối trên CodeJudge. Chế độ tối giúp giảm mỏi mắt khi lập trình lâu.",
        "File" => "badges_46_Vampire.svg"
    ],
    "Community_Member" => [
        "title" => "Thành Viên Cộng Đồng",
        "description" => "Đã tham gia cộng đồng CodeJudge. Kết nối với các lập trình viên khác để học hỏi và chia sẻ.",
        "File" => "badges_47_Agent_of_Discord.svg"
    ],
    "Learner" => [
        "title" => "Người Học",
        "description" => "Đã hoàn thành một khóa học trên CodeJudge. Các khóa học giúp bạn nắm vững kiến thức lập trình cơ bản.",
        "File" => "badges_48_Learner.svg"
    ],
    "Student" => [
        "title" => "Học Viên",
        "description" => "Đã hoàn thành 5 khóa học trên CodeJudge. Mỗi khóa học là một bước tiến trên con đường lập trình.",
        "File" => "badges_49_Student.svg"
    ],
    "Graduate" => [
        "title" => "Tốt Nghiệp",
        "description" => "Đã hoàn thành 10 khóa học trên CodeJudge. Bạn đã có nền tảng vững chắc về lập trình.",
        "File" => "badges_50_Graduate.svg"
    ],
    "7_Day_Login_Streak" => [
        "title" => "Chuỗi Đăng Nhập 7 Ngày",
        "description" => "Đã đăng nhập vào CodeJudge 7 ngày liên tiếp.",
        "File" => "badges_53_7_Day_Login_Streak.svg"
    ],
    "30_Day_Login_Streak" => [
        "title" => "Chuỗi Đăng Nhập 30 Ngày",
        "description" => "Đã đăng nhập vào CodeJudge 30 ngày liên tiếp.",
        "File" => "badges_54_30_Day_Login_Streak.svg"
    ],
    "100_Day_Login_Streak" => [
        "title" => "Chuỗi Đăng Nhập 100 Ngày",
        "description" => "Đã đăng nhập vào CodeJudge 100 ngày liên tiếp.",
        "File" => "badges_55_100_Day_Login_Streak.svg"
    ],
    "Year_Long_Login_Streak" => [
        "title" => "Chuỗi Đăng Nhập Cả Năm",
        "description" => "Đã đăng nhập vào CodeJudge 365 ngày liên tiếp.",
        "File" => "badges_56_Year_Long_Login_Streak.svg"
    ],
];

$TYPE_PROBLEM = [
    "Array" => [
        "name" => "Mảng",
        "description" => "Bài toán liên quan đến xử lý mảng.",
        "icon" => "bx-grid-alt"
    ],
    "String" => [
        "name" => "Chuỗi",
        "description" => "Bài toán liên quan đến xử lý chuỗi.",
        "icon" => "bx-text"
    ],
    "Hash_Table" => [
        "name" => "Bảng Băm",
        "description" => "Bài toán liên quan đến cấu trúc dữ liệu bảng băm.",
        "icon" => "bx-hash"
    ],
    "Dynamic_Programming" => [
        "name" => "Quy Hoạch Động",
        "description" => "Bài toán sử dụng kỹ thuật quy hoạch động.",
        "icon" => "bx-trending-up"
    ],
    "Math" => [
        "name" => "Toán Học",
        "description" => "Bài toán liên quan đến toán học.",
        "icon" => "bx-calculator"
    ],
    "Sorting" => [
        "name" => "Sắp Xếp",
        "description" => "Bài toán liên quan đến thuật toán sắp xếp.",
        "icon" => "bx-sort-alt-2"
    ],
    "Greedy" => [
        "name" => "Tham Lam",
        "description" => "Bài toán sử dụng thuật toán tham lam.",
        "icon" => "bx-target-lock"
    ],
    "Depth_First_Search" => [
        "name" => "Tìm Kiếm Theo Chiều Sâu",
        "description" => "Bài toán sử dụng thuật toán DFS.",
        "icon" => "bx-git-branch"
    ],
    "Binary_Search" => [
        "name" => "Tìm Kiếm Nhị Phân",
        "description" => "Bài toán sử dụng thuật toán tìm kiếm nhị phân.",
        "icon" => "bx-search-alt"
    ],
    "Database" => [
        "name" => "Cơ Sở Dữ Liệu",
        "description" => "Bài toán liên quan đến cơ sở dữ liệu.",
        "icon" => "bx-data"
    ],
    "Matrix" => [
        "name" => "Ma Trận",
        "description" => "Bài toán liên quan đến xử lý ma trận.",
        "icon" => "bx-grid"
    ],
    "Tree" => [
        "name" => "Cây",
        "description" => "Bài toán liên quan đến cấu trúc dữ liệu cây.",
        "icon" => "bx-git-branch"
    ],
    "Breadth_First_Search" => [
        "name" => "Tìm Kiếm Theo Chiều Rộng",
        "description" => "Bài toán sử dụng thuật toán BFS.",
        "icon" => "bx-expand-horizontal"
    ],
    "Bit_Manipulation" => [
        "name" => "Thao Tác Bit",
        "description" => "Bài toán liên quan đến thao tác trên bit.",
        "icon" => "bx-code-alt"
    ],
    "Two_Pointers" => [
        "name" => "Hai Con Trỏ",
        "description" => "Bài toán sử dụng kỹ thuật hai con trỏ.",
        "icon" => "bx-pointer"
    ],
    "Prefix_Sum" => [
        "name" => "Tổng Tiền Tố",
        "description" => "Bài toán sử dụng kỹ thuật tổng tiền tố.",
        "icon" => "bx-plus"
    ],
    "Heap_Priority_Queue" => [
        "name" => "Heap (Hàng Đợi Ưu Tiên)",
        "description" => "Bài toán sử dụng cấu trúc dữ liệu heap.",
        "icon" => "bx-layer"
    ],
    "Simulation" => [
        "name" => "Mô Phỏng",
        "description" => "Bài toán mô phỏng quá trình.",
        "icon" => "bx-play-circle"
    ],
    "Binary_Tree" => [
        "name" => "Cây Nhị Phân",
        "description" => "Bài toán liên quan đến cây nhị phân.",
        "icon" => "bx-network-chart"
    ],
    "Graph" => [
        "name" => "Đồ Thị",
        "description" => "Bài toán liên quan đến lý thuyết đồ thị.",
        "icon" => "bx-share-alt"
    ],
    "Stack" => [
        "name" => "Ngăn Xếp",
        "description" => "Bài toán sử dụng cấu trúc dữ liệu ngăn xếp.",
        "icon" => "bx-layer"
    ],
    "Counting" => [
        "name" => "Đếm",
        "description" => "Bài toán liên quan đến đếm số lượng.",
        "icon" => "bx-list-ol"
    ],
    "Sliding_Window" => [
        "name" => "Cửa Sổ Trượt",
        "description" => "Bài toán sử dụng kỹ thuật cửa sổ trượt.",
        "icon" => "bx-window"
    ],
    "Design" => [
        "name" => "Thiết Kế",
        "description" => "Bài toán thiết kế cấu trúc dữ liệu.",
        "icon" => "bx-cube"
    ],
    "Enumeration" => [
        "name" => "Liệt Kê",
        "description" => "Bài toán liệt kê các khả năng.",
        "icon" => "bx-list-ul"
    ],
    "Backtracking" => [
        "name" => "Quay Lui",
        "description" => "Bài toán sử dụng thuật toán quay lui.",
        "icon" => "bx-undo"
    ],
    "Union_Find" => [
        "name" => "Union Find",
        "description" => "Bài toán sử dụng cấu trúc dữ liệu Union Find.",
        "icon" => "bx-link"
    ],
    "Linked_List" => [
        "name" => "Danh Sách Liên Kết",
        "description" => "Bài toán liên quan đến danh sách liên kết.",
        "icon" => "bx-list-check"
    ],
    "Number_Theory" => [
        "name" => "Lý Thuyết Số",
        "description" => "Bài toán liên quan đến lý thuyết số.",
        "icon" => "bx-math"
    ],
    "Ordered_Set" => [
        "name" => "Tập Có Thứ Tự",
        "description" => "Bài toán sử dụng tập có thứ tự.",
        "icon" => "bx-sort-down"
    ],
    "Monotonic_Stack" => [
        "name" => "Ngăn Xếp Đơn Điệu",
        "description" => "Bài toán sử dụng ngăn xếp đơn điệu.",
        "icon" => "bx-trending-down"
    ],
    "Segment_Tree" => [
        "name" => "Cây Phân Đoạn",
        "description" => "Bài toán sử dụng cây phân đoạn.",
        "icon" => "bx-git-branch"
    ],
    "Trie" => [
        "name" => "Trie",
        "description" => "Bài toán sử dụng cấu trúc dữ liệu Trie.",
        "icon" => "bx-git-branch"
    ],
    "Combinatorics" => [
        "name" => "Tổ Hợp",
        "description" => "Bài toán liên quan đến tổ hợp.",
        "icon" => "bx-shuffle"
    ],
    "Bitmask" => [
        "name" => "Mặt Nạ Bit",
        "description" => "Bài toán sử dụng kỹ thuật bitmask.",
        "icon" => "bx-mask"
    ],
    "Queue" => [
        "name" => "Hàng Đợi",
        "description" => "Bài toán sử dụng cấu trúc dữ liệu hàng đợi.",
        "icon" => "bx-right-arrow-alt"
    ],
    "Recursion" => [
        "name" => "Đệ Quy",
        "description" => "Bài toán sử dụng kỹ thuật đệ quy.",
        "icon" => "bx-refresh"
    ],
    "Divide_and_Conquer" => [
        "name" => "Chia Để Trị",
        "description" => "Bài toán sử dụng thuật toán chia để trị.",
        "icon" => "bx-split-horizontal"
    ],
    "Geometry" => [
        "name" => "Hình Học",
        "description" => "Bài toán liên quan đến hình học.",
        "icon" => "bx-shape-triangle"
    ],
    "Binary_Indexed_Tree" => [
        "name" => "Cây Chỉ Số Nhị Phân",
        "description" => "Bài toán sử dụng cây chỉ số nhị phân.",
        "icon" => "bx-git-branch"
    ],
    "Memoization" => [
        "name" => "Ghi Nhớ",
        "description" => "Bài toán sử dụng kỹ thuật ghi nhớ.",
        "icon" => "bx-bookmark"
    ],
    "Hash_Function" => [
        "name" => "Hàm Băm",
        "description" => "Bài toán sử dụng hàm băm.",
        "icon" => "bx-hash"
    ],
    "Binary_Search_Tree" => [
        "name" => "Cây Tìm Kiếm Nhị Phân",
        "description" => "Bài toán liên quan đến cây tìm kiếm nhị phân.",
        "icon" => "bx-git-branch"
    ],
    "Shortest_Path" => [
        "name" => "Đường Đi Ngắn Nhất",
        "description" => "Bài toán tìm đường đi ngắn nhất.",
        "icon" => "bx-navigation"
    ],
    "String_Matching" => [
        "name" => "Khớp Chuỗi",
        "description" => "Bài toán khớp mẫu chuỗi.",
        "icon" => "bx-search"
    ],
    "Topological_Sort" => [
        "name" => "Sắp Xếp Tô-pô",
        "description" => "Bài toán sắp xếp tô-pô.",
        "icon" => "bx-sort"
    ],
    "Rolling_Hash" => [
        "name" => "Băm Cuộn",
        "description" => "Bài toán sử dụng kỹ thuật băm cuộn.",
        "icon" => "bx-refresh"
    ],
    "Game_Theory" => [
        "name" => "Lý Thuyết Trò Chơi",
        "description" => "Bài toán liên quan đến lý thuyết trò chơi.",
        "icon" => "bx-joystick"
    ],
    "Interactive" => [
        "name" => "Tương Tác",
        "description" => "Bài toán tương tác với hệ thống.",
        "icon" => "bx-chat"
    ],
    "Data_Stream" => [
        "name" => "Luồng Dữ Liệu",
        "description" => "Bài toán xử lý luồng dữ liệu.",
        "icon" => "bx-transfer"
    ],
    "Monotonic_Queue" => [
        "name" => "Hàng Đợi Đơn Điệu",
        "description" => "Bài toán sử dụng hàng đợi đơn điệu.",
        "icon" => "bx-trending-down"
    ],
    "Brainteaser" => [
        "name" => "Câu Đố",
        "description" => "Bài toán câu đố logic.",
        "icon" => "bx-brain"
    ],
    "Doubly_Linked_List" => [
        "name" => "Danh Sách Liên Kết Đôi",
        "description" => "Bài toán liên quan đến danh sách liên kết đôi.",
        "icon" => "bx-link-alt"
    ],
    "Randomized" => [
        "name" => "Ngẫu Nhiên",
        "description" => "Bài toán sử dụng thuật toán ngẫu nhiên.",
        "icon" => "bx-shuffle"
    ],
    "Merge_Sort" => [
        "name" => "Sắp Xếp Trộn",
        "description" => "Bài toán sử dụng thuật toán sắp xếp trộn.",
        "icon" => "bx-merge"
    ],
    "Counting_Sort" => [
        "name" => "Sắp Xếp Đếm",
        "description" => "Bài toán sử dụng thuật toán sắp xếp đếm.",
        "icon" => "bx-list-ol"
    ],
    "Iterator" => [
        "name" => "Bộ Lặp",
        "description" => "Bài toán sử dụng bộ lặp.",
        "icon" => "bx-repeat"
    ],
    "Concurrency" => [
        "name" => "Đồng Thời",
        "description" => "Bài toán xử lý đồng thời.",
        "icon" => "bx-timer"
    ],
    "Probability_and_Statistics" => [
        "name" => "Xác Suất và Thống Kê",
        "description" => "Bài toán liên quan đến xác suất và thống kê.",
        "icon" => "bx-bar-chart"
    ],
    "Quickselect" => [
        "name" => "Chọn Nhanh",
        "description" => "Bài toán sử dụng thuật toán chọn nhanh.",
        "icon" => "bx-select-multiple"
    ],
    "Suffix_Array" => [
        "name" => "Mảng Hậu Tố",
        "description" => "Bài toán sử dụng mảng hậu tố.",
        "icon" => "bx-grid-alt"
    ],
    "Line_Sweep" => [
        "name" => "Quét Đường",
        "description" => "Bài toán sử dụng thuật toán quét đường.",
        "icon" => "bx-scan"
    ],
    "Minimum_Spanning_Tree" => [
        "name" => "Cây Khung Nhỏ Nhất",
        "description" => "Bài toán tìm cây khung có trọng số nhỏ nhất.",
        "icon" => "bx-git-branch"
    ],
    "Bucket_Sort" => [
        "name" => "Sắp Xếp Thùng",
        "description" => "Bài toán sử dụng thuật toán sắp xếp thùng.",
        "icon" => "bx-grid"
    ],
    "Shell" => [
        "name" => "Shell",
        "description" => "Bài toán liên quan đến shell script.",
        "icon" => "bx-terminal"
    ],
    "Reservoir_Sampling" => [
        "name" => "Lấy Mẫu Hồ Chứa",
        "description" => "Bài toán sử dụng thuật toán lấy mẫu hồ chứa.",
        "icon" => "bx-droplet"
    ],
    "Strongly_Connected_Component" => [
        "name" => "Thành Phần Liên Thông Mạnh",
        "description" => "Bài toán tìm thành phần liên thông mạnh.",
        "icon" => "bx-share-alt"
    ],
    "Eulerian_Circuit" => [
        "name" => "Chu Trình Euler",
        "description" => "Bài toán tìm chu trình Euler.",
        "icon" => "bx-refresh"
    ],
    "Radix_Sort" => [
        "name" => "Sắp Xếp Cơ Số",
        "description" => "Bài toán sử dụng thuật toán sắp xếp cơ số.",
        "icon" => "bx-sort"
    ],
    "Rejection_Sampling" => [
        "name" => "Lấy Mẫu Loại Bỏ",
        "description" => "Bài toán sử dụng thuật toán lấy mẫu loại bỏ.",
        "icon" => "bx-x-circle"
    ],
    "Biconnected_Component" => [
        "name" => "Thành Phần Liên Thông Kép",
        "description" => "Bài toán tìm thành phần liên thông kép.",
        "icon" => "bx-link-alt"
    ]
];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>