-- Hệ thống quản lý sinh viên
-- Database: student_management

-- Tạo database
CREATE DATABASE IF NOT EXISTS student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_management;

-- Bảng users (quản lý tài khoản)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL, -- lưu SHA256("password")
    email VARCHAR(100),
    role ENUM('superadmin','admin','teacher','student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng students (thông tin sinh viên)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    msv VARCHAR(20) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    dob DATE,
    gender ENUM('male','female','other'),
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(100),
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng scores (điểm số)
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(100),
    score FLOAT,
    semester VARCHAR(20),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Bảng reset_tokens (quên mật khẩu)
CREATE TABLE reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    token VARCHAR(64),
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Thêm dữ liệu mẫu
-- Tài khoản mặc định
INSERT INTO users (username, password, email, role) VALUES 
('admin', SHA2('admin123', 256), 'admin@example.com', 'superadmin'),
('admin2', SHA2('admin123', 256), 'admin2@example.com', 'admin'),
('teacher1', SHA2('teacher123', 256), 'teacher1@example.com', 'teacher'),
('teacher2', SHA2('teacher123', 256), 'teacher2@example.com', 'teacher'),
('student1', SHA2('student123', 256), 'student1@example.com', 'student'),
('student2', SHA2('student123', 256), 'student2@example.com', 'student');

-- Dữ liệu sinh viên mẫu
INSERT INTO students (msv, fullname, dob, gender, address, phone, email) VALUES 
('SV001', 'Nguyễn Văn An', '2000-01-15', 'male', 'Hà Nội', '0123456789', 'an.nguyen@example.com'),
('SV002', 'Trần Thị Bình', '2000-03-20', 'female', 'TP.HCM', '0987654321', 'binh.tran@example.com'),
('SV003', 'Lê Văn Cường', '1999-12-10', 'male', 'Đà Nẵng', '0369258147', 'cuong.le@example.com'),
('SV004', 'Phạm Thị Dung', '2000-07-05', 'female', 'Cần Thơ', '0741852963', 'dung.pham@example.com'),
('SV005', 'Hoàng Văn Em', '2000-09-18', 'male', 'Hải Phòng', '0852741963', 'em.hoang@example.com');

-- Dữ liệu điểm mẫu
INSERT INTO scores (student_id, subject, score, semester) VALUES 
(1, 'Toán cao cấp', 8.5, 'HK1-2024'),
(1, 'Lập trình C', 9.0, 'HK1-2024'),
(1, 'Cơ sở dữ liệu', 8.0, 'HK1-2024'),
(2, 'Toán cao cấp', 7.5, 'HK1-2024'),
(2, 'Lập trình C', 8.5, 'HK1-2024'),
(2, 'Cơ sở dữ liệu', 9.5, 'HK1-2024'),
(3, 'Toán cao cấp', 6.5, 'HK1-2024'),
(3, 'Lập trình C', 7.0, 'HK1-2024'),
(3, 'Cơ sở dữ liệu', 8.0, 'HK1-2024'),
(4, 'Toán cao cấp', 9.5, 'HK1-2024'),
(4, 'Lập trình C', 9.0, 'HK1-2024'),
(4, 'Cơ sở dữ liệu', 8.5, 'HK1-2024'),
(5, 'Toán cao cấp', 5.5, 'HK1-2024'),
(5, 'Lập trình C', 6.0, 'HK1-2024'),
(5, 'Cơ sở dữ liệu', 7.5, 'HK1-2024');

-- Tạo indexes để tối ưu hiệu suất
CREATE INDEX idx_students_msv ON students(msv);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_scores_student_id ON scores(student_id);
CREATE INDEX idx_scores_semester ON scores(semester);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_reset_tokens_token ON reset_tokens(token);
CREATE INDEX idx_reset_tokens_expires ON reset_tokens(expires_at);

-- Tạo view để thống kê
CREATE VIEW student_scores_summary AS
SELECT 
    s.id,
    s.msv,
    s.fullname,
    COUNT(sc.id) as total_scores,
    AVG(sc.score) as average_score,
    MAX(sc.score) as highest_score,
    MIN(sc.score) as lowest_score
FROM students s
LEFT JOIN scores sc ON s.id = sc.student_id
GROUP BY s.id, s.msv, s.fullname;

-- Tạo view để thống kê theo môn học
CREATE VIEW subject_statistics AS
SELECT 
    subject,
    COUNT(*) as student_count,
    AVG(score) as average_score,
    MAX(score) as highest_score,
    MIN(score) as lowest_score
FROM scores
GROUP BY subject;

-- Tạo view để thống kê theo học kỳ
CREATE VIEW semester_statistics AS
SELECT 
    semester,
    COUNT(*) as score_count,
    AVG(score) as average_score,
    COUNT(DISTINCT student_id) as student_count
FROM scores
GROUP BY semester;
