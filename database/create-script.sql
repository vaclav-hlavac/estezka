SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
USE e_stezka;

-- Drop tables if exist
DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS notification;
DROP TABLE IF EXISTS task_progress;
DROP TABLE IF EXISTS patrol_member;
DROP TABLE IF EXISTS patrol_leader;
DROP TABLE IF EXISTS patrol;
DROP TABLE IF EXISTS task;
DROP TABLE IF EXISTS troop_leader;
DROP TABLE IF EXISTS troop;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS refresh_tokens;

-- Create tables

CREATE TABLE troop (
    id_troop INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(256) NOT NULL,
    PRIMARY KEY (id_troop)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user (
    id_user INT AUTO_INCREMENT NOT NULL,
    nickname VARCHAR(256) NOT NULL,
    name VARCHAR(256) NOT NULL,
    surname VARCHAR(256) NOT NULL,
    password VARCHAR(256) NOT NULL,
    email VARCHAR(256) NOT NULL UNIQUE,
    avatar_url VARCHAR(512) DEFAULT NULL,
    notifications_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patrol (
    id_patrol INT AUTO_INCREMENT NOT NULL,
    id_troop INT NOT NULL,
    name VARCHAR(256) NOT NULL,
    color VARCHAR(256),
    invite_code VARCHAR(256) NOT NULL UNIQUE,
    PRIMARY KEY (id_patrol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patrol_leader (
    id_patrol_leader INT AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    id_patrol INT NOT NULL,
    PRIMARY KEY (id_patrol_leader)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE patrol_member (
    id_user INT NOT NULL,
    id_patrol INT,
    active_path_level INT,
    PRIMARY KEY (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task (
    id_task INT AUTO_INCREMENT NOT NULL,
    number INT NOT NULL,
    name VARCHAR(256) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(256) NOT NULL,
    subcategory VARCHAR(256) NOT NULL,
    tag VARCHAR(256),
    path_level INT NOT NULL,
    id_troop INT,
    PRIMARY KEY (id_task)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_progress (
    id_task_progress INT AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    id_task INT NOT NULL,
    status VARCHAR(256) NOT NULL,
    planned_to DATE,
    signed_at DATETIME,
    witness VARCHAR(256),
    id_confirmed_by INT,
    confirmed_at DATETIME,
    filled_text TEXT,
    PRIMARY KEY (id_task_progress),
    UNIQUE KEY (id_user, id_task)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT NOT NULL,
    id_user_creator INT NOT NULL,
    id_user_receiver INT NOT NULL,
    text TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'generic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    was_received BOOLEAN NOT NULL DEFAULT FALSE,
    id_task_progress INT,
    creator_name VARCHAR(50),
    PRIMARY KEY (id_notification)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comment (
    id_comment INT AUTO_INCREMENT NOT NULL,
    id_task_progress INT,
    user_by INT NOT NULL,
    user_to INT NOT NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    text TEXT NOT NULL,
    PRIMARY KEY (id_comment)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE troop_leader (
    id_troop_leader INT AUTO_INCREMENT NOT NULL,
    id_user INT NOT NULL,
    id_troop INT,
    PRIMARY KEY (id_troop_leader)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE refresh_tokens (
    id_refresh_token INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create foreign keys
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE patrol
ADD CONSTRAINT fk_patrol_troop
FOREIGN KEY (id_troop) REFERENCES troop (id_troop) ON DELETE CASCADE;

ALTER TABLE patrol_leader
ADD CONSTRAINT fk_patrol_leader_user
FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE patrol_leader
ADD CONSTRAINT fk_patrol_leader_patrol
FOREIGN KEY (id_patrol) REFERENCES patrol (id_patrol) ON DELETE CASCADE;

ALTER TABLE patrol_member
ADD CONSTRAINT fk_patrol_member_user
FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE patrol_member
ADD CONSTRAINT fk_patrol_member_patrol
FOREIGN KEY (id_patrol) REFERENCES patrol (id_patrol) ON DELETE CASCADE;

ALTER TABLE task
ADD CONSTRAINT fk_task_troop
FOREIGN KEY (id_troop) REFERENCES troop (id_troop) ON DELETE CASCADE;

ALTER TABLE task_progress
ADD CONSTRAINT fk_task_progress_patrol_member
FOREIGN KEY (id_user) REFERENCES patrol_member (id_user) ON DELETE CASCADE;

ALTER TABLE task_progress
ADD CONSTRAINT fk_task_progress_task
FOREIGN KEY (id_task) REFERENCES task (id_task) ON DELETE CASCADE;

ALTER TABLE task_progress
ADD CONSTRAINT fk_task_progress_user
FOREIGN KEY (id_confirmed_by) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE notification
ADD CONSTRAINT fk_notification_task_progress
FOREIGN KEY (id_task_progress) REFERENCES task_progress (id_task_progress) ON DELETE CASCADE;

ALTER TABLE notification
ADD CONSTRAINT fk_notification_user_creator
FOREIGN KEY (id_user_creator) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE notification
ADD CONSTRAINT fk_notification_user_receiver
FOREIGN KEY (id_user_receiver) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE comment
ADD CONSTRAINT fk_comment_task_progress
FOREIGN KEY (id_task_progress) REFERENCES task_progress (id_task_progress) ON DELETE CASCADE;

ALTER TABLE comment
ADD CONSTRAINT fk_comment_user_by
FOREIGN KEY (user_by) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE comment
ADD CONSTRAINT fk_comment_user_to
FOREIGN KEY (user_to) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE troop_leader
ADD CONSTRAINT fk_troop_leader_user
FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE;

ALTER TABLE troop_leader
ADD CONSTRAINT fk_troop_leader_troop
FOREIGN KEY (id_troop) REFERENCES troop (id_troop) ON DELETE CASCADE;

ALTER TABLE refresh_tokens
ADD CONSTRAINT fk_refresh_tokens_user
FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE;