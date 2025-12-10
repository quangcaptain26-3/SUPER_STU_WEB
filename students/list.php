<?php
// --- KHỞI TẠO VÀ BẢO VỆ ---
// Bắt đầu hoặc tiếp tục phiên làm việc để truy cập `$_SESSION`.
session_start();
// Nạp các file cần thiết.
require_once '../utils.php';
require_once '../studentController.php';

// --- KIỂM TRA QUYỀN TRUY CẬP ---
// Đây là "cổng" bảo mật của trang. Nếu người dùng không có quyền `PERMISSION_VIEW_STUDENTS`,
// hàm `requirePermission` sẽ tự động chuyển hướng họ và dừng script.
requirePermission(PERMISSION_VIEW_STUDENTS);

// --- XỬ LÝ DỮ LIỆU ĐẦU VÀO (INPUT) ---
// Khởi tạo đối tượng StudentController để tương tác với CSDL.
$studentController = new StudentController();

// Lấy từ khóa tìm kiếm từ tham số `search` trên URL.
// `?? ''` là toán tử Null Coalescing, nếu `$_GET['search']` không tồn tại, `$search` sẽ là chuỗi rỗng.
$search = $_GET['search'] ?? '';

// Lấy số trang hiện tại từ tham số `page` trên URL.
// `intval()` chuyển đổi giá trị sang số nguyên. `max(1, ...)` đảm bảo số trang luôn >= 1.
$page = max(1, intval($_GET['page'] ?? 1));

// Thiết lập số lượng sinh viên hiển thị trên mỗi trang.
$limit = 10;

// Tính toán giá trị `OFFSET` cho câu lệnh SQL, dựa trên trang hiện tại.
// Ví dụ: Trang 1 -> offset 0. Trang 2 -> offset 10.
$offset = ($page - 1) * $limit;

// --- TRUY VẤN DỮ LIỆU TỪ CONTROLLER ---
// Gọi controller để lấy danh sách sinh viên cho trang hiện tại, có áp dụng tìm kiếm và phân trang.
$students = $studentController->getAllStudents($search, $limit, $offset);

// Gọi controller để lấy tổng số sinh viên (có áp dụng tìm kiếm) để tính toán phân trang.
$totalStudents = $studentController->getTotalStudents($search);

// Tính tổng số trang cần thiết. `ceil()` làm tròn lên.
// Ví dụ: 25 sinh viên, limit 10 -> 2.5 -> 3 trang.
$totalPages = ceil($totalStudents / $limit);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sinh viên - Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }

        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar tĩnh, được copy/paste qua các trang. -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>

                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="../public/index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <a class="nav-link active" href="list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-users me-2"></i>Quản lý sinh viên</h2>
                        <?php // Kiểm tra quyền trước khi hiển thị nút "Thêm sinh viên" ?>
                        <?php if (hasPermission(PERMISSION_ADD_STUDENTS)): ?>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm sinh viên
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Form tìm kiếm -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" class="form-control search-box" name="search"
                                            value="<?php echo htmlspecialchars($search); // Hiển thị lại từ khóa đã tìm kiếm ?>"
                                            placeholder="Tìm kiếm theo tên, mã SV, email...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-search me-1"></i>Tìm kiếm
                                    </button>
                                    <a href="list.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-refresh me-1"></i>Làm mới
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bảng danh sách sinh viên -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Danh sách sinh viên
                                <span class="badge bg-primary ms-2"><?php echo $totalStudents; // Hiển thị tổng số sinh viên tìm thấy ?></span>
                            </h5>
                            <div>
                                <?php // Kiểm tra quyền trước khi hiển thị các nút xuất file ?>
                                <?php if (hasPermission(PERMISSION_EXPORT_DATA)): ?>
                                    <a href="../exports/export_pdf.php?type=students" class="btn btn-outline-danger btn-sm me-2">
                                        <i class="fas fa-file-pdf me-1"></i>Xuất PDF
                                    </a>
                                    <a href="../exports/export_docx.php?type=students" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-file-word me-1"></i>Xuất DOCX
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Ảnh</th>
                                            <th>Mã SV</th>
                                            <th>Họ tên</th>
                                            <th>Ngày sinh</th>
                                            <th>Giới tính</th>
                                            <th>Email</th>
                                            <th>SĐT</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php // Nếu không có sinh viên nào, hiển thị thông báo ?>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Không tìm thấy sinh viên nào.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php // Lặp qua mảng sinh viên và hiển thị từng dòng ?>
                                            <?php foreach ($students as $index => $student): ?>
                                                <tr>
                                                    <?php // Tính số thứ tự dựa trên offset và index ?>
                                                    <td><?php echo $offset + $index + 1; ?></td>
                                                    <td>
                                                        <?php // Nếu có avatar, hiển thị ảnh ?>
                                                        <?php if ($student['avatar']): ?>
                                                            <img src="../uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>"
                                                                class="avatar" alt="Avatar">
                                                        <?php else: // Nếu không, hiển thị icon mặc định ?>
                                                            <div class="avatar bg-secondary d-flex align-items-center justify-content-center">
                                                                <i class="fas fa-user text-white"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($student['msv']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                                                    <td><?php echo formatDate($student['dob']); // Dùng hàm tiện ích để định dạng ngày ?></td>
                                                    <td>
                                        <?php
                                        // Chuyển đổi giá trị giới tính từ CSDL sang dạng hiển thị tiếng Việt
                                        $genderText = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
                                        echo $genderText[$student['gender']] ?? 'N/A';
                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                                    <td>
                                                        <?php // Nút Xem luôn hiển thị cho người có quyền xem ?>
                                                        <a href="view.php?id=<?php echo $student['id']; ?>"
                                                            class="btn btn-sm btn-outline-info btn-action" title="Xem">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php // Kiểm tra quyền sửa ?>
                                                        <?php if (hasPermission(PERMISSION_EDIT_STUDENTS)): ?>
                                                            <a href="edit.php?id=<?php echo $student['id']; ?>"
                                                                class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php // Kiểm tra quyền xóa ?>
                                                        <?php if (hasPermission(PERMISSION_DELETE_STUDENTS)): ?>
                                                            <button onclick="deleteStudent(<?php echo $student['id']; ?>)"
                                                                class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php // Chỉ hiển thị phân trang nếu có nhiều hơn 1 trang ?>
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php // Nút "Trang trước" ?>
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php 
                                        // Vòng lặp để hiển thị các nút số trang.
                                        // Logic này giúp chỉ hiển thị một vài trang xung quanh trang hiện tại.
                                        for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; // Thêm class 'active' cho trang hiện tại ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); // Giữ lại từ khóa tìm kiếm khi chuyển trang ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php // Nút "Trang sau" ?>
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/notifications.js"></script>
    <script>
        /**
         * Hàm xử lý sự kiện xóa sinh viên, được gọi khi người dùng nhấn nút xóa.
         * @param {number} id - ID của sinh viên cần xóa.
         */
        function deleteStudent(id) {
            // Sử dụng thư viện SweetAlert2 để hiển thị hộp thoại xác nhận chuyên nghiệp.
            Swal.fire({
                title: 'Bạn chắc chắn chứ?',
                text: "Hành động này sẽ xóa vĩnh viễn sinh viên và không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Vâng, xóa nó!',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                // `result.isConfirmed` là true nếu người dùng nhấn nút "Xóa".
                if (result.isConfirmed) {
                    // Hiển thị một thông báo loading trong khi chờ request xử lý.
                    Swal.fire({
                        title: 'Đang xử lý...',
                        text: 'Vui lòng chờ trong giây lát.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Sử dụng `fetch API` để gửi request đến server một cách bất đồng bộ.
                    fetch('delete.php', {
                        method: 'POST', // Sử dụng phương thức POST.
                        headers: {
                            // Header này cần thiết cho việc gửi dữ liệu dạng form-encoded.
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        // Body của request, chứa ID của sinh viên cần xóa.
                        // Cần thêm CSRF token ở đây trong môi trường production.
                        body: 'id=' + id
                    })
                    .then(response => response.json()) // Chuyển đổi response từ server (dạng chuỗi JSON) thành đối tượng JavaScript.
                    .then(data => {
                        // `data` là đối tượng JS, ví dụ: { success: true, message: "..." }
                        if (data.success) {
                            // Nếu server trả về thành công.
                            Swal.fire({
                                title: 'Đã xóa!',
                                text: data.message, // Hiển thị thông báo từ server.
                                icon: 'success'
                            }).then(() => {
                                // Sau khi người dùng nhấn OK, tải lại trang để cập nhật danh sách.
                                location.reload();
                            });
                        } else {
                            // Nếu server trả về lỗi.
                            Swal.fire({
                                title: 'Lỗi!',
                                text: data.message, // Hiển thị thông báo lỗi từ server.
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        // Xử lý các lỗi mạng hoặc lỗi không mong muốn khác.
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Đã có lỗi xảy ra khi gửi yêu cầu. ' + error,
                            icon: 'error'
                        });
                    });
                }
            });
        }
    </script>
</body>

</html>