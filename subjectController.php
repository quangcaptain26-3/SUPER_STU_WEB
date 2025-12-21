<?php
require_once 'config/db.php';
require_once 'utils.php';

/**
 * Class SubjectController
 * Chịu trách nhiệm xử lý tất cả các nghiệp vụ logic liên quan đến môn học.
 */
class SubjectController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lấy danh sách tất cả môn học
     * @param string $status Lọc theo trạng thái (active/inactive), null = tất cả
     * @return array Mảng chứa danh sách các môn học
     */
    public function getAllSubjects($status = 'active')
    {
        $query = "SELECT * FROM subjects";
        $params = [];
        
        if ($status !== null) {
            $query .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một môn học theo ID
     * @param int $id ID của môn học
     * @return array|false Mảng thông tin môn học hoặc false nếu không tìm thấy
     */
    public function getSubjectById($id)
    {
        $query = "SELECT * FROM subjects WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm môn học mới
     * @param array $data Dữ liệu môn học từ form
     * @return array Mảng kết quả
     */
    public function addSubject($data)
    {
        // Kiểm tra mã môn học đã tồn tại chưa (nếu có)
        if (!empty($data['code'])) {
            $checkQuery = "SELECT id FROM subjects WHERE code = :code";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':code', $data['code']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Mã môn học này đã tồn tại.'];
            }
        }
        
        $query = "INSERT INTO subjects (code, name, credits, status) 
                  VALUES (:code, :name, :credits, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':credits', $data['credits']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm môn học thành công.', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi thêm môn học.'];
        }
    }

    /**
     * Cập nhật thông tin môn học
     * @param int $id ID của môn học
     * @param array $data Dữ liệu mới
     * @return array Mảng kết quả
     */
    public function updateSubject($id, $data)
    {
        // Kiểm tra mã môn học đã tồn tại chưa (nếu có, và không phải chính nó)
        if (!empty($data['code'])) {
            $checkQuery = "SELECT id FROM subjects WHERE code = :code AND id != :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':code', $data['code']);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Mã môn học này đã tồn tại.'];
            }
        }
        
        $query = "UPDATE subjects 
                  SET code = :code, name = :name, credits = :credits, status = :status 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':credits', $data['credits']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật môn học thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi cập nhật môn học.'];
        }
    }

    /**
     * Xóa môn học
     * @param int $id ID của môn học
     * @return array Mảng kết quả
     */
    public function deleteSubject($id)
    {
        // Kiểm tra xem môn học có đang được sử dụng không
        $checkQuery = "SELECT COUNT(*) as count FROM scores WHERE subject_id = :id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Không thể xóa môn học này vì đã có điểm số liên quan.'];
        }
        
        $query = "DELETE FROM subjects WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa môn học thành công.'];
        } else {
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa môn học.'];
        }
    }
}

