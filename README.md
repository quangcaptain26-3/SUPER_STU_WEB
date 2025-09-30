# 🎓 Hệ thống quản lý sinh viên (Student Management System)

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.1.3-purple.svg)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

> **Hệ thống quản lý sinh viên toàn diện** được xây dựng bằng PHP, MySQL, Bootstrap và Chart.js với đầy đủ các chức năng CRUD, phân quyền người dùng, xuất báo cáo và thống kê trực quan.

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
- ✅ **Quyền hạn chi tiết**: Mỗi vai trò có quyền riêng biệt
- ✅ **Bảo mật cao**: Kiểm tra quyền truy cập cho từng chức năng
- ✅ **Quản lý người dùng**: Super Admin quản lý tất cả tài khoản

### 📈 **Thống kê và báo cáo**

- ✅ **Dashboard trực quan**: Tổng quan số liệu chính
- ✅ **Biểu đồ tương tác**: Sử dụng Chart.js hiển thị dữ liệu
- ✅ **Export đa định dạng**: Xuất PDF và DOCX chuyên nghiệp
- ✅ **Real-time**: Đồng hồ thời gian thực
- ✅ **Thống kê nâng cao**: Phân tích xu hướng, so sánh

### 🔑 **Xác thực người dùng**

- ✅ **Đăng nhập/Đăng xuất**: Hệ thống session bảo mật
- ✅ **Đăng ký**: Tạo tài khoản mới với validation
- ✅ **Quên mật khẩu**: Reset mật khẩu qua email
- ✅ **Profile**: Quản lý thông tin cá nhân
- ✅ **Đổi mật khẩu**: Bảo mật tài khoản

### 🗑️ **Modal xác nhận xóa**

- ✅ **SweetAlert2**: Modal xác nhận đẹp mắt và chuyên nghiệp
- ✅ **Loading state**: Hiển thị trạng thái xử lý
- ✅ **Thông báo kết quả**: Phản hồi rõ ràng cho người dùng
- ✅ **Animation mượt**: Hiệu ứng chuyển động tự nhiên
- ✅ **Responsive**: Tối ưu cho mọi thiết bị

---

## 🛠️ Công nghệ sử dụng

### **Backend**

- **PHP 7.4+**: Ngôn ngữ lập trình chính
- **MySQL 5.7+**: Cơ sở dữ liệu quan hệ
- **PDO**: Kết nối database an toàn

### **Frontend**

- **HTML5**: Cấu trúc trang web
- **CSS3**: Styling và responsive design
- **JavaScript (ES6)**: Tương tác người dùng
- **Bootstrap 5.1.3**: Framework UI responsive
- **Chart.js**: Thư viện biểu đồ tương tác
- **Font Awesome 6.0.0**: Icon library
- **SweetAlert2**: Modal xác nhận và thông báo

### **Libraries & Tools**

- **PHPWord**: Tạo file DOCX
- **TCPDF**: Tạo file PDF
- **Apache**: Web server
- **XAMPP**: Môi trường phát triển

---

## 📁 Cấu trúc dự án

```
super-stu/
├── 📁 config/
│   └── db.php                 # Cấu hình database
├── 📁 public/                 # Giao diện người dùng
│   ├── index.php             # Trang chủ
│   ├── login.php             # Đăng nhập
│   ├── register.php          # Đăng ký
│   ├── logout.php            # Đăng xuất
│   ├── forgot_password.php   # Quên mật khẩu
│   ├── reset_password.php    # Reset mật khẩu
│   ├── users.php             # Quản lý người dùng
│   ├── profile.php           # Thông tin cá nhân
│   └── permissions.php       # Ma trận quyền hạn
├── 📁 students/              # Module sinh viên
│   ├── list.php              # Danh sách sinh viên
│   ├── add.php               # Thêm sinh viên
│   ├── edit.php              # Sửa sinh viên
│   ├── delete.php            # Xóa sinh viên
│   └── view.php              # Xem chi tiết
├── 📁 scores/                # Module điểm số
│   ├── list.php              # Danh sách điểm
│   ├── add.php               # Thêm điểm
│   ├── edit.php              # Sửa điểm
│   └── delete.php            # Xóa điểm
├── 📁 charts/                # Module thống kê
│   ├── statistics.php        # Trang thống kê
│   └── api/
│       └── statistics.php    # API thống kê
├── 📁 exports/               # Module xuất báo cáo
│   ├── export_pdf.php        # Xuất PDF
│   └── export_docx.php       # Xuất DOCX
├── 📁 uploads/               # File upload
│   └── avatars/              # Ảnh đại diện sinh viên
├── 📁 assets/                # Tài nguyên tĩnh
│   ├── css/                  # CSS tùy chỉnh
│   ├── js/                   # JavaScript
│   └── libs/                 # Thư viện bên thứ 3
│       ├── phpword/          # PHPWord library
│       └── tcpdf/            # TCPDF library
├── 📄 authController.php     # Controller xác thực
├── 📄 studentController.php  # Controller sinh viên
├── 📄 scoreController.php    # Controller điểm số
├── 📄 exportController.php   # Controller xuất báo cáo
├── 📄 utils.php              # Hàm tiện ích
├── 📄 middleware.php         # Middleware bảo mật
├── 📄 database.sql           # Cơ sở dữ liệu
└── 📄 README.md              # Tài liệu hướng dẫn
```

---

## ⚙️ Cài đặt

### **1. Yêu cầu hệ thống**

| Thành phần | Phiên bản tối thiểu | Khuyến nghị |
| ---------- | ------------------- | ----------- |
| PHP        | 7.4                 | 8.0+        |
| MySQL      | 5.7                 | 8.0+        |
| Apache     | 2.4                 | 2.4+        |
| RAM        | 512MB               | 1GB+        |
| Disk       | 500MB               | 1GB+        |

### **2. Cài đặt local (XAMPP)**

#### **Bước 1: Tải và cài đặt XAMPP**

```bash
# Tải XAMPP từ: https://www.apachefriends.org/
# Cài đặt và khởi động Apache + MySQL
```

#### **Bước 2: Clone project**

```bash
# Clone hoặc download project
git clone https://github.com/quangcaptain26-3/SUPER_STU_WEB.git
# Hoặc giải nén file ZIP vào thư mục htdocs
```

#### **Bước 3: Import database**

```sql
-- Tạo database
CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import file database.sql
mysql -u root -p student_management < database.sql
```

#### **Bước 4: Cấu hình database**

Chỉnh sửa file `config/db.php`:

```php
<?php
$host = 'localhost';
$dbname = 'student_management';
$username = 'root';
$password = ''; // Mật khẩu MySQL của bạn
$charset = 'utf8mb4';
?>
```

#### **Bước 5: Cấu hình quyền thư mục**

```bash
# Tạo thư mục uploads
mkdir uploads/avatars
chmod 755 uploads/avatars

# Cấu hình quyền ghi
chmod 755 exports/
```

#### **Bước 6: Truy cập ứng dụng**

```
http://localhost/super-stu/public/
```

### **3. Tài khoản mặc định**

| Vai trò     | Username | Password   | Quyền hạn                   |
| ----------- | -------- | ---------- | --------------------------- |
| Super Admin | admin    | admin123   | Tất cả quyền                |
| Admin       | admin2   | admin123   | Quản lý sinh viên, điểm     |
| Teacher     | teacher1 | teacher123 | Xem sinh viên, quản lý điểm |
| Student     | student1 | student123 | Xem thông tin cá nhân       |

> ⚠️ **Lưu ý bảo mật**: Đổi mật khẩu mặc định ngay sau khi cài đặt!

---

## 👤 Hệ thống phân quyền

### **🔴 Super Admin**

- ✅ Quản lý tất cả người dùng (CRUD)
- ✅ Quản lý sinh viên (CRUD)
- ✅ Quản lý điểm số (CRUD)
- ✅ Xem thống kê và báo cáo
- ✅ Xuất báo cáo PDF/DOCX
- ✅ Cấu hình hệ thống

### **🟠 Admin**

- ✅ Quản lý sinh viên (CRUD)
- ✅ Quản lý điểm số (CRUD)
- ✅ Xem thống kê và báo cáo
- ✅ Xuất báo cáo PDF/DOCX
- ❌ Quản lý người dùng

### **🟡 Teacher**

- ✅ Xem danh sách sinh viên
- ✅ Thêm sinh viên
- ✅ Sửa thông tin sinh viên
- ✅ Quản lý điểm số (CRUD)
- ✅ Xem thống kê
- ✅ Xuất báo cáo PDF/DOCX
- ❌ Xóa sinh viên
- ❌ Quản lý người dùng

### **🟢 Student**

- ✅ Xem thông tin cá nhân
- ✅ Xem điểm số của mình
- ❌ Quản lý dữ liệu

---

## 📊 Chức năng thống kê

### **Dashboard tổng quan**

- 📈 Tổng số sinh viên (Nam/Nữ)
- 📊 Điểm trung bình theo môn học
- 📉 Phân bố điểm theo xếp loại
- ⏰ Thời gian thực

### **Biểu đồ tương tác**

- 📊 **Biểu đồ cột**: So sánh điểm theo môn
- 🥧 **Biểu đồ tròn**: Phân bố xếp loại
- 📈 **Biểu đồ đường**: Xu hướng điểm theo thời gian
- 📋 **Bảng thống kê**: Chi tiết số liệu

### **Bộ lọc thông minh**

- 🎯 Lọc theo học kỳ
- 👤 Lọc theo sinh viên
- 📚 Lọc theo môn học
- 📅 Lọc theo khoảng thời gian

---

## 📄 Xuất báo cáo

### **PDF Export**

- ✅ Format chuyên nghiệp
- ✅ Bảng dữ liệu đẹp mắt
- ✅ Thống kê chi tiết
- ✅ Hỗ trợ tiếng Việt
- ✅ Logo và header tùy chỉnh

### **DOCX Export**

- ✅ Format Microsoft Word chuẩn
- ✅ Dễ dàng chỉnh sửa
- ✅ Bảng dữ liệu có định dạng
- ✅ Thống kê và biểu đồ
- ✅ Tương thích Office 365

### **Tính năng xuất báo cáo**

- 📋 Danh sách sinh viên
- 📊 Bảng điểm chi tiết
- 📈 Thống kê tổng hợp
- 🎯 Báo cáo theo bộ lọc

---

## 🎨 Giao diện

### **Thiết kế hiện đại**

- 🎨 **Bootstrap 5**: Framework UI responsive
- 📱 **Mobile-first**: Tối ưu cho mọi thiết bị
- 🌈 **Color scheme**: Bảng màu chuyên nghiệp
- ⚡ **Performance**: Tải trang nhanh chóng

### **Trải nghiệm người dùng**

- 🔍 **Tìm kiếm thông minh**: Gợi ý và auto-complete
- 📄 **Phân trang**: Hiển thị dữ liệu tối ưu
- ⚡ **Real-time**: Cập nhật thời gian thực
- 🎯 **Intuitive**: Giao diện trực quan, dễ sử dụng

### **Responsive Design**

- 💻 **Desktop**: Giao diện đầy đủ tính năng
- 📱 **Tablet**: Tối ưu cho màn hình trung bình
- 📱 **Mobile**: Giao diện thân thiện với điện thoại

---

## 🔧 Tùy chỉnh

### **Thêm quyền mới**

1. Định nghĩa constant trong `utils.php`:

```php
const PERMISSION_NEW_FEATURE = 'permission_new_feature';
```

2. Thêm vào `getRolePermissions()`:

```php
'admin' => [
    // ... existing permissions
    PERMISSION_NEW_FEATURE
]
```

3. Sử dụng trong controller:

```php
requirePermission(PERMISSION_NEW_FEATURE);
```

### **Thêm chức năng mới**

1. **Tạo controller**: `newController.php`
2. **Tạo views**: Thư mục `new/` với các file PHP
3. **Cập nhật navigation**: Thêm menu trong `public/index.php`
4. **Cập nhật phân quyền**: Thêm quyền mới

### **Tùy chỉnh giao diện**

- **CSS**: Chỉnh sửa trong `assets/css/`
- **JavaScript**: Thêm vào `assets/js/`
- **Layout**: Chỉnh sửa `public/index.php`
- **Icons**: Sử dụng Font Awesome

---

## 🌐 Triển khai hosting

### **Yêu cầu hosting**

- **PHP**: 7.4+ với các extension: mysqli, gd, mbstring, zip
- **MySQL**: 5.7+ hoặc MariaDB 10.2+
- **Web Server**: Apache hoặc Nginx
- **SSL**: HTTPS (khuyến nghị)
- **Disk Space**: 1GB+ (khuyến nghị 2GB+)

### **Các nhà cung cấp hosting phù hợp**

#### **Hosting Việt Nam**

- **Tenten**: Giá rẻ, hỗ trợ PHP/MySQL tốt
- **Hostinger**: Giao diện dễ dùng, hiệu suất cao
- **Vietnix**: Ổn định, hỗ trợ kỹ thuật tốt
- **AZDIGI**: Chuyên nghiệp, hiệu suất cao

#### **Hosting quốc tế**

- **DigitalOcean**: VPS, linh hoạt cao
- **AWS**: Chuyên nghiệp, mở rộng dễ dàng
- **Vultr**: Giá cả hợp lý, hiệu suất tốt

### **Hướng dẫn triển khai**

#### **Bước 1: Chuẩn bị**

```bash
# Nén project (loại bỏ file không cần thiết)
zip -r super-stu.zip . -x "*.git*" "*.txt" "*.md" "node_modules/*"
```

#### **Bước 2: Upload lên hosting**

- Upload file ZIP lên thư mục `public_html`
- Giải nén file ZIP
- Xóa file ZIP sau khi giải nén

#### **Bước 3: Cấu hình database**

- Tạo database mới trên hosting
- Import file `database.sql`
- Cập nhật `config/db.php` với thông tin hosting

#### **Bước 4: Cấu hình quyền**

```bash
# Cấu hình quyền thư mục
chmod 755 uploads/avatars/
chmod 755 exports/
```

#### **Bước 5: Kiểm tra**

- Truy cập website
- Kiểm tra đăng nhập
- Test các chức năng chính

---

## 🐛 Troubleshooting

### **Lỗi kết nối database**

```php
// Kiểm tra config/db.php
$host = 'localhost';        // Hoặc IP hosting
$dbname = 'your_database';
$username = 'your_username';
$password = 'your_password';
```

**Giải pháp:**

- ✅ Kiểm tra thông tin database
- ✅ Đảm bảo MySQL đang chạy
- ✅ Kiểm tra quyền truy cập database

### **Lỗi upload file**

**Nguyên nhân:**

- Quyền thư mục không đúng
- Cấu hình PHP không phù hợp

**Giải pháp:**

```bash
# Cấu hình quyền thư mục
chmod 755 uploads/avatars/
```

```ini
# Cấu hình PHP (php.ini)
upload_max_filesize = 10M
post_max_size = 20M
max_execution_time = 300
```

### **Lỗi export PDF/DOCX**

**Nguyên nhân:**

- Thiếu thư viện
- Quyền ghi file không đủ
- Memory limit thấp

**Giải pháp:**

```ini
# Cấu hình PHP
memory_limit = 256M
max_execution_time = 600
```

### **Lỗi hiển thị tiếng Việt**

**Nguyên nhân:**

- Encoding không đúng
- Database charset không phù hợp

**Giải pháp:**

```sql
-- Cấu hình database
ALTER DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 📞 Hỗ trợ

### **Tài liệu tham khảo**

- 📖 [PHP Documentation](https://www.php.net/docs.php)
- 📖 [MySQL Documentation](https://dev.mysql.com/doc/)
- 📖 [Bootstrap Documentation](https://getbootstrap.com/docs/)
- 📖 [Chart.js Documentation](https://www.chartjs.org/docs/)

### **Liên hệ hỗ trợ**

- 🐛 **Báo lỗi**: Tạo issue trên GitHub
- 💡 **Đề xuất tính năng**: Tạo feature request
- 📧 **Email**: [phamminhquang2603@gmail.com]
- 💬 **Discord**: [Discord Server Link]

### **FAQ thường gặp**

**Q: Làm sao để thay đổi giao diện?**
A: Chỉnh sửa file CSS trong `assets/css/` hoặc thay đổi theme Bootstrap.

**Q: Có thể thêm chức năng mới không?**
A: Có, bạn có thể thêm module mới theo hướng dẫn trong phần Tùy chỉnh.

**Q: Hệ thống có hỗ trợ đa ngôn ngữ không?**
A: Hiện tại chỉ hỗ trợ tiếng Việt, có thể mở rộng thêm ngôn ngữ khác.

**Q: Làm sao để backup dữ liệu?**
A: Export database MySQL và backup thư mục `uploads/`.

---

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

**Copyright © 2024 Student Management Team**

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
