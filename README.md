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
- [⚙️ Cài đặt](#️-cài-đặt)
- [👤 Hệ thống phân quyền](#-hệ-thống-phân-quyền)
- [📊 Chức năng thống kê](#-chức-năng-thống-kê)
- [📄 Xuất báo cáo](#-xuất-báo-cáo)
- [🎨 Giao diện](#-giao-diện)
- [🔧 Tùy chỉnh](#-tùy-chỉnh)
- [🌐 Triển khai hosting](#-triển-khai-hosting)
- [🐛 Troubleshooting](#-troubleshooting)
- [📞 Hỗ trợ](#-hỗ-trợ)
- [🔒 Bảo mật](#-bảo-mật)

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

| Tầng         | Công nghệ                                          |
| ------------ | -------------------------------------------------- |
| **Frontend** | HTML5, CSS3, Bootstrap 5.1.3, JavaScript (Vanilla) |
| **Backend**  | PHP 7.4+ (OOP)                                     |
| **Database** | MySQL 5.7+ / MariaDB                               |
| **Chart**    | Chart.js                                           |
| **Export**   | TCPDF (PDF), PHPWord (DOCX)                        |
| **Icons**    | Font Awesome 6.0                                   |
| **Alerts**   | SweetAlert2                                        |

---

## 📁 Cấu trúc dự án

```
super-stu/
├── 📄 index.php                 # File chính (redirect)
├── 📄 authController.php        # Xử lý đăng nhập/đăng ký
├── 📄 studentController.php     # Xử lý CRUD sinh viên
├── 📄 scoreController.php       # Xử lý CRUD điểm
├── 📄 exportController.php      # Xử lý xuất dữ liệu
├── 📄 middleware.php            # Middleware phân quyền
├── 📄 utils.php                 # Hàm tiện ích chung
│
├── 📁 config/
│   └── 📄 db.php               # Cấu hình database
│
├── 📁 public/                   # File công khai
│   ├── 📄 index.php            # Trang chủ
│   ├── 📄 login.php            # Đăng nhập
│   ├── 📄 register.php         # Đăng ký
│   ├── 📄 forgot_password.php  # Quên mật khẩu
│   ├── 📄 reset_password.php   # Đặt lại mật khẩu
│   ├── 📄 profile.php          # Hồ sơ người dùng
│   ├── 📄 users.php            # Quản lý người dùng
│   ├── 📄 permissions.php      # Quản lý quyền
│   └── 📄 logout.php           # Đăng xuất
│
├── 📁 students/                 # Quản lý sinh viên
│   ├── 📄 list.php             # Danh sách sinh viên
│   ├── 📄 add.php              # Thêm sinh viên
│   ├── 📄 edit.php             # Sửa sinh viên
│   ├── 📄 delete.php           # Xóa sinh viên
│   └── 📄 view.php             # Chi tiết sinh viên
│
├── 📁 scores/                   # Quản lý điểm
│   ├── 📄 list.php             # Danh sách điểm
│   ├── 📄 add.php              # Thêm điểm
│   ├── 📄 edit.php             # Sửa điểm
│   └── 📄 delete.php           # Xóa điểm
│
├── 📁 charts/                   # Thống kê & biểu đồ
│   ├── 📄 statistics.php       # Trang thống kê chính
│   └── 📁 api/
│       └── 📄 statistics.php   # API thống kê
│
├── 📁 exports/                  # Xuất dữ liệu
│   ├── 📄 export_pdf.php       # Export PDF
│   └── 📄 export_docx.php      # Export Word
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── 📄 notifications.css
│   ├── 📁 js/
│   │   ├── 📄 notifications.js
│   │   ├── 📄 realtime.js
│   │   └── 📄 clock-widget.js
│   └── 📁 libs/
│       ├── 📁 phpword/
│       └── 📁 tcpdf/
│
├── 📁 uploads/
│   └── 📁 avatars/             # Ảnh đại diện sinh viên
│
└── 📄 database.sql             # Database dump
```

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

## 👤 Hệ thống phân quyền

### Các vai trò & quyền hạn

| Quyền              | Super Admin | Admin | Teacher | Student |
| ------------------ | :---------: | :---: | :-----: | :-----: |
| Xem sinh viên      |     ✅      |  ✅   |   ✅    |   ✅    |
| Thêm sinh viên     |     ✅      |  ✅   |   ✅    |   ❌    |
| Sửa sinh viên      |     ✅      |  ✅   |   ✅    |   ❌    |
| Xóa sinh viên      |     ✅      |  ✅   |   ❌    |   ❌    |
| Xem điểm           |     ✅      |  ✅   |   ✅    |   ✅    |
| Thêm/Sửa điểm      |     ✅      |  ✅   |   ✅    |   ❌    |
| Xóa điểm           |     ✅      |  ✅   |   ❌    |   ❌    |
| Thống kê           |     ✅      |  ✅   |   ✅    |   ❌    |
| Xuất báo cáo       |     ✅      |  ✅   |   ✅    |   ❌    |
| Quản lý người dùng |     ✅      |  ❌   |   ❌    |   ❌    |

---

## 📊 Chức năng thống kê

### Biểu đồ & Phân tích

- **Biểu đồ phân bố điểm**: Xem cách điểm phân bố trên các lớp (A, B, C, D)
- **Xu hướng điểm**: Theo dõi sự thay đổi điểm theo thời gian
- **Top sinh viên**: Xem danh sách sinh viên có điểm cao nhất
- **Thống kê môn học**: Phân tích hiệu suất theo từng môn học
- **So sánh**: So sánh kết quả giữa các học kỳ

---

## 📄 Xuất báo cáo

### Định dạng hỗ trợ

#### 📕 **PDF**

- Xuất danh sách sinh viên / điểm thành PDF
- Có header, footer, đánh số trang
- Hỗ trợ tiếng Việt đầy đủ

#### 📗 **DOCX (Word)**

- Xuất sang định dạng Word
- Có bảng định dạng đẹp
- Dễ chỉnh sửa sau khi xuất

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

## 🐛 Troubleshooting

### Q: Lỗi "Cannot connect to database"

**A:** Kiểm tra:

- MySQL service đang chạy?
- Thông tin host, username, password đúng chưa? (trong `config/db.php`)
- Database `student_management` đã được tạo chưa?
- User MySQL có quyền truy cập không?

### Q: Upload ảnh không hoạt động

**A:** Kiểm tra:

- Thư mục `uploads/avatars/` tồn tại?
- Thư mục có quyền ghi (777)?
- File size không vượt quá 5MB?
- Định dạng là JPG, PNG, GIF?

```bash
chmod 777 uploads/avatars/
```

### Q: Đăng nhập không được

**A:** Kiểm tra:

- Mật khẩu đúng chưa?
- Tài khoản có tồn tại trong database?
- Session hoạt động bình thường?

### Q: Xuất PDF/Word bị lỗi

**A:** Kiểm tra:

- GD library được bật trong PHP?
- ZipArchive extension được bật?
- Folder uploads có quyền ghi?

### Q: Trang không tải CSS/JS

**A:** Kiểm tra:

- File tồn tại trong thư mục?
- Path URL đúng chưa?
- Quyền file đúng chưa?

### Q: Toàn bộ hệ thống chậm

**A:** Cách khắc phục:

- Thêm index vào database
- Tối ưu query
- Bật PHP caching (APCu, OPcache)
- Tăng RAM & CPU

### Q: Ký tự tiếng Việt bị lỗi

**A:** Kiểm tra:

- Database charset: `utf8mb4`
- Table charset: `utf8mb4`
- Connection charset trong PHP: `SET NAMES utf8mb4`

### Q: Hiện tại chỉ hỗ trợ tiếng Việt, có thể mở rộng thêm ngôn ngữ khác.

A: Có thể, cần tách hardcode text ra file config ngôn ngữ.

### Q: Làm sao để backup dữ liệu?

A: Export database MySQL và backup thư mục `uploads/`.

---

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

**Copyright © 2024 Student Management Team**

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

### 🔄 **Validation Multi-Layer**

- Backend: PHP validation trước khi lưu database
- Frontend: JavaScript validation cho trải nghiệm tốt
- Database: Constraint kiểm tra tính hợp lệ của dữ liệu

### ⚠️ **Lưu ý an toàn**

- Luôn cập nhật PHP & MySQL lên phiên bản mới nhất
- Sử dụng HTTPS trên production
- Đặt mật khẩu mạnh (tối thiểu 8 ký tự, chữ + số + ký tự đặc biệt)
- Thay đổi mật khẩu mặc định admin ngay khi đầu tiên đăng nhập
- Kiểm tra log truy cập định kỳ

---

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
