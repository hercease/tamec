-- Documents & User Documents Migration
-- Run this SQL in your tamec database

CREATE TABLE `documents` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_name` varchar(255) NOT NULL,
  `doc_tag` varchar(100) NOT NULL,
  `optional` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_id`),
  UNIQUE KEY `uq_doc_tag` (`doc_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_documents` (
  `user_doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `doc_id` int(11) NOT NULL,
  `doc_tag` varchar(100) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_doc_id`),
  UNIQUE KEY `uq_staff_doc` (`staff_id`, `doc_id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_doc_id` (`doc_id`),
  CONSTRAINT `fk_ud_staff` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`staff_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ud_doc` FOREIGN KEY (`doc_id`) REFERENCES `documents` (`doc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
