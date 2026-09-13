CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `related_item_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `related_item_id`, `is_read`, `created_at`) VALUES
(1, 'Report Submitted', 'Your lost item report for "Apple iPhone 13" has been submitted successfully.', 'report_submitted', 1, 1, NOW() - INTERVAL 5 DAY),
(1, 'Report Approved', 'Your report for "Apple iPhone 13" has been approved by the administrator.', 'report_approved', 1, 0, NOW() - INTERVAL 4 DAY),
(2, 'Possible Match Found', 'A possible match has been found for your reported "Dell XPS 15". Check the item details.', 'possible_match', 2, 0, NOW() - INTERVAL 1 DAY),
(3, 'Item Returned', 'Your reported item "Math Notebook" has been marked as returned.', 'item_returned', 8, 1, NOW() - INTERVAL 10 DAY),
(4, 'Report Rejected', 'Your report for "Unknown Keys" was rejected. Please review the report details.', 'report_rejected', 12, 0, NOW() - INTERVAL 2 HOUR);
