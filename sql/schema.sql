-- ============================================
-- Saxho.net — Schema de base de donnees
-- Base : u473667317_saxhonet
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------
-- Table : users
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `job_title` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `email_token` VARCHAR(255) DEFAULT NULL,
    `email_token_expires` DATETIME DEFAULT NULL,
    `mfa_secret` VARCHAR(255) DEFAULT NULL,
    `mfa_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `mfa_backup_codes` TEXT DEFAULT NULL,
    `login_attempts` INT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : projects (portfolio)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name_fr` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `image` VARCHAR(255) DEFAULT NULL,
    `pitch_fr` TEXT NOT NULL,
    `pitch_en` TEXT DEFAULT NULL,
    `domain` VARCHAR(100) NOT NULL,
    `problem_fr` TEXT NOT NULL,
    `problem_en` TEXT DEFAULT NULL,
    `solution_fr` TEXT NOT NULL,
    `solution_en` TEXT DEFAULT NULL,
    `phase` ENUM('ideation', 'study', 'prototype', 'development', 'pre_launch', 'transferred') NOT NULL DEFAULT 'ideation',
    `investment_sought` VARCHAR(100) DEFAULT NULL,
    `skills_sought_fr` TEXT DEFAULT NULL,
    `skills_sought_en` TEXT DEFAULT NULL,
    `launch_date` DATE DEFAULT NULL,
    `status` ENUM('open', 'complete', 'paused') NOT NULL DEFAULT 'open',
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `display_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_phase` (`phase`),
    INDEX `idx_status` (`status`),
    INDEX `idx_visible` (`is_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : interest_requests
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `interest_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `project_id` INT UNSIGNED NOT NULL,
    `type` ENUM('competence', 'investment') NOT NULL,
    -- Snapshot des coordonnees au moment de la demande
    `contact_company` VARCHAR(255) NOT NULL,
    `contact_job_title` VARCHAR(255) NOT NULL,
    `contact_phone` VARCHAR(20) NOT NULL,
    `contact_address` TEXT NOT NULL,
    `contact_country` VARCHAR(100) NOT NULL,
    -- Details communs
    `message` TEXT DEFAULT NULL,
    -- Si competences
    `expertise_domain` TEXT DEFAULT NULL,
    `availability` VARCHAR(255) DEFAULT NULL,
    `linkedin_cv_url` VARCHAR(500) DEFAULT NULL,
    -- Si investissement
    `investment_range` ENUM('less_10k', '10k_50k', '50k_100k', 'more_100k', 'to_discuss') DEFAULT NULL,
    `investment_experience` TEXT DEFAULT NULL,
    `investment_structure` VARCHAR(255) DEFAULT NULL,
    -- Suivi
    `status` ENUM('submitted', 'in_progress', 'finalized', 'declined') NOT NULL DEFAULT 'submitted',
    `admin_notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_project` (`project_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_interest_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_interest_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : password_resets
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token` (`token`),
    INDEX `idx_user` (`user_id`),
    CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : blog_categories
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name_fr` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : blog_posts
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title_fr` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content_fr` LONGTEXT NOT NULL,
    `content_en` LONGTEXT DEFAULT NULL,
    `excerpt_fr` TEXT DEFAULT NULL,
    `excerpt_en` TEXT DEFAULT NULL,
    `cover_image` VARCHAR(255) DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME DEFAULT NULL,
    `reading_time` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_published` (`published_at`),
    INDEX `idx_category` (`category_id`),
    CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : contact_messages
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `company` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_read` (`is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- Table : blog_comments
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_comment_post` (`post_id`),
    INDEX `idx_comment_user` (`user_id`),
    CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
