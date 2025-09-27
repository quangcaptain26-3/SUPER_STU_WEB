<?php
require_once 'config/db.php';
require_once 'utils.php';

class StudentController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllStudents($search = '', $limit = 50, $offset = 0) {
        $query = "SELECT * FROM students";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStudentById($id) {
        $query = "SELECT * FROM students WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function addStudent($data) {
        // Check if MSV exists
        $query = "SELECT id FROM students WHERE msv = :msv";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên đã tồn tại'];
        }
        
        $query = "INSERT INTO students (msv, fullname, dob, gender, address, phone, email, avatar) 
                  VALUES (:msv, :fullname, :dob, :gender, :address, :phone, :email, :avatar)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Thêm sinh viên thành công', 'id' => $this->conn->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Lỗi thêm sinh viên'];
        }
    }
    
    public function updateStudent($id, $data) {
        // Check if MSV exists for other students
        $query = "SELECT id FROM students WHERE msv = :msv AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên đã tồn tại'];
        }
        
        $query = "UPDATE students SET msv = :msv, fullname = :fullname, dob = :dob, 
                  gender = :gender, address = :address, phone = :phone, email = :email, 
                  avatar = :avatar WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':msv', $data['msv']);
        $stmt->bindParam(':fullname', $data['fullname']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':avatar', $data['avatar']);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cập nhật sinh viên thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi cập nhật sinh viên'];
        }
    }
    
    public function deleteStudent($id) {
        // Get student info to delete avatar
        $student = $this->getStudentById($id);
        if ($student && $student['avatar']) {
            deleteFile('uploads/avatars/' . $student['avatar']);
        }
        
        $query = "DELETE FROM students WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Xóa sinh viên thành công'];
        } else {
            return ['success' => false, 'message' => 'Lỗi xóa sinh viên'];
        }
    }
    
    public function getTotalStudents($search = '') {
        $query = "SELECT COUNT(*) as total FROM students";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE fullname LIKE :search OR msv LIKE :search OR email LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getStatistics() {
        $stats = [];
        
        // Total students
        $query = "SELECT COUNT(*) as total FROM students";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Students by gender
        $query = "SELECT gender, COUNT(*) as count FROM students GROUP BY gender";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_gender'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Students by month (last 12 months)
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                  FROM students 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY month 
                  ORDER BY month";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
?>
