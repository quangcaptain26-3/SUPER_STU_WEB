# 🎓 Hệ thống quản lý sinh viên (Student Management System)

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.1.3-purple.svg)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Security](https://img.shields.io/badge/Security-Enhanced-brightgreen.svg)]()

> **Hệ thống quản lý sinh viên toàn diện** được xây dựng bằng PHP, MySQL, Bootstrap và Chart.js với đầy đủ các chức năng CRUD, phân quyền người dùng, xuất báo cáo, thống kê trực quan và **bảo mật cao cấp**.

---

## 📋 Mục lục

- [🚀 Tính năng chính](#-tính-năng-chính)
- [🛠️ Công nghệ sử dụng](#️-công-nghệ-sử-dụng)
- [📁 Cấu trúc dự án](#-cấu-trúc-dự-án)
- [📖 Chức năng và Mã nguồn](#-chức-năng-và-mã-nguồn)
- [⚙️ Cài đặt](#️-cài-đặt)
- [🎨 Giao diện](#-giao-diện)
- [🔧 Tùy chỉnh](#-tùy-chỉnh)
- [🌐 Triển khai hosting](#-triển-khai-hosting)
- [🔒 Bảo mật](#-bảo-mật)
- [📞 Hỗ trợ](#-hỗ-trợ)


---

## 🚀 Tính năng chính

### 👥 **Quản lý sinh viên**

- ✅ **CRUD đầy đủ**: Thêm, sửa, xóa, xem thông tin sinh viên
- ✅ **Upload avatar**: Tải lên ảnh đại diện cho sinh viên
- ✅ **Tìm kiếm thông minh**: Lọc sinh viên theo tên, mã sinh viên, email
- ✅ **Phân trang**: Hiển thị danh sách với phân trang tối ưu
- ✅ **Xem chi tiết**: Thông tin đầy đủ về từng sinh viên
- ✅ **Validation**: Kiểm tra dữ liệu đầu vào chặt chẽ

### 📊 **Quản lý điểm số**

- ✅ **Nhập điểm**: Thêm điểm cho từng môn học
- ✅ **Xếp loại tự động**: Tính xếp loại A+, A, B+, B, C, D
- ✅ **Bộ lọc linh hoạt**: Lọc theo sinh viên, học kỳ, môn học
- ✅ **Thống kê chi tiết**: Điểm trung bình, phân bố điểm
- ✅ **Lịch sử điểm**: Theo dõi quá trình học tập

### 🔐 **Hệ thống phân quyền (RBAC)**

- ✅ **4 cấp độ**: Super Admin > Admin > Teacher > Student
- ✅ **Kiểm soát chi tiết**: Quyền hạn dựa trên vai trò
- ✅ **Bảo vệ tài nguyên**: Kiểm tra quyền sở hữu dữ liệu
- ✅ **Hiển thị động**: Nút hành động chỉ hiện nếu có quyền

### 📈 **Thống kê & Báo cáo**

- ✅ **Biểu đồ tương tác**: Visualize dữ liệu với Chart.js
- ✅ **Xuất PDF**: Tạo báo cáo PDF chuyên nghiệp (TCPDF)
- ✅ **Xuất DOCX**: Xuất danh sách sang Word (PHPWord)
- ✅ **Phân tích**: Xem xét xu hướng điểm và hiệu suất

### 🔔 **Thông báo & Ghi chú**

- ✅ **Thông báo realtime**: Hệ thống thông báo động
- ✅ **Alerts**: Cảnh báo khi có sự kiện quan trọng

---

## 🛠️ Công nghệ sử dụng

| Tầng | Công nghệ |
| --- | --- |
| **Frontend** | HTML5, CSS3, Bootstrap 5.1.3, JavaScript (Vanilla) |
| **Backend** | PHP 7.4+ (OOP) |
| **Database** | MySQL 5.7+ / MariaDB |
| **Chart** | Chart.js |
| **Export** | TCPDF (PDF), PHPWord (DOCX) |
| **Icons** | Font Awesome 6.0 |
| **Alerts** | SweetAlert2 |

---

## 📁 Cấu trúc dự án

```
super-stu/
├── 📄 index.php # File chính (redirect)
├── 📄 authController.php # Xử lý đăng nhập/đăng ký
├── 📄 studentController.php # Xử lý CRUD sinh viên
├── 📄 scoreController.php # Xử lý CRUD điểm
├── 📄 exportController.php # Xử lý xuất dữ liệu
├── 📄 middleware.php # Middleware phân quyền
├── 📄 utils.php # Hàm tiện ích chung
│
├── 📁 config/
│   └── 📄 db.php # Cấu hình database
│
├── 📁 public/ # File công khai
│
├── 📁 students/ # Quản lý sinh viên
│
├── 📁 scores/ # Quản lý điểm
│
├── 📁 charts/ # Thống kê & biểu đồ
│   ├── 📄 statistics.php # Trang thống kê chính
│   └── 📁 api/
│       └── 📄 statistics.php # API thống kê
│
├── 📁 exports/ # Xuất dữ liệu
│   ├── 📄 export_pdf.php # Export PDF
│   └── 📄 export_docx.php # Export Word
│
├── 📁 assets/
│
├── 📁 uploads/
│   └── 📁 avatars/ # Ảnh đại diện sinh viên
│
└── 📄 database.sql # Database dump
```

---

## 📖 Chức năng và Mã nguồn

Phần này cung cấp các tham chiếu trực tiếp đến các đoạn code chính giúp triển khai các chức năng của hệ thống.

### 1. **Quản lý Sinh viên & Điểm (CRUD)**
Logic chính cho các thao tác Create, Read, Update, Delete được đóng gói trong các lớp Controller.

- **`studentController.php`**: Xử lý toàn bộ logic cho sinh viên.
  - `getAllStudents()`: Lấy danh sách sinh viên kèm tìm kiếm và phân trang.
  - `createStudent()`, `updateStudent()`, `deleteStudent()`: Các hàm CRUD cơ bản.
- **`scoreController.php`**: Xử lý logic cho điểm số.
  - `getAllScores()`: Lấy danh sách điểm kèm bộ lọc theo sinh viên, học kỳ.
  - `createScore()`, `updateScore()`, `deleteScore()`: Các hàm CRUD.
- **Giao diện**: Các tệp trong thư mục `students/` và `scores/` chịu trách nhiệm hiển thị form và danh sách.

### 2. **Xác thực & Phân quyền (RBAC)**
Hệ thống sử dụng Middleware để kiểm soát quyền truy cập dựa trên vai trò.

- **`middleware.php`**: File trung tâm của hệ thống phân quyền.
  - `requirePermission($permission)`: Hàm được gọi ở đầu mỗi trang yêu cầu quyền. Nó kiểm tra `$_SESSION['role']` và `USER_PERMISSIONS`. (Dòng 20-35)
  - `isOwner($resourceOwnerId)`: Kiểm tra quyền sở hữu tài nguyên.
- **`utils.php`**: Định nghĩa các hằng số quyền và vai trò. (Dòng 5-25)
- **`authController.php`**: Xử lý logic đăng nhập, đăng ký và tạo phiên làm việc.
  - `login()`: Xác thực người dùng và gán vai trò vào session.
  - `register()`: Đăng ký người dùng mới.

### 3. **Thống kê & Biểu đồ**
Dữ liệu được lấy từ backend qua API và vẽ bằng Chart.js ở frontend.

- **`charts/statistics.php`**: Trang hiển thị chính.
  - Phần `<script>` (từ dòng 250): Khởi tạo và cấu hình 4 loại biểu đồ (Doughnut, Line, Bar) sử dụng dữ liệu được truyền từ PHP.
- **`charts/api/statistics.php`**: API endpoint cung cấp dữ liệu JSON cho dashboard và các biểu đồ.
- **`studentController.php` & `scoreController.php`**:
  - `getStatistics()`: Tổng hợp số liệu sinh viên (theo giới tính, tháng).
  - `getScoreStatistics()`: Tổng hợp số liệu điểm (theo môn học, học kỳ, phân bố xếp loại).

### 4. **Xuất Báo cáo (PDF & DOCX)**
Sử dụng thư viện TCPDF và PHPWord để tạo các tệp báo cáo động.

- **`exports/export_pdf.php`**: Tạo file PDF danh sách sinh viên hoặc bảng điểm. Logic chính sử dụng HTML để render nội dung.
- **`exports/export_docx.php`**: Tạo file DOCX. Dữ liệu được điền vào các bảng và văn bản được định dạng.

### 5. **Bảo mật**
Các biện pháp bảo mật được tích hợp trên toàn hệ thống.

- **Chống SQL Injection**: Sử dụng Prepared Statements (PDO) trong tất cả các truy vấn. Xem `config/db.php` và các phương thức trong `*Controller.php`.
- **Chống XSS**: Hàm `htmlspecialchars()` được áp dụng cho mọi dữ liệu xuất ra HTML.
- **Chống CSRF**:
  - `utils.php` (Dòng 60-75): Các hàm `generateCsrfToken()` và `verifyCsrfToken()`.
  - Token được chèn vào các form (ví dụ: `students/list.php`) và được kiểm tra trong các file xử lý (`delete.php`).
- **Mã hóa mật khẩu**:
  - `authController.php` (trong hàm `register()`): Sử dụng `password_hash()` với thuật toán BCRYPT.
  - `authController.php` (trong hàm `login()`): Sử dụng `password_verify()` để kiểm tra mật khẩu.

---

## ⚙️ Cài đặt

### 🔧 Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 / MariaDB
- Apache/Nginx
- Trình duyệt hiện đại (Chrome, Firefox, Edge, Safari)

### 📥 Hướng dẫn cài đặt

#### 1️⃣ **Clone repository**

```bash
git clone https://github.com/quangcaptain26-3/SUPER_STU_WEB.git
cd super-stu
```

#### 2️⃣ **Cấu hình database**

```sql
-- Tạo database
CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

- Mở phpMyAdmin hoặc MySQL Workbench
- Import file `database.sql` vào database vừa tạo

#### 3️⃣ **Cấu hình kết nối database**

Chỉnh sửa file `config/db.php`:

```php
private $host = 'localhost';
private $db_name = 'student_management';
private $username = 'root';
private $password = '';  // Nhập mật khẩu MySQL nếu có
```

#### 4️⃣ **Cấu hình web server**

**Nếu sử dụng XAMPP/WAMP:**

- Copy thư mục `super-stu` vào `htdocs` (XAMPP) hoặc `www` (WAMP)
- Truy cập: `http://localhost/super-stu/public/login.php`

**Nếu sử dụng hosting:**

- Upload tất cả files lên server
- Cấu hình domain trỏ đến thư mục `public`

#### 5️⃣ **Tài khoản mặc định**

```
👨‍💼 Super Admin
Username: admin
Password: admin123

👨‍🏫 Teacher
Username: teacher1
Password: teacher123

👨‍🎓 Student
Username: student1
Password: student123
```

⚠️ **CẢNH BÁO**: Thay đổi mật khẩu ngay sau lần đầu đăng nhập!

---

## 🎨 Giao diện

### Thiết kế

- **Responsive**: Tương thích đầy đủ trên mobile, tablet, desktop
- **Dark Mode**: Giao diện hiện đại, dễ nhìn
- **Gradient**: Nền màu gradient đẹp mắt
- **Smooth Animation**: Các hiệu ứng chuyển động mượt mà
- **Icon**: Sử dụng Font Awesome icons đẹp

### Trang chính

- Dashboard hiển thị thống kê nhanh
- Biểu đồ chart.js tương tác
- Thông báo realtime

---

## 🔧 Tùy chỉnh

### Đổi logo & tên ứng dụng

Chỉnh sửa trong các file HTML:

```html
<h4 class="text-white mb-4">
  <i class="fas fa-graduation-cap me-2"></i>
  Student Management
  <!-- Thay ở đây -->
</h4>
```

### Đổi màu gradient

File sidebar (`sidebar` class):

```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
/* Thay màu hex code */
```

### Thêm thêm trường dữ liệu

1. Chỉnh sửa migration file `database.sql`
2. Thêm trường vào bảng MySQL
3. Cập nhật form HTML (add.php, edit.php)
4. Thêm xử lý dữ liệu trong Controller

---

## 🌐 Triển khai hosting

### Bước chuẩn bị

1. **Cập nhật bảo mật**

   - Thay đổi tất cả mật khẩu mặc định
   - Bật HTTPS
   - Cấu hình SSL certificate

2. **Tối ưu hiệu suất**

   - Bật compression
   - Cache control
   - CDN cho static files

3. **Backup dữ liệu**
   - Backup database hàng tuần
   - Backup thư mục uploads

---

## 🔒 Bảo mật

Hệ thống được trang bị các biện pháp bảo mật hiện đại:

### ✅ **Xác thực & Mã hóa**

- 🔐 **Password Hashing**: Sử dụng `password_hash()` với Bcrypt (cost=12) - tiêu chuẩn an toàn nhất
- 🛡️ **Prepared Statements**: Chống SQL Injection trên tất cả truy vấn database
- 🔑 **Session Management**: Quản lý phiên làm việc an toàn với PHP sessions

### 🚫 **Phòng chống tấn công**

- 🛑 **CSRF Protection**: Token CSRF trên tất cả form (add, edit, delete)
- 🔓 **Access Control**: Kiểm tra quyền truy cập trước mỗi hành động
- 👤 **Resource Ownership**: Kiểm tra quyền sở hữu tài nguyên (học sinh chỉ xem dữ liệu của chính mình)
- 🧼 **Input Validation**: Sanitize và validate tất cả dữ liệu đầu vào
- 🔍 **XSS Prevention**: Escape output với `htmlspecialchars()` ở tất cả nơi hiển thị dữ liệu

### 📝 **Phân quyền chi tiết**

```
👨‍💼 Super Admin    → Quản lý tất cả + Quản lý người dùng
👨‍💼 Admin          → CRUD sinh viên & điểm + Xuất báo cáo
👨‍🏫 Teacher        → CRUD sinh viên & điểm + Thống kê + Xuất báo cáo
👨‍🎓 Student        → XEM THÔI (không sửa xóa thêm)
```

### ⚠️ **Lưu ý an toàn**

- Luôn cập nhật PHP & MySQL lên phiên bản mới nhất
- Sử dụng HTTPS trên production
- Đặt mật khẩu mạnh (tối thiểu 8 ký tự, chữ + số + ký tự đặc biệt)
- Thay đổi mật khẩu mặc định admin ngay khi đầu tiên đăng nhập
- Kiểm tra log truy cập định kỳ

---
## 📞 Hỗ trợ

Nếu bạn có bất kỳ câu hỏi hoặc góp ý nào, vui lòng tạo một **Issue** trên GitHub.

## Lib

Các thư viện và framework mã nguồn mở đã hỗ trợ:

- [PHPWord](https://github.com/PHPOffice/PHPWord) - Tạo file DOCX
- [TCPDF](https://tcpdf.org/) - Tạo file PDF
- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Chart.js](https://www.chartjs.org/) - Thư viện biểu đồ
- [Font Awesome](https://fontawesome.com/) - Icon library

---

<div align="center">

**⭐ Nếu project hữu ích, hãy cho một star nhé! ⭐**

**Phát triển bởi**: Minh Quang - TTM63ĐH

</div>