<?php
session_start();
require_once '../utils.php';
require_once '../studentController.php';
require_once '../scoreController.php';

requirePermission(PERMISSION_VIEW_STATISTICS);

$studentController = new StudentController();
$scoreController = new ScoreController();

$studentStats = $studentController->getStatistics();
$scoreStats = $scoreController->getScoreStatistics();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Hệ thống quản lý sinh viên</title>
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
        .chart-container {
            position: relative;
            height: 400px;
        }
        .chart-small {
            height: 300px;
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
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                </div>
                
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
                    <a class="nav-link" href="../public/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-chart-bar me-2"></i>Thống kê tổng quan</h2>
                        <div class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y H:i'); ?>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
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
                                        $totalScore = 0;
                                        $scoreCount = 0;
                                        foreach ($scoreStats['by_subject'] as $subject) {
                                            $totalScore += $subject['avg_score'] * $subject['count'];
                                            $scoreCount += $subject['count'];
                                        }
                                        echo $scoreCount > 0 ? number_format($totalScore / $scoreCount, 1) : '0.0';
                                        ?>
                                    </div>
                                    <div>Điểm TB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row 1 -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-pie me-2"></i>Phân bố giới tính</h5>
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
                                    <h5><i class="fas fa-chart-line me-2"></i>Xu hướng đăng ký</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container chart-small">
                                        <canvas id="trendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row 2 -->
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
                    
                    <!-- Data Tables -->
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
                                                <?php foreach (array_slice($scoreStats['by_subject'], 0, 5) as $subject): ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderData = <?php echo json_encode($studentStats['by_gender']); ?>;
        const genderLabels = [];
        const genderValues = [];
        const genderColors = ['#36A2EB', '#FF6384', '#FFCE56'];
        
        genderData.forEach((item, index) => {
            genderLabels.push(item.gender === 'male' ? 'Nam' : (item.gender === 'female' ? 'Nữ' : 'Khác'));
            genderValues.push(item.count);
        });
        
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: genderLabels,
                datasets: [{
                    data: genderValues,
                    backgroundColor: genderColors.slice(0, genderLabels.length),
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
        
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendData = <?php echo json_encode($studentStats['by_month']); ?>;
        const trendLabels = [];
        const trendValues = [];
        
        trendData.forEach(item => {
            trendLabels.push(item.month);
            trendValues.push(item.count);
        });
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Sinh viên mới',
                    data: trendValues,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Subject Chart
        const subjectCtx = document.getElementById('subjectChart').getContext('2d');
        const subjectData = <?php echo json_encode($scoreStats['by_subject']); ?>;
        const subjectLabels = [];
        const subjectValues = [];
        
        subjectData.slice(0, 5).forEach(item => {
            subjectLabels.push(item.subject);
            subjectValues.push(parseFloat(item.avg_score));
        });
        
        new Chart(subjectCtx, {
            type: 'bar',
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
                        max: 10
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Grade Chart
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        const gradeData = <?php echo json_encode($scoreStats['grade_distribution']); ?>;
        const gradeLabels = [];
        const gradeValues = [];
        const gradeColors = ['#28a745', '#20c997', '#ffc107', '#fd7e14', '#dc3545'];
        
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
