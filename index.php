<?php
// Bắt đầu hoặc tiếp tục phiên làm việc để truy cập dữ liệu session.
session_start();
// Nạp file tiện ích chứa các hàm kiểm tra đăng nhập và phân quyền.
require_once 'utils.php';

// --- BẢO VỆ TRANG: YÊU CẦU ĐĂNG NHẬP ---
// Kiểm tra xem người dùng đã đăng nhập hay chưa bằng cách kiểm tra sự tồn tại của `$_SESSION['user_id']`.
if (!isLoggedIn()) {
    // Nếu chưa đăng nhập, chuyển hướng người dùng về trang login.
    header('Location: public/login.php');
    // Dừng thực thi script ngay lập tức.
    exit();
}

// --- LẤY THÔNG TIN NGƯỜI DÙNG TỪ SESSION ---
// Lấy vai trò của người dùng đã được lưu trong session khi đăng nhập.
$userRole = $_SESSION['role'];
// Lấy tên đăng nhập của người dùng.
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar, .offcanvas-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link, .offcanvas-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active,
        .offcanvas-sidebar .nav-link:hover, .offcanvas-sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .card-body {
            padding: 2rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        /* Button hamburger cho mobile */
        .menu-toggle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 1.2rem;
        }
        .menu-toggle:hover {
            background: linear-gradient(135deg, #5568d3 0%, #653a8f 100%);
            color: white;
        }

        /* Ẩn đồng hồ trên mobile */
        @media (max-width: 767.98px) {
            .header-clock {
                display: none !important;
            }
        }

        /* Fix click offcanvas mobile */
        .offcanvas-sidebar { z-index: 1050; }
        .offcanvas-backdrop { z-index: 1040; }
        .offcanvas-sidebar .nav-link {
            pointer-events: auto !important;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(255,255,255,0.3);
        }
        @media (max-width: 767.98px) {
            .offcanvas-sidebar .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Desktop (ẩn trên mobile, hiện từ md trở lên) -->
            <div class="col-md-3 col-lg-2 sidebar p-0 d-none d-md-block">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($username); ?>
                        <span class="badge <?php echo getRoleBadgeClass($userRole); ?> ms-2">
                            <?php echo getRoleDisplayName($userRole); ?>
                        </span>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-calendar me-2"></i>
                        <span id="current-date"></span>
                    </div>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    
                    <a class="nav-link" href="students/list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    
                    <a class="nav-link" href="scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    
                    <a class="nav-link" href="charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    
                    <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="public/users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="public/profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    
                    <a class="nav-link" href="public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Button hamburger chỉ hiện trên mobile -->
                            <button class="btn menu-toggle d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                        </div>
                    <div class="text-muted header-clock">
                        <i class="fas fa-calendar me-1"></i>
                        <span class="realtime-datetime"><?php echo date('d/m/Y H:i'); // Hiển thị thời gian lúc tải trang ?></span>
                        <span class="live-indicator"></span>
                    </div>
                    </div>
                    
                    <!-- Các thẻ thống kê nhanh, dữ liệu sẽ được điền bởi JavaScript -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <div class="stat-number" id="totalStudents">-</div>
                                    <div>Tổng sinh viên</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-male fa-2x mb-3"></i>
                                    <div class="stat-number" id="maleStudents">-</div>
                                    <div>Sinh viên nam</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-female fa-2x mb-3"></i>
                                    <div class="stat-number" id="femaleStudents">-</div>
                                    <div>Sinh viên nữ</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                                    <div class="stat-number" id="avgScore">-</div>
                                    <div>Điểm TB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vùng chứa các biểu đồ, sẽ được vẽ bởi Chart.js -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar me-2"></i>Phân bố giới tính</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="genderChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-book me-2"></i>Điểm TB theo môn (Top 5)</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="subjectChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hoạt động gần đây (hiện tại là dữ liệu tĩnh) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-clock me-2"></i>Hoạt động gần đây</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-user-plus text-success me-2"></i>
                                                Sinh viên mới đăng ký
                                            </div>
                                            <small class="text-muted">2 phút trước</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-edit text-primary me-2"></i>
                                                Cập nhật thông tin sinh viên
                                            </div>
                                            <small class="text-muted">15 phút trước</small>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-chart-line text-info me-2"></i>
                                                Nhập điểm mới
                                            </div>
                                            <small class="text-muted">1 giờ trước</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Sidebar cho Mobile -->
    <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">
                <i class="fas fa-graduation-cap me-2"></i>
                Student Management
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="p-3">
                <div class="text-white-50 mb-3">
                    <i class="fas fa-user me-2"></i>
                    <?php echo htmlspecialchars($username); ?>
                    <span class="badge <?php echo getRoleBadgeClass($userRole); ?> ms-2">
                        <?php echo getRoleDisplayName($userRole); ?>
                    </span>
                </div>
                <div class="text-white-50">
                    <i class="fas fa-calendar me-2"></i>
                    <span id="current-date-mobile"></span>
                </div>
            </div>
            
            <nav class="nav flex-column px-3">
                <a class="nav-link active" href="index.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-home me-2"></i>Trang chủ
                </a>
                
                <a class="nav-link" href="students/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-users me-2"></i>Quản lý sinh viên
                </a>
                
                <a class="nav-link" href="scores/list.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                </a>
                
                <a class="nav-link" href="charts/statistics.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-chart-bar me-2"></i>Thống kê
                </a>
                
                <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                <a class="nav-link" href="public/users.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                </a>
                <?php endif; ?>
                
                <a class="nav-link" href="public/profile.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-user me-2"></i>Thông tin cá nhân
                </a>
                
                <a class="nav-link" href="public/logout.php" data-bs-dismiss="offcanvas">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </nav>
        </div>
    </div>

    <!-- Nạp các thư viện JavaScript từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Nạp các file JS tùy chỉnh của dự án -->
    <script src="assets/js/realtime.js"></script>
    <script>
        // Đóng offcanvas trước khi điều hướng (mobile)
        document.addEventListener('DOMContentLoaded', () => {
            const offcanvasEl = document.getElementById('mobileSidebar');
            if (!offcanvasEl) return;

            const links = offcanvasEl.querySelectorAll('.nav-link[href]');
            links.forEach(link => {
                link.addEventListener('click', (event) => {
                    const target = link.getAttribute('href');
                    if (!target) return;
                    event.preventDefault();

                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    bsOffcanvas.hide();

                    setTimeout(() => { window.location.href = target; }, 150);
                });
            });
        });

        // Hiển thị thứ ngày tháng hiện tại
        function updateCurrentDate() {
            const now = new Date();
            const days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
            const dayName = days[now.getDay()];
            const dateStr = now.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const dateElement = document.getElementById('current-date');
            const dateElementMobile = document.getElementById('current-date-mobile');
            const dateText = `${dayName}, ${dateStr}`;
            if (dateElement) {
                dateElement.textContent = dateText;
            }
            if (dateElementMobile) {
                dateElementMobile.textContent = dateText;
            }
        }
        updateCurrentDate(); // Cập nhật ngay lập tức
        
        // --- TẢI DỮ LIỆU BẤT ĐỒNG BỘ VÀ VẼ BIỂU ĐỒ ---
        // Sử dụng `fetch API` của trình duyệt để gửi một request GET đến API thống kê.
        // Đây là cách tiếp cận hiện đại để lấy dữ liệu mà không cần tải lại trang.
        fetch('charts/api/statistics.php')
            .then(response => response.json()) // Sau khi nhận được response, chuyển đổi nó từ chuỗi JSON thành đối tượng JavaScript.
            .then(data => { // `data` lúc này là một đối tượng JS chứa toàn bộ thông tin thống kê.
                // 1. Cập nhật các thẻ thống kê nhanh.
                // `|| 0` để đảm bảo nếu dữ liệu không tồn tại thì sẽ hiển thị số 0.
                document.getElementById('totalStudents').textContent = data.total_students || 0;
                document.getElementById('maleStudents').textContent = data.male_students || 0;
                document.getElementById('femaleStudents').textContent = data.female_students || 0;
                document.getElementById('avgScore').textContent = data.avg_score || 'N/A';
                
                // 2. Vẽ biểu đồ phân bố giới tính (Bar Chart - Cột).
                // Lấy context 2D của thẻ canvas.
                const genderCtx = document.getElementById('genderChart').getContext('2d');
                new Chart(genderCtx, {
                    type: 'bar', // Chọn loại biểu đồ là bar (cột).
                    data: {
                        labels: ['Nam', 'Nữ', 'Khác'], // Nhãn cho mỗi cột của biểu đồ.
                        datasets: [{
                            label: 'Số lượng',
                            // Dữ liệu tương ứng với các nhãn.
                            data: [data.male_students || 0, data.female_students || 0, data.other_students || 0],
                            // Màu nền cho mỗi cột.
                            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56'],
                            borderColor: ['#36A2EB', '#FF6384', '#FFCE56'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true, // Cho phép biểu đồ tự điều chỉnh kích thước theo container.
                        maintainAspectRatio: false, // Không giữ tỷ lệ khung hình cố định, giúp biểu đồ lấp đầy container.
                        scales: {
                            y: {
                                beginAtZero: true // Trục Y bắt đầu từ 0
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Ẩn chú giải
                            }
                        }
                    }
                });
                
                // 3. Vẽ biểu đồ điểm trung bình theo môn học (Top 5).
                const subjectCtx = document.getElementById('subjectChart').getContext('2d');
                new Chart(subjectCtx, {
                    type: 'bar', // Chọn loại biểu đồ cột.
                    data: {
                        labels: data.subject_labels || [], // Nhãn cho trục X (tên các môn học).
                        datasets: [{
                            label: 'Điểm trung bình',
                            data: data.subject_scores || [], // Dữ liệu cho trục Y (điểm trung bình).
                            backgroundColor: 'rgba(102, 126, 234, 0.8)', // Màu nền cho các cột.
                            borderColor: '#667eea', // Màu viền cho các cột.
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true, // Bắt đầu trục Y từ 0.
                                max: 10 // Giá trị tối đa của trục Y là 10
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Ẩn chú giải
                            }
                        }
                    }
                });
            })
            .catch(error => {
                // Nếu có lỗi xảy ra trong quá trình `fetch` (ví dụ: mất mạng, API lỗi 500).
                console.error('Lỗi khi tải dữ liệu thống kê:', error);
                // Có thể hiển thị một thông báo lỗi trên UI cho người dùng ở đây.
            });
    </script>
</body>
</html>
