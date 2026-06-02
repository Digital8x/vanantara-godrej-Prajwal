CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `project` varchar(255) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `device_type` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meta_key` (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`meta_key`, `meta_value`) VALUES
('smtp_host', 'mail.shivabihani.com'),
('smtp_user', 'leads@shivabihani.com'),
('smtp_pass', '={3)%J6b1mh7'),
('smtp_port', '465'),
('smtp_from_name', 'Godrej Vanantara Leads'),
('cc_emails', 'binodbihanij@yahoo.com,henry_siva@outlook.com'),
('admin_email', 'harshmheswry@gmail.com,diyarjun9@gmail.com')
ON DUPLICATE KEY UPDATE `meta_value` = VALUES(`meta_value`);
