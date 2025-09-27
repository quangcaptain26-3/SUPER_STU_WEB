<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userRole = $_SESSION['role'];
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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
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
                    <div id="mini-clock"></div>
                </div>
                
                <nav class="nav flex-column px-3">
                    <a class="nav-link active" href="index.php">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                    
                    <?php if (canAccess(PERMISSION_VIEW_STUDENTS)): ?>
                    <a class="nav-link" href="../students/list.php">
                        <i class="fas fa-users me-2"></i>
                        <?php echo canAccess(PERMISSION_ADD_STUDENTS) ? 'Quản lý sinh viên' : 'Danh sách sinh viên'; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (canAccess(PERMISSION_VIEW_SCORES)): ?>
                    <a class="nav-link" href="../scores/list.php">
                        <i class="fas fa-chart-line me-2"></i>
                        <?php echo canAccess(PERMISSION_ADD_SCORES) ? 'Quản lý điểm' : 'Xem điểm'; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (canAccess(PERMISSION_VIEW_STATISTICS)): ?>
                    <a class="nav-link" href="../charts/statistics.php">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </a>
                    <?php endif; ?>
                    
                    
                    <?php if (canAccess(PERMISSION_MANAGE_USERS)): ?>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-user-cog me-2"></i>Quản lý người dùng
                    </a>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </a>
                    
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                        <div class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <span class="realtime-datetime"><?php echo date('d/m/Y H:i'); ?></span>
                            <span class="live-indicator"></span>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
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
                    
                    <!-- Charts -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-pie me-2"></i>Phân bố giới tính</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="genderChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-line me-2"></i>Xu hướng đăng ký</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="trendChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/realtime.js"></script>
    <script src="../assets/js/clock-widget.js"></script>
    <script>
        // Khởi tạo đồng hồ mini trong sidebar
        new MiniClockWidget('mini-clock');
        
        // Load statistics
        fetch('../charts/api/statistics.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalStudents').textContent = data.total_students || 0;
                document.getElementById('maleStudents').textContent = data.male_students || 0;
                document.getElementById('femaleStudents').textContent = data.female_students || 0;
                document.getElementById('avgScore').textContent = data.avg_score || '0.0';
                
                // Gender Chart
                const genderCtx = document.getElementById('genderChart').getContext('2d');
                new Chart(genderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Nam', 'Nữ', 'Khác'],
                        datasets: [{
                            data: [data.male_students || 0, data.female_students || 0, data.other_students || 0],
                            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
                
                // Trend Chart
                const trendCtx = document.getElementById('trendChart').getContext('2d');
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: data.monthly_labels || [],
                        datasets: [{
                            label: 'Sinh viên mới',
                            data: data.monthly_data || [],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    </script>
</body>
</html>
