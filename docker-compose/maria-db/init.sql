CREATE DATABASE IF NOT EXISTS `vk_internship_backend_api`
    CHARACTER SET = 'utf8mb4'
    COLLATE = 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS `vk_internship_backend_api`.`events` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_status` tinyint(3) UNSIGNED NOT NULL,
    `ip` varchar(39) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vk_internship_backend_api`.`events_distribution`
(
    `events_hash` BINARY(8) NOT NULL,
    `counter`     INT UNSIGNED NOT NULL DEFAULT '1',
    INDEX         `DatedEventHash` (`events_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
