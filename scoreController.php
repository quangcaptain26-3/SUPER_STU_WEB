<?php
require_once 'config/db.php';
require_once 'utils.php';

class ScoreController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllScores($studentId = null, $semester = null) {
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id";
        $params = [];
        
        $conditions = [];
        if ($studentId) {
            $conditions[] = "s.student_id = :student_id";
            $params[':student_id'] = $studentId;
        }
        if ($semester) {
            $conditions[] = "s.semester = :semester";
            $params[':semester'] = $semester;
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY s.semester DESC, st.fullname";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getScoreById($id) {
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id 
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addScore($data) {
        $query = "INSERT INTO scores (student_id, subject, score, semester) 
                  VALUES (:student_id, :subject, :score, :semester)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm điểm thành công', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Lỗi thêm điểm'];
        }
    }
    
    public function updateScore($id, $data) {
        $query = "UPDATE scores SET student_id = :student_id, subject = :subject, 
                  score = :score, semester = :semester WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật điểm thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi cập nhật điểm'];
        }
    }
    
    public function deleteScore($id) {
        $query = "DELETE FROM scores WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa điểm thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi xóa điểm'];
        }
    }
    
    public function getScoreStatistics() {
        $stats = [];
        
        // Average score by subject
        $query = "SELECT subject, AVG(score) as avg_score, COUNT(*) as count 
                  FROM scores 
                  GROUP BY subject 
                  ORDER BY avg_score DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_subject'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Average score by semester
        $query = "SELECT semester, AVG(score) as avg_score, COUNT(*) as count 
                  FROM scores 
                  GROUP BY semester 
                  ORDER BY semester DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_semester'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Score distribution
        $query = "SELECT 
                    CASE 
                        WHEN score >= 9 THEN 'A+'
                        WHEN score >= 8 THEN 'A'
                        WHEN score >= 7 THEN 'B+'
                        WHEN score >= 6 THEN 'B'
                        WHEN score >= 5 THEN 'C'
                        ELSE 'D'
                    END as grade,
                    COUNT(*) as count
                  FROM scores 
                  GROUP BY grade 
                  ORDER BY grade";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['grade_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function getStudentScores($studentId) {
        $query = "SELECT * FROM scores WHERE student_id = :student_id ORDER BY semester DESC, subject";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentAverageScore($studentId) {
        $query = "SELECT AVG(score) as avg_score FROM scores WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round($result['avg_score'], 2);
    }
}
?>
