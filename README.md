<div align="center">
  <img src="https://img.shields.io/badge/SUPER--STU-🎓-blueviolet" alt="Project Logo" width="200"/>
  <h1>🎓 Hệ thống Quản lý Sinh viên (Super-Stu)</h1>
  <p>
    <strong>Một hệ thống quản lý sinh viên PHP thuần đỉnh cao, an toàn và đầy đủ tính năng.</strong>
  </p>
  <p>
    <em>Không chỉ là CRUD, đây là minh chứng cho một ứng dụng PHP có cấu trúc, bảo mật và dễ bảo trì mà không cần framework!</em>
  </p>
</div>

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg?style=for-the-badge&logo=php)](https://php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg?style=for-the-badge&logo=mysql)](https://mysql.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.1.3-purple.svg?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)
[![Security](https://img.shields.io/badge/Security-Fortified-brightgreen.svg?style=for-the-badge)]()
[![Stars](https://img.shields.io/github/stars/quangcaptain26-3/SUPER_STU_WEB?style=for-the-badge&logo=github)](https://github.com/quangcaptain26-3/SUPER_STU_WEB/stargazers)

</div>

---

## 🌟 Giới thiệu

**Super-Stu** là một hệ thống quản lý sinh viên toàn diện, được xây dựng từ **PHP thuần** với kiến trúc hướng đối tượng (OOP), PDO, và áp dụng các biện pháp bảo mật hiện đại nhất. Dự án không chỉ cung cấp đầy đủ chức năng CRUD mà còn đi sâu vào phân quyền chi tiết (RBAC), xuất báo cáo động (PDF/DOCX), và thống kê dữ liệu trực quan.

Đây là một dự án hoàn hảo cho các bạn sinh viên, lập trình viên muốn tìm hiểu cách xây dựng một ứng dụng web PHP chuyên nghiệp từ đầu mà không phụ thuộc vào bất kỳ framework nào.

## ✨ Tính năng nổi bật

| Icon | Tính năng                    | Mô tả chi tiết                                                                                                                               |
| :--: | :--------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------- |
|  👥  | **Quản lý Sinh viên & Điểm** | CRUD đầy đủ, upload avatar, tìm kiếm, phân trang và tự động xếp loại học lực.                                                                |
|  🔐  | **Phân quyền RBAC**          | 4 cấp độ vai trò (Super Admin, Admin, Teacher, Student) với kiểm soát truy cập chi tiết. Giao diện tự động ẩn/hiện các chức năng theo quyền. |
|  📈  | **Thống kê & Báo cáo**       | Dashboard trực quan với biểu đồ động (Chart.js) và API riêng. Xuất danh sách, bảng điểm ra file **PDF** và **DOCX** chuyên nghiệp.           |
|  🛡️  | **Bảo mật Tối đa**           | Chống **SQL Injection** (100% Prepared Statements), **XSS** (dữ liệu được escape), **CSRF** (bảo vệ mọi form) và băm mật khẩu an toàn.       |
|  😂  | **Trải nghiệm độc đáo**      | Quy trình đăng ký "có một không hai" với điều khoản sử dụng kết hợp giữa sự nghiêm túc, hài hước và một yếu tố "gây sốc" khó quên!           |

---

## 🛠️ Công nghệ sử dụng

| Tầng          | Công nghệ                                                                                                                                                                                                                                                         | Mô tả                                                  |
| :------------ | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :----------------------------------------------------- |
| **Backend**   | ![PHP](https://img.shields.io/badge/PHP_7.4+-232F3E?style=flat-square&logo=php)                                                                                                                                                                                   | Xử lý logic nghiệp vụ, kiến trúc OOP, không framework. |
| **Database**  | ![MySQL](https://img.shields.io/badge/MySQL_5.7+-005C84?style=flat-square&logo=mysql)                                                                                                                                                                             | Lưu trữ dữ liệu. Giao tiếp an toàn qua PDO.            |
| **Frontend**  | ![HTML5](https://img.shields.io/badge/-HTML5-E34F26?style=flat-square&logo=html5) ![CSS3](https://img.shields.io/badge/-CSS3-1572B6?style=flat-square&logo=css3) ![JavaScript](https://img.shields.io/badge/-JavaScript-F7DF1E?style=flat-square&logo=javascript) | Giao diện người dùng.                                  |
| **Styling**   | ![Bootstrap](https://img.shields.io/badge/-Bootstrap_5-7952B3?style=flat-square&logo=bootstrap)                                                                                                                                                                   | Responsive UI framework.                               |
| **Biểu đồ**   | ![Chart.js](https://img.shields.io/badge/-Chart.js-FF6384?style=flat-square&logo=chartdotjs)                                                                                                                                                                      | Vẽ biểu đồ thống kê động trên dashboard.               |
| **Xuất file** | **TCPDF, PHPWord**                                                                                                                                                                                                                                                | Thư viện hàng đầu để tạo file PDF và DOCX từ PHP.      |
| **Alerts**    | ![SweetAlert2](https://img.shields.io/badge/-SweetAlert2-A5DC86?style=flat-square)                                                                                                                                                                                | Tạo các hộp thoại thông báo đẹp và chuyên nghiệp.      |

---

## 📁 Cấu trúc thư mục

Cấu trúc dự án được tổ chức một cách khoa học, tách biệt rõ ràng giữa logic, giao diện và tài nguyên, giúp dễ dàng bảo trì và mở rộng.

```
super-stu/
│
├── 📄 Controller Files (Root)
│   ├── authController.php          # 🔑 Logic xác thực: đăng nhập, đăng ký, quên mật khẩu
│   ├── studentController.php       # 🧑‍🎓 Logic CRUD cho sinh viên
│   ├── scoreController.php         # 📝 Logic CRUD cho điểm số
│   ├── subjectController.php       # 📚 Logic CRUD cho môn học
│   ├── enrollmentController.php    # 📋 Logic CRUD cho đăng ký môn học
│   ├── exportController.php        # 📤 Logic xử lý các yêu cầu xuất file
│   ├── middleware.php              # 🚦 Các hàm kiểm tra quyền truy cập (RBAC)
│   ├── utils.php                   # 🛠️ File "thần thánh": chứa các hàm tiện ích, định nghĩa quyền
│   ├── index.php                   # 📊 Trang dashboard chính sau khi đăng nhập
│   ├── database.sql                 # 💾 File dump của cơ sở dữ liệu để cài đặt ban đầu
│   ├── LICENSE                      # 📜 Giấy phép MIT
│   ├── README.md                    # 📖 File hướng dẫn này
│   ├── THUYET_TRINH.md              # 📝 Tài liệu thuyết trình
│   ├── WORKFLOW.txt                 # 🔄 Mô tả luồng hoạt động
│   └── giai_thich_nghiep_vu.txt    # 📋 Giải thích nghiệp vụ chi tiết
│
├── 📁 public/                        # 🌐 Thư mục gốc của web server, người dùng truy cập từ đây
│   ├── login.php                    # 🚪 Trang đăng nhập
│   ├── register.php                 # ✍️ Trang đăng ký tài khoản
│   ├── logout.php                   # 🚪 Script xử lý đăng xuất
│   ├── forgot_password.php          # 🔑 Trang quên mật khẩu
│   ├── reset_password.php           # 🔄 Trang đặt lại mật khẩu
│   ├── profile.php                  # 👤 Trang thông tin cá nhân
│   ├── users.php                    # 👥 Trang quản lý người dùng (chỉ Super Admin)
│   └── permissions.php              # 🔐 Trang quản lý phân quyền
│
├── 📁 students/                     # 📦 Module quản lý sinh viên
│   ├── list.php                     # 📋 Giao diện danh sách sinh viên
│   ├── add.php                      # ➕ Form thêm sinh viên mới
│   ├── edit.php                     # ✏️ Form sửa thông tin sinh viên
│   ├── view.php                     # 👁️ Trang xem chi tiết sinh viên
│   └── delete.php                   # 🗑️ Script xử lý xóa sinh viên (AJAX)
│
├── 📁 subjects/                     # 📚 Module quản lý môn học
│   ├── list.php                     # 📋 Giao diện danh sách môn học
│   ├── add.php                      # ➕ Form thêm môn học mới
│   ├── edit.php                     # ✏️ Form sửa thông tin môn học
│   └── delete.php                   # 🗑️ Script xử lý xóa môn học (AJAX)
│
├── 📁 enrollments/                  # 📋 Module đăng ký môn học
│   ├── list.php                     # 📋 Giao diện danh sách đăng ký
│   ├── add.php                      # ➕ Form đăng ký môn học
│   └── delete.php                   # 🗑️ Script xử lý xóa đăng ký (AJAX)
│
├── 📁 scores/                       # 📝 Module quản lý điểm số
│   ├── list.php                     # 📋 Giao diện danh sách điểm
│   ├── add.php                      # ➕ Form nhập điểm mới
│   ├── edit.php                     # ✏️ Form sửa điểm
│   └── delete.php                   # 🗑️ Script xử lý xóa điểm (AJAX)
│
├── 📁 charts/                       # 📈 Module thống kê và biểu đồ
│   ├── statistics.php               # 📊 Giao diện hiển thị các biểu đồ thống kê
│   └── api/
│       └── statistics.php           # 🔗 API endpoint trả về dữ liệu JSON cho biểu đồ
│
├── 📁 exports/                      # 📤 Module xuất báo cáo
│   ├── export_pdf.php               # 📄 Script tạo và xuất file PDF
│   └── export_docx.php              # 📑 Script tạo và xuất file DOCX
│
├── 📁 config/                       # ⚙️ Thư mục cấu hình
│   └── db.php                       # 🔌 Lớp `Database` quản lý kết nối PDO đến CSDL
│
├── 📁 assets/                       # 🎨 Thư mục tài nguyên tĩnh
│   ├── css/
│   │   └── notifications.css       # 🔔 CSS cho thông báo
│   ├── js/
│   │   ├── realtime.js              # ⏰ Script hiển thị thời gian realtime
│   │   ├── clock-widget.js          # 🕐 Widget đồng hồ
│   │   └── notifications.js         # 🔔 Script xử lý thông báo
│   └── libs/                        # 📚 Thư viện bên thứ ba
│       ├── tcpdf/                   # 📄 Thư viện TCPDF để tạo PDF
│       │   └── TCPDF-main/
│       │       ├── tcpdf.php        # File chính của TCPDF
│       │       ├── fonts/           # Font chữ hỗ trợ
│       │       ├── include/          # Các file include cần thiết
│       │       └── config/          # Cấu hình TCPDF
│       └── phpword/                 # 📑 Thư viện PHPWord để tạo DOCX
│           └── src/
│               └── PhpWord/         # Source code của PHPWord
│
└── 📁 uploads/                       # 📁 Thư mục lưu trữ file upload
    └── avatars/                     # 🖼️ Nơi lưu trữ ảnh đại diện của sinh viên
```

---

## 🚀 Hướng dẫn Cài đặt

Thực hiện các bước sau để triển khai dự án trên máy của bạn.

### 1. Yêu cầu hệ thống

- **PHP 7.4+**
- **MySQL 5.7+** hoặc **MariaDB**
- Web server (khuyến nghị **XAMPP** hoặc **WAMP**)
- Trình duyệt web hiện đại (Chrome, Firefox, Edge,...)

### 2. Các bước cài đặt

1.  **Clone repository về máy**:

    ```bash
    git clone https://github.com/quangcaptain26-3/SUPER_STU_WEB.git
    cd SUPER_STU_WEB
    ```

2.  **Tạo và Import Database**:

    - Mở **phpMyAdmin** (hoặc công cụ quản trị CSDL bạn dùng).
    - Tạo một database mới với tên `student_management` và `collation` là `utf8mb4_unicode_ci`.
      ```sql
      CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
      ```
    - Chọn database vừa tạo, vào tab `Import` và tải lên file `database.sql` có sẵn trong dự án.

3.  **Cấu hình kết nối CSDL**:

    - Mở file `config/db.php`.
    - Chỉnh sửa các thông tin sau cho phù hợp với môi trường của bạn (thường chỉ cần sửa mật khẩu nếu có).
      ```php
      private $host = 'localhost';
      private $db_name = 'student_management';
      private $username = 'root';
      private $password = ''; // <-- Điền mật khẩu MySQL của bạn ở đây
      ```

4.  **Chạy ứng dụng**:

    - Copy toàn bộ thư mục dự án vào `htdocs` của XAMPP.
    - Truy cập vào đường dẫn: `http://localhost/SUPER_STU_WEB/public/login.php`

5.  **Đăng nhập và trải nghiệm**:
    - **Super Admin**: `admin` / `admin123`
    - **Teacher**: `teacher1` / `teacher123`
    - **Student**: `student1` / `student123`

> ⚠️ **Bảo mật!** Vui lòng đổi mật khẩu của các tài khoản mặc định ngay sau lần đăng nhập đầu tiên để đảm bảo an toàn.

---

## 🌊 Luồng hoạt động của ứng dụng

Phần này đã được viết rất chi tiết trong file `THUYET_TRINH.md` và `giai_thich_nghiep_vu.txt`. Để tránh lặp lại thông tin, bạn có thể tham khảo trực tiếp các file đó để có cái nhìn sâu sắc nhất về kiến trúc và luồng dữ liệu của hệ thống.

---

## 🔧 Gỡ rối (Troubleshooting)

| Vấn đề                      | Nguyên nhân                                    | Giải pháp                                                                                              |
| :-------------------------- | :--------------------------------------------- | :----------------------------------------------------------------------------------------------------- |
| **Lỗi "Connection error"**  | Sai thông tin kết nối CSDL.                    | Kiểm tra lại `host`, `db_name`, `username`, `password` trong `config/db.php`. Đảm bảo MySQL đang chạy. |
| **"Bạn không có quyền..."** | Tài khoản của bạn không có quyền.              | Đây là tính năng! Hãy đăng nhập bằng tài khoản có quyền cao hơn (admin, teacher).                      |
| **Upload ảnh lỗi**          | Thư mục `uploads/avatars/` không có quyền ghi. | Đảm bảo thư mục tồn tại. Trên Linux/macOS, dùng `chmod -R 775 uploads`.                                |
| **Trang trắng/Lỗi 500**     | Lỗi cú pháp PHP.                               | Kiểm tra file log của web server. Bật hiển thị lỗi PHP nếu đang ở môi trường dev.                      |
| **Giao diện "vỡ"**          | Không tải được CSS/JS từ CDN.                  | Kiểm tra kết nối Internet. Mở Developer Tools (F12) xem lỗi ở tab Console.                             |
| **Icon không hiển thị**     | Font Awesome không tải được từ CDN.            | Xem phần **Xử lý lỗi Icon** bên dưới.                                                                  |

### 🎨 Xử lý lỗi Icon không hiển thị

Nếu các icon (Font Awesome) không hiển thị trên trang web, có thể do một trong các nguyên nhân sau:

#### **Nguyên nhân 1: Mất kết nối Internet**

- Font Awesome được tải từ CDN, cần kết nối Internet.
- **Giải pháp**: Kiểm tra kết nối mạng hoặc sử dụng Font Awesome offline (xem bên dưới).

#### **Nguyên nhân 2: CDN bị chặn hoặc chậm**

- Một số mạng có thể chặn CDN hoặc tốc độ tải chậm.
- **Giải pháp**: Sử dụng CDN khác hoặc tải Font Awesome về máy.

#### **Giải pháp: Cài đặt Font Awesome Offline**

1. **Tải Font Awesome**:

   - Truy cập: https://fontawesome.com/download
   - Tải bản **Free for Web** (hoặc Pro nếu có license)
   - Giải nén file ZIP

2. **Copy vào dự án**:

   ```bash
   # Copy thư mục webfonts và file css vào assets
   super-stu/
   └── assets/
       └── fontawesome/
           ├── css/
           │   └── all.min.css
           └── webfonts/
               └── [các file font]
   ```

3. **Cập nhật các file PHP**:

   - Tìm và thay thế tất cả các dòng:
     ```html
     <link
       href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
       rel="stylesheet"
     />
     ```
   - Thay bằng:
     ```html
     <link href="../assets/fontawesome/css/all.min.css" rel="stylesheet" />
     ```
   - **Lưu ý**: Điều chỉnh đường dẫn `../` tùy theo vị trí file:
     - File trong `public/` → dùng `../assets/`
     - File trong `students/`, `scores/`, `subjects/`, `enrollments/` → dùng `../../assets/`
     - File trong `charts/` → dùng `../../assets/`

4. **Tự động hóa với script** (tùy chọn):
   ```bash
   # Trên Linux/macOS, dùng sed để thay thế hàng loạt
   find . -name "*.php" -type f -exec sed -i 's|https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css|../assets/fontawesome/css/all.min.css|g' {} \;
   ```

#### **Giải pháp nhanh: Sử dụng CDN dự phòng**

Nếu không muốn tải về, có thể thêm CDN dự phòng trong `<head>`:

```html
<!-- CDN chính -->
<link
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
  rel="stylesheet"
/>
<!-- CDN dự phòng nếu CDN chính lỗi -->
<link
  href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css"
  rel="stylesheet"
  onerror="this.onerror=null;this.href='../assets/fontawesome/css/all.min.css';"
/>
```

#### **Kiểm tra Icon đã tải chưa**

Mở **Developer Tools** (F12) → Tab **Network** → Reload trang → Tìm file `all.min.css`:

- ✅ **Status 200**: Icon đã tải thành công
- ❌ **Status 404/Timeout**: Icon không tải được, cần dùng giải pháp offline

---

## 🔒 Phân tích bảo mật

Hệ thống được xây dựng với tư duy "bảo mật là trên hết" (Security-First Mindset).

#### ✔️ **Điểm mạnh**

- **Chống SQL Injection**: Triệt để sử dụng **PDO Prepared Statements**.
- **Chống XSS**: Dữ liệu luôn được escape bằng `htmlspecialchars()`.
- **Chống CSRF**: Mọi hành động nhạy cảm đều được bảo vệ bằng CSRF token.
- **Phân quyền chặt chẽ (RBAC)**: Kiểm tra quyền ở cả backend và frontend.
- **So sánh chuỗi an toàn**: Dùng `hash_equals()` chống timing attack.

#### ⚠️ **Điểm có thể cải thiện**

- **Thuật toán băm mật khẩu**: Hiện dùng `SHA-256`. Nên nâng cấp lên **BCRYPT** (`password_hash()` và `password_verify()`) để tăng cường khả năng chống brute-force.
- **Tách biệt layout**: Layout (header, footer, sidebar) đang bị lặp lại. Có thể tạo các file `layout/header.php`, `layout/footer.php` và `require` chúng ở các trang để dễ bảo trì (nguyên tắc DRY).

---

## 📞 Hỗ trợ

Nếu bạn có bất kỳ câu hỏi hay góp ý nào, đừng ngần ngại tạo một **Issue** trên GitHub repository của dự án.

<div align="center">

**Made with ❤️ by Minh Quang - TTM63ĐH**

**⭐ Nếu thấy dự án này hữu ích, hãy tặng một ngôi sao nhé! ⭐**

</div>
