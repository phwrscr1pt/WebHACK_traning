USE locth_lab;

-- Users table for SQLi challenges
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    password_clear VARCHAR(50) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notes table for IDOR challenge
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert users
INSERT INTO users (username, password_hash, password_clear, role) VALUES
('admin', '$2y$10$abcdefghijklmnopqrstuv', 'admin123', 'admin'),
('staff', '$2y$10$zyxwvutsrqponmlkjihgfe', 'staff456', 'staff'),
('shadow_curator', '$2y$10$curator_hash_here_12345', 'cur4t0r', 'curator'),
('head_curator', '$2y$10$head_curator_hash_67890', 'master_key', 'head');

-- Insert notes for shadow_curator (id=3)
INSERT INTO notes (user_id, title, content) VALUES
(3, 'My First Note', 'This is a regular note by shadow_curator'),
(3, 'Project Ideas', 'Some ideas for the project: refactor, optimize, test'),
(3, 'Meeting Notes', 'Discussed the new security measures with the team');

-- Insert secret note for head_curator (id=4) - This is the IDOR target
INSERT INTO notes (user_id, title, content) VALUES
(4, 'SECRET: Master Access', 'LOCTH{idor_masterpiece}\n\nThis note contains the flag for Stage 4. Well done on exploiting the IDOR vulnerability!\n\nQA Mode is now enabled for file upload testing.');

-- Update note IDs to match requirements
UPDATE notes SET id = 501 WHERE user_id = 3 AND title = 'My First Note';
UPDATE notes SET id = 502 WHERE user_id = 3 AND title = 'Project Ideas';
UPDATE notes SET id = 503 WHERE user_id = 3 AND title = 'Meeting Notes';
UPDATE notes SET id = 555 WHERE user_id = 4;
