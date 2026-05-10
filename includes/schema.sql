-- Digital Harbor admin schema
-- Run once after creating empty DB: mysql -u root -p digital_harbor < schema.sql

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(120) NOT NULL,
    role          ENUM('admin','viewer') NOT NULL DEFAULT 'viewer',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login    DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    email      VARCHAR(255) NOT NULL,
    company    VARCHAR(255) NULL,
    budget     VARCHAR(50)  NULL,
    scope      VARCHAR(50)  NULL,
    message    TEXT NOT NULL,
    status     ENUM('unread','read','replied','archived') NOT NULL DEFAULT 'unread',
    ip         VARCHAR(45)  NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed first admin via /admin/setup.php (only runs when users table is empty).
