-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 14, 2025 lúc 05:22 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `student_management`
--
CREATE DATABASE IF NOT EXISTS `student_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `student_management`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reset_tokens`
--

DROP TABLE IF EXISTS `reset_tokens`;
CREATE TABLE IF NOT EXISTS `reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_reset_tokens_token` (`token`),
  KEY `idx_reset_tokens_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reset_tokens`
--

INSERT INTO `reset_tokens` (`id`, `user_id`, `token`, `expires_at`) VALUES
(2, 13, '33246c388a3ef306fc8fb47a051b657513e165647aecad058166d6b52bda7e07', '2025-12-08 18:47:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `scores`
--

DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `score` float DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_scores_student_id` (`student_id`),
  KEY `idx_scores_semester` (`semester`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `scores`
--

INSERT INTO `scores` (`id`, `student_id`, `subject`, `score`, `semester`) VALUES
(1, 1, 'Toán cao cấp', 8.5, 'HK1-2024'),
(2, 1, 'Lập trình C', 5, 'HK1-2024'),
(3, 1, 'Cơ sở dữ liệu', 8, 'HK1-2024'),
(4, 2, 'Toán cao cấp', 7.5, 'HK1-2024'),
(5, 2, 'Lập trình C', 6.8, 'HK1-2024'),
(6, 2, 'Cơ sở dữ liệu', 9.5, 'HK1-2024'),
(7, 3, 'Toán cao cấp', 6.5, 'HK1-2024'),
(8, 3, 'Lập trình C', 7, 'HK1-2024'),
(9, 3, 'Cơ sở dữ liệu', 8, 'HK1-2024'),
(10, 4, 'Toán cao cấp', 9.5, 'HK1-2024'),
(11, 4, 'Lập trình C', 9, 'HK1-2024'),
(12, 4, 'Cơ sở dữ liệu', 8.5, 'HK1-2024'),
(13, 5, 'Toán cao cấp', 5.5, 'HK1-2024'),
(14, 5, 'Lập trình C', 6, 'HK1-2024'),
(15, 5, 'Cơ sở dữ liệu', 9.2, 'HK1-2024'),
(17, 3, 'Phát triển ứng dụng', 7.3, 'HK2-2024');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msv` varchar(20) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `msv` (`msv`),
  KEY `idx_students_msv` (`msv`),
  KEY `idx_students_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`id`, `msv`, `fullname`, `dob`, `gender`, `address`, `phone`, `email`, `avatar`, `created_at`) VALUES
(1, 'SV001', 'Nguyễn Văn An', '2000-01-15', 'male', 'Hà Nội', '0123456789', 'an.nguyen@example.com', NULL, '2025-09-26 17:09:40'),
(2, 'SV002', 'Trần Thị Bình', '2000-03-20', 'female', 'TP.HCM', '0987654321', 'binh.tran@example.com', NULL, '2025-09-26 17:09:40'),
(3, 'SV003', 'Lê Văn Cường', '1999-12-10', 'male', 'Đà Nẵng', '0369258147', 'cuong.le@example.com', NULL, '2025-09-26 17:09:40'),
(4, 'SV004', 'Phạm Thị Dung', '2000-07-05', 'female', 'Cần Thơ', '0741852963', 'dung.pham@example.com', NULL, '2025-09-26 17:09:40'),
(5, 'SV005', 'Hoàng Văn Em', '2000-09-18', 'male', 'Hải Phòng', '0852741963', 'em.hoang@example.com', NULL, '2025-09-26 17:09:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin','teacher','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '682a1188c793e42525e75e5b227fe749d1b1e5ea276c73f59cc5d038d5fcf9cc', 'admin@example.com', 'superadmin', '2025-09-26 17:09:40'),
(2, 'teacher1', 'a3a5124e24d571c471362deb636cc9eac5edd2679191a533af82a6dbfbf9fcdc', 'teacher1@example.com', 'teacher', '2025-09-26 17:09:40'),
(3, 'student1', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'student1@example.com', 'student', '2025-09-26 17:09:40'),
(5, 'admin2', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin2@example.com', 'admin', '2025-09-26 17:23:02'),
(7, 'student2', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'student2@example.com', 'student', '2025-09-26 17:23:02'),
(11, 'teacher3', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'teacher3ex@gmail.com', 'teacher', '2025-09-30 16:35:24'),
(12, 'maichi01', '842006be475f18c5783c44e35fe0c64b713a2b90560c94d439a8da7cf687e6a6', 'chi123@gmail.com', 'student', '2025-11-11 02:54:01'),
(13, 'dolinh01', '2d0c6e5861d114fe7e617ae9e1b8927f9eb481eee5ef8245be442e5542c2a4dd', 'linh123@gmail.com', 'teacher', '2025-11-11 02:55:22'),
(14, 'hs1', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'hs01@gmail.com', 'student', '2025-12-12 17:03:18');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
