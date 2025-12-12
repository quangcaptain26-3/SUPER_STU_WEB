# 🎓 Hệ thống Quản lý Sinh viên (Super-Stu)

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.1.3-purple.svg)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Security](https://img.shields.io/badge/Security-Fortified-brightgreen.svg)]()

> **Hệ thống quản lý sinh viên toàn diện và bảo mật**, được xây dựng từ PHP thuần với kiến trúc hướng đối tượng, PDO, và áp dụng các biện pháp bảo mật hiện đại. Dự án cung cấp đầy đủ chức năng CRUD, phân quyền chi tiết (RBAC), xuất báo cáo động (PDF/DOCX), và thống kê dữ liệu trực quan.

Đây không chỉ là một ứng dụng CRUD thông thường, mà là một ví dụ điển hình về cách xây dựng một ứng-dụng PHP có cấu trúc, an toàn và dễ bảo trì mà không cần đến framework.

---

## 📋 Mục lục

- [🚀 Tính năng chính](#-tính-năng-chính)
- [🛠️ Công nghệ sử dụng](#️-công-nghệ-sử-dụng)
- [📁 Cấu trúc dự án](#-cấu-trúc-dự-án)
- [🌊 Luồng hoạt động của ứng dụng](#-luồng-hoạt-động-của-ứng-dụng)
- [⚙️ Cài đặt](#️-cài-đặt)
- [🔧 Troubleshooting (Gỡ rối)](#-troubleshooting-gỡ-rối)
- [🔒 Phân tích bảo mật](#-phân-tích-bảo-mật)
- [📞 Hỗ trợ](#-hỗ-trợ)

---

## 🚀 Tính năng chính

### 👥 **Quản lý sinh viên & Điểm số (CRUD)**
- **CRUD đầy đủ**: Thêm, sửa, xóa, xem thông tin sinh viên và điểm số.
- **Upload Avatar**: Tải và hiển thị ảnh đại diện cho sinh viên.
- **Tìm kiếm & Phân trang**: Tìm kiếm thông minh và phân trang hiệu quả cho danh sách.
- **Xếp loại tự động**: Tự động tính và hiển thị xếp loại (A, B, C...) dựa trên điểm số.
- **Điều khoản sử dụng "bất ngờ"**: Trải nghiệm đăng ký độc đáo với điều khoản sử dụng kết hợp nghiêm túc, hài hước và một yếu tố "gây sốc" khó quên.

### 🔐 **Hệ thống phân quyền (RBAC - Role-Based Access Control)**
- **4 cấp độ vai trò**: Super Admin > Admin > Teacher > Student.
- **Kiểm soát truy cập chi tiết**: Quyền hạn được định nghĩa rõ ràng cho từng vai trò. Các tính năng và cả các nút bấm trên giao diện sẽ được ẩn/hiện tùy theo quyền của người dùng.
- **Bảo vệ tài nguyên**: Middleware kiểm tra quyền sở hữu, đảm bảo sinh viên chỉ xem được dữ liệu của mình (nếu được cấu hình).

### 📈 **Thống kê & Báo cáo**
- **Dashboard trực quan**: Trang chủ hiển thị các số liệu thống kê quan trọng qua biểu đồ (Chart.js).
- **API thống kê**: Cung cấp JSON endpoint để giao diện người dùng (frontend) có thể lấy dữ liệu một cách bất đồng bộ.
- **Xuất báo cáo động**: Xuất danh sách sinh viên hoặc bảng điểm ra định dạng PDF (dùng TCPDF) và DOCX (dùng PHPWord).

### 🛡️ **Bảo mật cao cấp**
- **Chống SQL Injection**: Sử dụng 100% Prepared Statements (thông qua PDO).
- **Chống Cross-Site Scripting (XSS)**: Dữ liệu được mã hóa (escape) cẩn thận trước khi hiển thị ra HTML.
- **Chống Cross-Site Request Forgery (CSRF)**: Mọi form nhạy cảm (xóa, sửa) đều được bảo vệ bằng CSRF token.
- **Password Hashing**: Mật khẩu người dùng được băm an toàn bằng thuật toán SHA-256.

---

## 🛠️ Công nghệ sử dụng

| Tầng | Công nghệ | Mô tả |
| :--- | :--- | :--- |
| **Backend** | PHP 7.4+ (OOP) | Xử lý logic nghiệp vụ, không sử dụng framework. |
| **Database** | MySQL 5.7+ / MariaDB | Lưu trữ dữ liệu. Giao tiếp qua PDO. |
| **Frontend** | HTML5, CSS3, JS (ES6) | Giao diện người dùng. |
| **Styling** | Bootstrap 5.1.3 | Responsive UI framework. |
| **Biểu đồ** | Chart.js | Vẽ biểu đồ thống kê động trên dashboard. |
| **Xuất file** | TCPDF, PHPWord | Thư viện để tạo file PDF và DOCX. |
| **Alerts** | SweetAlert2 | Tạo các hộp thoại thông báo đẹp và chuyên nghiệp. |
| **Bảo mật** | SHA-256 Hashing | Băm mật khẩu người dùng. |

---

## 📁 Cấu trúc dự án

Cấu trúc thư mục được tổ chức rõ ràng theo chức năng, tách biệt logic, giao diện và tài nguyên.

```
super-stu/
│
├── 📄 *.php (Root-level controllers)
│   ├── authController.php     # Logic xác thực: đăng nhập, đăng ký, quên mật khẩu.
│   ├── studentController.php  # Logic CRUD cho sinh viên.
│   ├── scoreController.php    # Logic CRUD cho điểm số.
│   ├── exportController.php   # Logic xử lý các yêu cầu xuất file.
│   ├── middleware.php         # Các lớp/hàm kiểm tra quyền truy cập (RBAC).
│   └── utils.php              # File "thần thánh": chứa các hàm tiện ích, định nghĩa quyền, helpers.
│
├── 📁 public/                  # Thư mục gốc của web server, chứa các file người dùng có thể truy cập.
│   ├── index.php              # Trang dashboard chính sau khi đăng nhập.
│   ├── login.php              # Trang đăng nhập.
│   └── ...                    # Các file giao diện public khác.
│
├── 📁 students/ & scores/      # Các module chức năng chính.
│   ├── list.php               # Giao diện danh sách (sinh viên/điểm).
│   ├── add.php, edit.php      # Giao diện form thêm/sửa.
│   └── delete.php             # Script xử lý yêu cầu xóa (thường được gọi qua AJAX).
│
├── 📁 charts/
│   ├── statistics.php         # Trang giao diện hiển thị các biểu đồ thống kê.
│   └── api/statistics.php     # API endpoint trả về dữ liệu JSON cho các biểu đồ.
│
├── 📁 exports/
│   ├── export_pdf.php         # Script tạo và xuất file PDF.
│   └── export_docx.php        # Script tạo và xuất file DOCX.
│
├── 📁 config/
│   └── db.php                 # Lớp `Database` quản lý kết nối PDO đến CSDL.
│
├── 📁 assets/
│   ├── css/, js/              # Chứa các file CSS và JavaScript của dự án.
│   └── libs/                  # Chứa các thư viện bên thứ ba (TCPDF, PHPWord).
│
├── 📁 uploads/
│   └── avatars/               # Nơi lưu trữ ảnh đại diện của sinh viên.
│
└── 📄 database.sql             # File dump của cơ sở dữ liệu để cài đặt ban đầu.
```

---

## 🌊 Luồng hoạt động của ứng dụng

Ứng dụng này không dùng router trung tâm. Thay vào đó, mỗi file `.php` trong các thư mục `public/`, `students/`, `scores/`... hoạt động như một endpoint riêng lẻ.

#### 1. **Luồng Request & Hiển thị Trang (Ví dụ: `students/list.php`)**

1.  **Truy cập**: Người dùng điều hướng đến `students/list.php`.
2.  **Khởi tạo & Bảo mật**:
    -   `session_start()`: Bắt đầu phiên làm việc.
    -   `require_once '../utils.php';`: Nạp file tiện ích và định nghĩa quyền.
    -   `require_once '../studentController.php';`: Nạp file controller xử lý logic sinh viên.
    -   `requirePermission(PERMISSION_VIEW_STUDENTS);`: **Cổng bảo mật đầu tiên**. Hàm này (từ `utils.php`) sẽ kiểm tra vai trò (`$_SESSION['role']`) của người dùng có quyền `PERMISSION_VIEW_STUDENTS` không. Nếu không, người dùng sẽ bị chuyển hướng về trang chủ với thông báo lỗi.
3.  **Lấy dữ liệu**:
    -   Script khởi tạo `$studentController = new StudentController()`.
    -   Script lấy các tham số từ `$_GET` (ví dụ: `search`, `page`) để phục vụ tìm kiếm và phân trang.
    -   Gọi phương thức của controller để lấy dữ liệu từ CSDL: `$students = $studentController->getAllStudents(...)`.
4.  **Render Giao diện**:
    -   HTML được viết trực tiếp trong file.
    -   Dữ liệu từ biến `$students` được lặp và hiển thị trong bảng. `htmlspecialchars()` được dùng để chống XSS.
    -   **Kiểm tra quyền lần 2**: Các nút bấm như "Sửa", "Xóa" được đặt trong khối `if (hasPermission(...))` để chỉ hiển thị cho người dùng có quyền tương ứng.

#### 2. **Luồng Xác thực (Login)**

1.  Người dùng nhập username/password vào form ở `public/login.php` và nhấn submit.
2.  `login.php` nhận request `POST`, khởi tạo `AuthController`.
3.  Gọi `$auth->login($username, $password)`.
4.  Bên trong `AuthController`:
    -   Truy vấn CSDL để tìm user bằng `username`.
    -   Băm mật khẩu người dùng nhập vào bằng `hash('sha256', $password)`.
    -   Sử dụng `hash_equals()` để so sánh mật khẩu một cách an toàn (chống timing attack).
    -   Nếu thành công, lưu thông tin người dùng (`user_id`, `role`, `username`) vào `$_SESSION`.
5.  `login.php` nhận kết quả, nếu thành công thì chuyển hướng (`header('Location: index.php')`) đến trang dashboard.

#### 3. **Luồng AJAX (Ví dụ: Xóa sinh viên)**

1.  Người dùng nhấn nút "Xóa" trên `students/list.php`.
2.  JavaScript phía client (dùng `SweetAlert2`) hiện hộp thoại xác nhận.
3.  Nếu người dùng đồng ý, JavaScript dùng `fetch` gửi một request `POST` đến `students/delete.php` với `id` của sinh viên cần xóa.
4.  `students/delete.php` xử lý request:
    -   Kiểm tra quyền `PERMISSION_DELETE_STUDENTS`.
    -   Xác minh CSRF token (nếu có).
    -   Khởi tạo `StudentController`.
    -   Gọi `$studentController->deleteStudent($id)`. Phương thức này cũng sẽ xóa cả file avatar liên quan.
    -   Trả về một response JSON (ví dụ: `{'success': true, 'message': '...'}`).
5.  JavaScript ở `students/list.php` nhận response, hiển thị thông báo thành công và tải lại trang.

---

## ⚙️ Cài đặt

#### 1. **Yêu cầu hệ thống**
-   PHP 7.4+
-   MySQL 5.7+ / MariaDB
-   Web server (Apache, Nginx - với `mod_rewrite` cho Apache).
-   Trình duyệt web hiện đại.

#### 2. **Hướng dẫn**

1.  **Clone repository**:
    ```bash
    git clone https://github.com/quangcaptain26-3/SUPER_STU_WEB.git
    cd super-stu
    ```

2.  **Tạo Database**:
    -   Dùng phpMyAdmin hoặc command line để tạo một database mới.
        ```sql
        CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ```
    -   Import file `database.sql` vào database vừa tạo.

3.  **Cấu hình kết nối**:
    -   Mở file `config/db.php` và chỉnh sửa thông tin cho đúng với môi trường của bạn:
        ```php
        private $host = 'localhost';
        private $db_name = 'student_management';
        private $username = 'root';
        private $password = ''; // Mật khẩu của bạn
        ```

4.  **Triển khai**:
    -   **Với XAMPP/WAMP**: Copy toàn bộ thư mục `super-stu` vào `htdocs` (XAMPP) hoặc `www` (WAMP). Truy cập `http://localhost/super-stu/public/login.php`.
    -   **Với hosting**: Upload tất cả các file lên hosting. Trỏ domain của bạn vào thư mục `public`.

5.  **Tài khoản mặc định**:
    -   **Super Admin**: `admin` / `admin123`
    -   **Teacher**: `teacher1` / `teacher123`
    -   **Student**: `student1` / `student123`

> ⚠️ **QUAN TRỌNG**: Hãy đổi mật khẩu của các tài khoản mặc định ngay sau lần đăng nhập đầu tiên!

---

## 🔧 Troubleshooting (Gỡ rối)

-   **Lỗi "Connection error: ...":**
    -   **Nguyên nhân**: Sai thông tin kết nối CSDL.
    -   **Giải pháp**: Kiểm tra lại `host`, `db_name`, `username`, `password` trong file `config/db.php`. Đảm bảo dịch vụ MySQL đang chạy.

-   **Lỗi "Bạn không có quyền truy cập trang này":**
    -   **Nguyên nhân**: Đây là cơ chế bảo mật của hệ thống. Tài khoản của bạn không có quyền để xem trang hoặc thực hiện hành động này.
    -   **Giải pháp**: Đăng nhập bằng tài khoản có quyền cao hơn (ví dụ: `admin` hoặc `teacher`). Xem lại định nghĩa quyền trong `utils.php` để biết vai trò nào có quyền gì.

-   **Upload ảnh đại diện thất bại:**
    -   **Nguyên nhân**: Thư mục `uploads/avatars/` không tồn tại hoặc không có quyền ghi.
    -   **Giải pháp**: Đảm bảo thư mục `uploads/avatars/` tồn tại. Trên môi trường Linux/macOS, cấp quyền ghi cho web server bằng lệnh `chmod -R 775 uploads` và `chown -R www-data:www-data uploads` (thay `www-data` bằng user của web server bạn).

-   **Trang trắng hoặc lỗi 500 Internal Server Error:**
    -   **Nguyên nhân**: Lỗi cú pháp PHP.
    -   **Giải pháp**: Mở file log lỗi của Apache/Nginx để xem chi tiết lỗi. Nếu đang ở môi trường phát triển, bật hiển thị lỗi PHP bằng cách thêm `ini_set('display_errors', 1); error_reporting(E_ALL);` vào đầu file `public/index.php`.

-   **Giao diện bị vỡ, không có style:**
    -   **Nguyên nhân**: Trình duyệt không tải được các file CSS/JS từ CDN (Bootstrap, FontAwesome).
    -   **Giải pháp**: Kiểm tra kết nối Internet. Mở Developer Tools (F12) và xem tab "Console" có báo lỗi tải tài nguyên không.

---

## 🔒 Phân tích bảo mật

Hệ thống được xây dựng với tư duy "bảo mật là trên hết".

#### ✔️ **Các biện pháp đã áp dụng tốt**

-   **Chống SQL Injection**: Triệt để sử dụng **PDO Prepared Statements**. Mọi dữ liệu từ người dùng đều được truyền vào câu lệnh SQL qua các tham số ràng buộc (`bindParam`, `bindValue`), không bao giờ ghép chuỗi trực tiếp.
-   **Chống XSS (Cross-Site Scripting)**: Dữ liệu luôn được escape bằng `htmlspecialchars()` trước khi hiển thị ra HTML, vô hiệu hóa mọi thẻ script độc hại.
-   **Chống CSRF (Cross-Site Request Forgery)**: Các hành động thay đổi dữ liệu (xóa, cập nhật) đều yêu cầu một CSRF token hợp lệ được tạo và xác minh qua `utils.php`.
-   **Phân quyền chi tiết (RBAC)**: Quyền truy cập được kiểm tra ở cả backend (`requirePermission`) và frontend (ẩn/hiện nút bấm), đảm bảo người dùng chỉ thấy và làm những gì họ được phép.
-   **So sánh chuỗi an toàn**: Sử dụng `hash_equals()` để so sánh mật khẩu và token, giúp chống lại tấn công timing attack.

#### ⚠️ **Những điểm có thể cải thiện**

-   **Thuật toán băm mật khẩu**: Hiện tại đang dùng `SHA-256`. Mặc dù an toàn, nhưng tiêu chuẩn hiện đại khuyến nghị dùng các thuật toán có "cost factor" như **BCRYPT** hoặc **Argon2** (thông qua hàm `password_hash()` và `password_verify()` của PHP). Chúng được thiết kế để làm chậm quá trình băm, gây khó khăn hơn cho các cuộc tấn công brute-force.
-   **Thiếu cấu trúc tập trung**: Việc lặp lại code layout (sidebar, header) ở nhiều file làm tăng khả năng xảy ra lỗi và khó bảo trì. Áp dụng một hệ thống template đơn giản (ví dụ: một file `header.php` và `footer.php` để `require`) sẽ cải thiện điều này.
-   **Cấu hình nhạy cảm**: Thông tin đăng nhập CSDL đang được lưu trực tiếp trong `config/db.php`. Trong môi trường production, nên đưa các thông tin này ra ngoài web root và đọc từ biến môi trường (dùng thư viện như `vlucas/phpdotenv`).

---
## 📞 Hỗ trợ

Nếu bạn có bất kỳ câu hỏi hay góp ý nào, vui lòng tạo một **Issue** trên GitHub repository của dự án.

<div align="center">

**⭐ Nếu project hữu ích, hãy cho một star nhé! ⭐**

**Phát triển bởi**: Minh Quang - TTM63ĐH

</div>
