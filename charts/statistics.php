<?php
// Bắt đầu phiên làm việc
session_start();
// Nạp các tệp cần thiết
require_once '../utils.php'; // Chứa các hàm tiện ích, hằng số và kiểm tra quyền
require_once '../studentController.php'; // Lớp xử lý logic cho sinh viên
require_once '../scoreController.php'; // Lớp xử lý logic cho điểm

// Yêu cầu quyền xem thống kê, nếu không có sẽ dừng
requirePermission(PERMISSION_VIEW_STATISTICS);

// Khởi tạo các đối tượng controller
$studentController = new StudentController();
$scoreController = new ScoreController();

// Lấy dữ liệu thống kê từ controller
$studentStats = $studentController->getStatistics();
$scoreStats = $scoreController->getScoreStatistics();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Hệ thống quản lý sinh viên</title>
    <!-- Nạp CSS từ CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS nội bộ để tùy chỉnh giao diện -->
    <style>
        .sidebar {
            min-height: 100vh; /* Chiều cao tối thiểu bằng chiều cao màn hình */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Nền gradient */
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8); /* Màu chữ cho link */
            padding: 12px 20px;
            border-radius: 8px; /* Bo góc */
            margin: 2px 0;
            transition: all 0.3s; /* Hiệu ứng chuyển động */
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white; /* Màu chữ khi hover hoặc active */
            background: rgba(255,255,255,0.2); /* Nền khi hover hoặc active */
            transform: translateX(5px); /* Dịch chuyển sang phải một chút */
        }
        .main-content {
            background-color: #f8f9fa; /* Màu nền cho nội dung chính */
            min-height: 100vh;
        }
        .card {
            border: none; /* Bỏ viền card */
            border-radius: 15px; /* Bo góc card */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Đổ bóng */
            transition: transform 0.3s; /* Hiệu ứng chuyển động */
        }
        .card:hover {
            transform: translateY(-5px); /* Nâng card lên khi hover */
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Nền gradient cho card thống kê */
            color: white; /* Chữ màu trắng */
        }
        .stat-card .card-body {
            padding: 2rem; /* Tăng đệm cho card thống kê */
        }
        .stat-number {
            font-size: 2.5rem; /* Cỡ chữ lớn cho số liệu */
            font-weight: bold;
        }
        .chart-container {
            position: relative;
            height: 400px; /* Chiều cao cho container chứa biểu đồ */
        }
        .chart-small {
            height: 300px; /* Chiều cao nhỏ hơn cho các biểu đồ phụ */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Thanh điều hướng bên trái) -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Student Management
                    </h4>
                    <!-- Hiển thị thông tin người dùng đang đăng nhập -->
                    <div class="text-white-50 mb-3">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>
                
                <!-- Menu điều hướng -->
                <nav class="nav flex-column px-3">
                    <a class="nav-link" href="../public/index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>Quản lý sinh viên
                    </a>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>Quản lý điểm
                    </a>
                    <a class="nav-link active" href="statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    
                    <?php // Chỉ Super Admin mới có quyền quản lý người dùng. ?>
                    <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="../public/users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="../public/profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content (Nội dung chính) -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-chart-bar me-2"></i>Thống kê tổng quan</h2>
                        <div class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y H:i'); // Hiển thị ngày giờ hiện tại ?>
                        </div>
                    </div>
                    
                    <!-- Các card thống kê nhanh -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <div class="stat-number"><?php echo $studentStats['total_students']; ?></div>
                                    <div>Tổng sinh viên</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-male fa-2x mb-3"></i>
                                    <div class="stat-number">
                                        <?php 
                                        $maleCount = 0;
                                        // Lấy số lượng sinh viên nam từ mảng thống kê
                                        foreach ($studentStats['by_gender'] as $gender) {
                                            if ($gender['gender'] == 'male') {
                                                $maleCount = $gender['count'];
                                                break;
                                            }
                                        }
                                        echo $maleCount;
                                        ?>
                                    </div>
                                    <div>Sinh viên nam</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-female fa-2x mb-3"></i>
                                    <div class="stat-number">
                                        <?php 
                                        $femaleCount = 0;
                                        // Lấy số lượng sinh viên nữ
                                        foreach ($studentStats['by_gender'] as $gender) {
                                            if ($gender['gender'] == 'female') {
                                                $femaleCount = $gender['count'];
                                                break;
                                            }
                                        }
                                        echo $femaleCount;
                                        ?>
                                    </div>
                                    <div>Sinh viên nữ</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-3"></i>
                                    <div class="stat-number">
                                        <?php 
                                        // Tính điểm trung bình chung
                                        $totalScore = 0;
                                        $scoreCount = 0;
                                        foreach ($scoreStats['by_subject'] as $subject) {
                                            $totalScore += $subject['avg_score'] * $subject['count'];
                                            $scoreCount += $subject['count'];
                                        }
                                        // Hiển thị điểm TB, nếu không có điểm nào thì hiển thị 0.0
                                        echo $scoreCount > 0 ? number_format($totalScore / $scoreCount, 1) : '0.0';
                                        ?>
                                    </div>
                                    <div>Điểm TB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hàng chứa các biểu đồ -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar me-2"></i>Phân bố giới tính</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container chart-small">
                                        <canvas id="genderChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-calendar-alt me-2"></i>Điểm TB theo học kỳ</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container chart-small">
                                        <canvas id="semesterChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hàng chứa các biểu đồ (tiếp) -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar me-2"></i>Điểm trung bình theo môn</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container chart-small">
                                        <canvas id="subjectChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-doughnut me-2"></i>Phân bố xếp loại</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container chart-small">
                                        <canvas id="gradeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hàng chứa các bảng dữ liệu -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-table me-2"></i>Top môn học</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Môn học</th>
                                                    <th>Điểm TB</th>
                                                    <th>Số SV</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($scoreStats['by_subject'], 0, 5) as $subject): // Lấy 5 môn đầu tiên ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($subject['subject']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo number_format($subject['avg_score'], 1); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $subject['count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-table me-2"></i>Thống kê theo học kỳ</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Học kỳ</th>
                                                    <th>Điểm TB</th>
                                                    <th>Số điểm</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($scoreStats['by_semester'] as $semester): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($semester['semester']); ?></td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo number_format($semester['avg_score'], 1); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $semester['count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nạp các thư viện JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- Vẽ các biểu đồ bằng Chart.js ---

        // Biểu đồ Phân bố giới tính (Bar Chart - Cột)
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderData = <?php echo json_encode($studentStats['by_gender']); ?>; // Lấy dữ liệu từ PHP
        const genderLabels = [];
        const genderValues = [];
        const genderColors = ['#36A2EB', '#FF6384', '#FFCE56']; // Màu cho Nam, Nữ, Khác
        
        genderData.forEach((item, index) => {
            genderLabels.push(item.gender === 'male' ? 'Nam' : (item.gender === 'female' ? 'Nữ' : 'Khác'));
            genderValues.push(item.count);
        });
        
        new Chart(genderCtx, {
            type: 'bar', // Loại biểu đồ cột
            data: {
                labels: genderLabels, // Nhãn (Nam, Nữ, Khác)
                datasets: [{
                    label: 'Số lượng',
                    data: genderValues, // Dữ liệu số lượng
                    backgroundColor: genderColors.slice(0, genderLabels.length),
                    borderColor: genderColors.slice(0, genderLabels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, // Tự động co giãn
                maintainAspectRatio: false, // Không giữ tỷ lệ khung hình
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
        
        // Biểu đồ Điểm trung bình theo học kỳ (Bar Chart)
        const semesterCtx = document.getElementById('semesterChart').getContext('2d');
        const semesterData = <?php echo json_encode($scoreStats['by_semester']); ?>; // Dữ liệu điểm theo học kỳ
        const semesterLabels = [];
        const semesterValues = [];
        
        semesterData.forEach(item => {
            semesterLabels.push(item.semester); // Nhãn là các học kỳ
            semesterValues.push(parseFloat(item.avg_score)); // Dữ liệu là điểm trung bình
        });
        
        new Chart(semesterCtx, {
            type: 'bar', // Loại biểu đồ cột
            data: {
                labels: semesterLabels,
                datasets: [{
                    label: 'Điểm trung bình',
                    data: semesterValues,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)', // Màu gradient purple
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true, // Trục Y bắt đầu từ 0
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
        
        // Biểu đồ Điểm trung bình theo môn (Bar Chart)
        const subjectCtx = document.getElementById('subjectChart').getContext('2d');
        const subjectData = <?php echo json_encode($scoreStats['by_subject']); ?>;
        const subjectLabels = [];
        const subjectValues = [];
        
        subjectData.slice(0, 5).forEach(item => { // Chỉ lấy 5 môn đầu tiên
            subjectLabels.push(item.subject);
            subjectValues.push(parseFloat(item.avg_score));
        });
        
        new Chart(subjectCtx, {
            type: 'bar', // Loại biểu đồ cột
            data: {
                labels: subjectLabels,
                datasets: [{
                    label: 'Điểm trung bình',
                    data: subjectValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
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
        
        // Biểu đồ Phân bố xếp loại (Doughnut Chart)
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        const gradeData = <?php echo json_encode($scoreStats['grade_distribution']); ?>; // Dữ liệu phân bố xếp loại
        const gradeLabels = [];
        const gradeValues = [];
        const gradeColors = ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545']; // Màu cho các loại xếp loại
        
        gradeData.forEach((item, index) => {
            gradeLabels.push(item.grade);
            gradeValues.push(item.count);
        });
        
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeLabels,
                datasets: [{
                    data: gradeValues,
                    backgroundColor: gradeColors.slice(0, gradeLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
