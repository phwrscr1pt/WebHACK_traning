-- LOCTH Lab Database Initialization
-- All passwords WITHOUT numbers to match login.php filter

-- Create database
CREATE DATABASE IF NOT EXISTS locth_lab;
USE locth_lab;

-- Drop existing tables
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_clear VARCHAR(50) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create notes table
CREATE TABLE notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert users (passwords WITHOUT any numbers 0-9)
INSERT INTO users (id, username, password_clear, role) VALUES
(1, 'admin', 'adminsecret', 'admin'),
(2, 'staff', 'staffpassword', 'staff'),
(3, 'shadow_curator', 'curatorsecretkey', 'curator'),
(4, 'head_curator', 'masterkey', 'head'),
(5, 'guest', 'guestpass', 'guest');

-- Insert notes for shadow_curator (user_id = 3)
INSERT INTO notes (id, user_id, title, content) VALUES
(501, 3, 'Project Notes', 'These are my personal project notes for the web security lab. Working on improving authentication mechanisms and database security.'),
(502, 3, 'Meeting Minutes', 'Discussed new security features and vulnerability testing procedures. Next meeting scheduled for review of penetration testing results.'),
(503, 3, 'Todo List', 'Complete stages 1-5, review code for vulnerabilities, prepare documentation for security assessment, update password policies.');

-- Insert secret note with FLAG (accessible via IDOR - note_id = 555)
INSERT INTO notes (id, user_id, title, content) VALUES
(555, 1, 'Secret Admin Note', 'Congratulations! You found the secret note through IDOR vulnerability.

Flag #4: LOCTH{idor_access_granted}

QA Mode has been enabled for Stage 5 (File Upload).
You can now proceed to upload.php to continue the challenge.');

-- Insert additional notes for head_curator
INSERT INTO notes (id, user_id, title, content) VALUES
(601, 4, 'Security Audit Report', 'Annual security audit completed. Several vulnerabilities identified and documented for remediation.'),
(602, 4, 'Access Control Policy', 'Updated access control policies to restrict unauthorized access to sensitive resources.');

-- Display results
SELECT '==================================' AS '';
SELECT 'Database initialization complete!' AS 'STATUS';
SELECT '==================================' AS '';

SELECT '' AS '';
SELECT 'USERS TABLE:' AS '';
SELECT id, username, password_clear, role FROM users ORDER BY id;

SELECT '' AS '';
SELECT 'NOTES TABLE:' AS '';
SELECT id, title, user_id FROM notes ORDER BY id;

SELECT '' AS '';
SELECT '==================================' AS '';
SELECT 'IMPORTANT: All passwords have NO numbers!' AS 'NOTICE';
SELECT '==================================' AS '';
