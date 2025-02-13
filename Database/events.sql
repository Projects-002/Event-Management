
CREATE TABLE `events` (
  `id` int primary key AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT now()
);


