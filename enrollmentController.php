<?php
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class EnrollmentController
 * Chịu trách nhiệm xử lý tất cả các nghiệp vụ logic liên quan đến đăng ký môn học.
 */
class EnrollmentController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy danh sách đăng ký môn học
     * @param int|null $studentId Lọc theo sinh viên
     * @param int|null $subjectId Lọc theo môn học
     * @param string|null $semester Lọc theo học kỳ
     * @return array Mảng chứa danh sách đăng ký
     */
    public function getAllEnrollments($studentId = null, $subjectId = null, $semester = null)
    {
        $query = "SELECT e.*, 
                         st.msv, st.fullname as student_name,
                         s.code as subject_code, s.name as subject_name, s.credits
                  FROM enrollments e
                  LEFT JOIN students st ON e.student_id = st.id
                  LEFT JOIN subjects s ON e.subject_id = s.id";
        
        $params = [];
        $conditions = [];
        
        if ($studentId !== null) {
            $conditions[] = "e.student_id = :student_id";
            $params[':student_id'] = $studentId;
        }
        
        if ($subjectId !== null) {
            $conditions[] = "e.subject_id = :subject_id";
            $params[':subject_id'] = $subjectId;
        }
        
        if ($semester !== null && $semester !== '') {
            $conditions[] = "e.semester = :semester";
            $params[':semester'] = $semester;
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY e.enrolled_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách môn học đã đăng ký của một sinh viên trong học kỳ
     * @param int $studentId ID sinh viên
     * @param string $semester Học kỳ
     * @return array Mảng các môn học đã đăng ký
     */
    public function getEnrolledSubjects($studentId, $semester)
    {
        $query = "SELECT s.* 
                  FROM subjects s
                  INNER JOIN enrollments e ON s.id = e.subject_id
                  WHERE e.student_id = :student_id AND e.semester = :semester AND e.status = 'enrolled'
                  ORDER BY s.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xem sinh viên đã đăng ký môn học chưa
     * @param int $studentId ID sinh viên
     * @param int $subjectId ID môn học
     * @param string $semester Học kỳ
     * @return bool
     */
    public function isEnrolled($studentId, $subjectId, $semester)
    {
        $query = "SELECT id FROM enrollments 
                  WHERE student_id = :student_id AND subject_id = :subject_id AND semester = :semester";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':subject_id', $subjectId);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Đăng ký môn học cho sinh viên
     * @param array $data Dữ liệu đăng ký
     * @return array Mảng kết quả
     */
    public function addEnrollment($data)
    {
        // Kiểm tra đã đăng ký chưa
        if ($this->isEnrolled($data['student_id'], $data['subject_id'], $data['semester'])) {
            return ['success' => false, 'message' => 'Sinh viên đã đăng ký môn học này trong học kỳ này rồi.'];
        }
        
        $query = "INSERT INTO enrollments (student_id, subject_id, semester, status) 
                  VALUES (:student_id, :subject_id, :semester, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject_id', $data['subject_id']);
        $stmt->bindParam(':semester', $data['semester']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Đăng ký môn học thành công.', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi đăng ký môn học.'];
        }
    }

    /**
     * Xóa đăng ký môn học
     * @param int $id ID của đăng ký
     * @return array Mảng kết quả
     */
    public function deleteEnrollment($id)
    {
        // Kiểm tra xem đã có điểm chưa
        $query = "SELECT e.student_id, e.subject_id, e.semester 
                  FROM enrollments e
                  WHERE e.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($enrollment) {
            $checkScore = "SELECT id FROM scores 
                          WHERE student_id = :student_id AND subject_id = :subject_id AND semester = :semester";
            $checkStmt = $this->conn->prepare($checkScore);
            $checkStmt->bindParam(':student_id', $enrollment['student_id']);
            $checkStmt->bindParam(':subject_id', $enrollment['subject_id']);
            $checkStmt->bindParam(':semester', $enrollment['semester']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Không thể xóa đăng ký vì đã có điểm số.'];
            }
        }
        
        $query = "DELETE FROM enrollments WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa đăng ký thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa đăng ký.'];
        }
    }
}

