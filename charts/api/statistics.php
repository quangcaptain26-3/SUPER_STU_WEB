<?php
session_start();
require_once '../../utils.php';
require_once '../../studentController.php';
require_once '../../scoreController.php';

requirePermission(PERMISSION_VIEW_STATISTICS);

header('Content-Type: application/json');

$studentController = new StudentController();
$scoreController = new ScoreController();

$studentStats = $studentController->getStatistics();
$scoreStats = $scoreController->getScoreStatistics();

// Prepare data for dashboard
$data = [
    'total_students' => $studentStats['total_students'],
    'male_students' => 0,
    'female_students' => 0,
    'other_students' => 0,
    'avg_score' => 0,
    'monthly_labels' => [],
    'monthly_data' => []
];

// Process gender data
foreach ($studentStats['by_gender'] as $gender) {
    switch ($gender['gender']) {
        case 'male':
            $data['male_students'] = $gender['count'];
            break;
        case 'female':
            $data['female_students'] = $gender['count'];
            break;
        default:
            $data['other_students'] = $gender['count'];
            break;
    }
}

// Process monthly data
foreach ($studentStats['by_month'] as $month) {
    $data['monthly_labels'][] = $month['month'];
    $data['monthly_data'][] = $month['count'];
}

// Calculate average score
$totalScore = 0;
$scoreCount = 0;
foreach ($scoreStats['by_subject'] as $subject) {
    $totalScore += $subject['avg_score'] * $subject['count'];
    $scoreCount += $subject['count'];
}
$data['avg_score'] = $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : 0;

echo json_encode($data);
?>
