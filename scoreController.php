<?php
// Nạp các file cần thiết.
require_once 'config/db.php'; // Chứa lớp `Database` để kết nối CSDL.
require_once 'utils.php';     // Chứa các hàm tiện ích.

/**
 * Class ScoreController
 * Chịu trách nhiệm xử lý tất cả các nghiệp vụ logic liên quan đến điểm số của sinh viên.
 */
class ScoreController
{
    // Thuộc tính private để lưu trữ đối tượng kết nối CSDL (PDO).
    private $conn;

    /**
     * Hàm khởi tạo của lớp ScoreController.
     */
    public function __construct()
    {
        // Khởi tạo đối tượng Database.
        $database = new Database();
        // Lấy kết nối PDO và gán vào thuộc tính của controller.
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy danh sách điểm, có hỗ trợ lọc theo sinh viên và học kỳ.
     * @param int|null $studentId ID của sinh viên cần lọc (nếu có).
     * @param string|null $semester Chuỗi học kỳ cần lọc (ví dụ: '2025-1').
     * @return array Mảng chứa danh sách các bản ghi điểm.
     */
    public function getAllScores($studentId = null, $semester = null)
    {
        // Câu lệnh SQL cơ bản, sử dụng LEFT JOIN để lấy thêm thông tin `fullname` và `msv` từ bảng `students`.
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id";
        
        // Mảng lưu các tham số và mảng lưu các điều kiện WHERE.
        $params = [];
        $conditions = [];

        // Nếu có cung cấp `studentId` để lọc.
        if ($studentId) {
            // Thêm điều kiện vào mảng `conditions`.
            $conditions[] = "s.student_id = :student_id";
            // Thêm giá trị vào mảng tham số.
            $params[':student_id'] = $studentId;
        }
        // Nếu có cung cấp `semester` để lọc.
        if ($semester) {
            $conditions[] = "s.semester = :semester";
            $params[':semester'] = $semester;
        }

        // Nếu mảng `conditions` không rỗng, tức là có ít nhất một bộ lọc được áp dụng.
        if (!empty($conditions)) {
            // Nối các điều kiện lại với nhau bằng " AND " và thêm vào câu lệnh SQL.
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Sắp xếp kết quả: học kỳ mới nhất lên đầu, sau đó sắp xếp theo tên sinh viên.
        $query .= " ORDER BY s.semester DESC, st.fullname";

        // Chuẩn bị và thực thi câu lệnh.
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        // Trả về tất cả các dòng kết quả.
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin chi tiết của một bản ghi điểm dựa vào ID.
     * @param int $id ID của bản ghi điểm.
     * @return array|false Mảng thông tin điểm hoặc `false` nếu không tìm thấy.
     */
    public function getScoreById($id)
    {
        // Tương tự `getAllScores`, câu lệnh này cũng JOIN với bảng students.
        $query = "SELECT s.*, st.fullname, st.msv 
                  FROM scores s 
                  LEFT JOIN students st ON s.student_id = st.id 
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Trả về một bản ghi duy nhất.
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm một bản ghi điểm mới vào CSDL.
     * @param array $data Dữ liệu điểm từ form.
     * @return array Mảng kết quả.
     */
    public function addScore($data)
    {
        // Câu lệnh INSERT đơn giản.
        $query = "INSERT INTO scores (student_id, subject, score, semester) 
                  VALUES (:student_id, :subject, :score, :semester)";

        $stmt = $this->conn->prepare($query);
        
        // Gắn các giá trị vào tham số.
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);

        if ($stmt->execute()) {
            // Trả về ID của bản ghi điểm vừa được thêm vào.
            return ['success' => true, 'message' => 'Thêm điểm thành công.', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi thêm điểm.'];
        }
    }

    /**
     * Cập nhật một bản ghi điểm đã có.
     * @param int $id ID của bản ghi điểm cần cập nhật.
     * @param array $data Dữ liệu mới.
     * @return array Mảng kết quả.
     */
    public function updateScore($id, $data)
    {
        // Câu lệnh UPDATE, xác định bản ghi cần cập nhật qua `WHERE id = :id`.
        $query = "UPDATE scores SET student_id = :student_id, subject = :subject, 
                  score = :score, semester = :semester WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Gắn các giá trị mới.
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':semester', $data['semester']);
        $stmt->bindParam(':id', $id); // Gắn ID của bản ghi cần cập nhật.

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật điểm thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật điểm.'];
        }
    }

    /**
     * Xóa một bản ghi điểm khỏi CSDL.
     * @param int $id ID của bản ghi điểm cần xóa.
     * @return array Mảng kết quả.
     */
    public function deleteScore($id)
    {
        // Câu lệnh DELETE đơn giản.
        $query = "DELETE FROM scores WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa điểm thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa điểm.'];
        }
    }

    /**
     * Lấy các dữ liệu thống kê liên quan đến điểm số.
     * @return array Mảng chứa các thông tin thống kê.
     */
    public function getScoreStatistics()
    {
        $stats = [];

        // 1. Thống kê điểm trung bình và số lượng bài thi theo từng môn học.
        // Tương đương với VIEW subject_statistics đã bị xóa
        $query1 = "SELECT subject, 
                          COUNT(*) as count, 
                          COUNT(*) as student_count, 
                          AVG(score) as avg_score, 
                          MAX(score) as highest_score, 
                          MIN(score) as lowest_score 
                   FROM scores 
                   GROUP BY subject 
                   ORDER BY avg_score DESC";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->execute();
        $stats['by_subject'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        // 2. Thống kê điểm trung bình và số lượng bài thi theo từng học kỳ.
        // Tương đương với VIEW semester_statistics đã bị xóa
        $query2 = "SELECT semester, 
                          COUNT(*) as count, 
                          COUNT(*) as score_count, 
                          AVG(score) as avg_score, 
                          COUNT(DISTINCT student_id) as student_count 
                   FROM scores 
                   GROUP BY semester 
                   ORDER BY semester DESC";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute();
        $stats['by_semester'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 3. Thống kê phân bố điểm theo xếp loại (A+, A, B+, ...).
        // `CASE` là một cấu trúc điều kiện trong SQL, hoạt động như switch...case hoặc if...else if.
        $query3 = "SELECT 
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
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->execute();
        $stats['grade_distribution'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        // Trả về mảng lớn chứa tất cả các kết quả thống kê.
        return $stats;
    }

    /**
     * Lấy tất cả các điểm của một sinh viên cụ thể.
     * @param int $studentId ID của sinh viên.
     * @return array Mảng chứa các bản ghi điểm của sinh viên đó.
     */
    public function getStudentScores($studentId)
    {
        $query = "SELECT * FROM scores WHERE student_id = :student_id ORDER BY semester DESC, subject";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính điểm trung bình chung của một sinh viên cụ thể.
     * @param int $studentId ID của sinh viên.
     * @return float Điểm trung bình đã được làm tròn.
     */
    public function getStudentAverageScore($studentId)
    {
        // Sử dụng hàm tổng hợp `AVG()` của SQL để tính trung bình.
        $query = "SELECT AVG(score) as avg_score FROM scores WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // `round()`: Làm tròn kết quả đến 2 chữ số thập phân.
        return round($result['avg_score'] ?? 0, 2);
    }

    /**
     * Lấy thống kê tổng hợp điểm số của một sinh viên.
     * Tương đương với VIEW student_scores_summary đã bị xóa.
     * @param int $studentId ID của sinh viên.
     * @return array|false Mảng chứa thống kê hoặc false nếu không tìm thấy.
     */
    public function getStudentScoresSummary($studentId)
    {
        $query = "SELECT s.id, s.msv, s.fullname, 
                         COUNT(sc.id) as total_scores, 
                         AVG(sc.score) as average_score, 
                         MAX(sc.score) as highest_score, 
                         MIN(sc.score) as lowest_score 
                  FROM students s 
                  LEFT JOIN scores sc ON s.id = sc.student_id 
                  WHERE s.id = :student_id 
                  GROUP BY s.id, s.msv, s.fullname";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
