-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 21, 2025 lúc 06:51 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


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
-- Cấu trúc bảng cho bảng `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `status` enum('enrolled','completed','dropped') DEFAULT 'enrolled',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`,`semester`),
  KEY `idx_enrollments_student_id` (`student_id`),
  KEY `idx_enrollments_subject_id` (`subject_id`),
  KEY `idx_enrollments_semester` (`semester`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `semester`, `status`, `enrolled_at`) VALUES
(1, 1, 4, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(2, 1, 2, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(3, 1, 1, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(4, 2, 4, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(5, 2, 2, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(6, 2, 1, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(7, 3, 4, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(8, 3, 2, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(9, 3, 1, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(10, 4, 4, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(11, 4, 2, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(12, 4, 1, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(13, 5, 4, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(14, 5, 2, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(15, 5, 1, 'HK1-2024', 'completed', '2025-12-21 17:07:58'),
(16, 3, 3, 'HK2-2024', 'completed', '2025-12-21 17:07:58'),
(32, 3, 8, 'HK2-2023', 'enrolled', '2025-12-21 17:16:44');

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
  `subject_id` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `score` float DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_scores_student_id` (`student_id`),
  KEY `idx_scores_semester` (`semester`),
  KEY `idx_scores_subject_id` (`subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `scores`
--

INSERT INTO `scores` (`id`, `student_id`, `subject_id`, `subject`, `score`, `semester`) VALUES
(1, 1, 4, 'Toán cao cấp', 8.5, 'HK1-2024'),
(2, 1, 2, 'Lập trình C', 5, 'HK1-2024'),
(3, 1, 1, 'Cơ sở dữ liệu', 8, 'HK1-2024'),
(4, 2, 4, 'Toán cao cấp', 7.5, 'HK1-2024'),
(5, 2, 2, 'Lập trình C', 6.8, 'HK1-2024'),
(6, 2, 1, 'Cơ sở dữ liệu', 9.5, 'HK1-2024'),
(7, 3, 4, 'Toán cao cấp', 6.5, 'HK1-2024'),
(8, 3, 2, 'Lập trình C', 7, 'HK1-2024'),
(9, 3, 1, 'Cơ sở dữ liệu', 8, 'HK1-2024'),
(10, 4, 4, 'Toán cao cấp', 9.5, 'HK1-2024'),
(11, 4, 2, 'Lập trình C', 9, 'HK1-2024'),
(12, 4, 1, 'Cơ sở dữ liệu', 8.5, 'HK1-2024'),
(13, 5, 4, 'Toán cao cấp', 5.5, 'HK1-2024'),
(14, 5, 2, 'Lập trình C', 6, 'HK1-2024'),
(15, 5, 1, 'Cơ sở dữ liệu', 9.2, 'HK1-2024'),
(17, 3, 3, 'Phát triển ứng dụng', 7.3, 'HK2-2024'),
(18, 1, 8, NULL, 3, 'HK1-2023');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `semester_statistics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `semester_statistics`;
CREATE TABLE IF NOT EXISTS `semester_statistics` (
`semester` varchar(20)
,`score_count` bigint(21)
,`average_score` double
,`student_count` bigint(21)
);

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
-- Cấu trúc đóng vai cho view `student_scores_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `student_scores_summary`;
CREATE TABLE IF NOT EXISTS `student_scores_summary` (
`id` int(11)
,`msv` varchar(20)
,`fullname` varchar(100)
,`total_scores` bigint(21)
,`average_score` double
,`highest_score` float
,`lowest_score` float
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) DEFAULT NULL COMMENT 'Mã môn học (ví dụ: MATH101)',
  `name` varchar(100) NOT NULL COMMENT 'Tên môn học',
  `credits` int(11) DEFAULT 3 COMMENT 'Số tín chỉ',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_subjects_name` (`name`),
  KEY `idx_subjects_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `credits`, `status`, `created_at`) VALUES
(1, 'SUB001', 'Cơ sở dữ liệu', 3, 'active', '2025-12-21 17:07:56'),
(2, 'SUB002', 'Lập trình C', 3, 'active', '2025-12-21 17:07:56'),
(3, 'SUB003', 'Phát triển ứng dụng', 3, 'active', '2025-12-21 17:07:56'),
(4, 'SUB004', 'Toán cao cấp', 3, 'active', '2025-12-21 17:07:56'),
(8, 'LTW001', 'Lập trình Web', 3, 'active', '2025-12-21 17:16:24');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `subject_statistics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `subject_statistics`;
CREATE TABLE IF NOT EXISTS `subject_statistics` (
`subject` varchar(100)
,`student_count` bigint(21)
,`average_score` double
,`highest_score` float
,`lowest_score` float
);

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(14, 'hs1', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'hs01@gmail.com', 'student', '2025-12-12 17:03:18'),
(15, 'gv1', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'gv1@gmail.com', 'teacher', '2025-12-21 17:40:06'),
(16, 'hs12', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'hs12@gmail.com', 'student', '2025-12-21 17:40:44');

-- --------------------------------------------------------

--
-- Cấu trúc cho view `semester_statistics`
--
DROP TABLE IF EXISTS `semester_statistics`;

DROP VIEW IF EXISTS `semester_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `semester_statistics`  AS SELECT `scores`.`semester` AS `semester`, count(0) AS `score_count`, avg(`scores`.`score`) AS `average_score`, count(distinct `scores`.`student_id`) AS `student_count` FROM `scores` GROUP BY `scores`.`semester` ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `student_scores_summary`
--
DROP TABLE IF EXISTS `student_scores_summary`;

DROP VIEW IF EXISTS `student_scores_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `student_scores_summary`  AS SELECT `s`.`id` AS `id`, `s`.`msv` AS `msv`, `s`.`fullname` AS `fullname`, count(`sc`.`id`) AS `total_scores`, avg(`sc`.`score`) AS `average_score`, max(`sc`.`score`) AS `highest_score`, min(`sc`.`score`) AS `lowest_score` FROM (`students` `s` left join `scores` `sc` on(`s`.`id` = `sc`.`student_id`)) GROUP BY `s`.`id`, `s`.`msv`, `s`.`fullname` ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `subject_statistics`
--
DROP TABLE IF EXISTS `subject_statistics`;

DROP VIEW IF EXISTS `subject_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `subject_statistics`  AS SELECT `scores`.`subject` AS `subject`, count(0) AS `student_count`, avg(`scores`.`score`) AS `average_score`, max(`scores`.`score`) AS `highest_score`, min(`scores`.`score`) AS `lowest_score` FROM `scores` GROUP BY `scores`.`subject` ;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enrollments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enrollments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reset_tokens`
--
ALTER TABLE `reset_tokens`
  ADD CONSTRAINT `reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `fk_scores_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
