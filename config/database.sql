-- Database creation
CREATE DATABASE IF NOT EXISTS ticketing_system;
\c ticketing_system;  -- Esto es para conectarse a la base de datos después de crearla.

-- Definir ENUMs
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'role_enum') THEN
        CREATE TYPE role_enum AS ENUM ('user', 'support', 'administrator');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_enum') THEN
        CREATE TYPE status_enum AS ENUM ('pending', 'in_progress', 'resolved');
    END IF;
END $$;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role role_enum NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    profile_pic VARCHAR(255) DEFAULT './assets/img/profilepic.webp'
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Tickets table
CREATE TABLE IF NOT EXISTS tickets (
    id SERIAL PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category_id INT,
    created_by INT NOT NULL,
    assigned_to INT,
    status status_enum NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Files table
CREATE TABLE IF NOT EXISTS attachments (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

-- Labels table
CREATE TABLE IF NOT EXISTS labels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#cccccc'
);

-- Relation tickets with labels
CREATE TABLE IF NOT EXISTS ticket_labels (
    ticket_id INT NOT NULL,
    label_id INT NOT NULL,
    PRIMARY KEY (ticket_id, label_id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    site_name VARCHAR(100) NOT NULL UNIQUE,
    admin_email VARCHAR(100) NOT NULL UNIQUE
);

-- Chat messages table
CREATE TABLE IF NOT EXISTS ticket_messages (
    id SERIAL PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    can_reply BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Example data, user passwords are: password123
INSERT INTO users (name, email, password, role) VALUES
('Alex Thompson', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Emma Wilson', 'emma.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Lucas Rodriguez', 'lucas.rodriguez@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Sarah Martinez', 'support@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'support'),
('Michael Chen', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator');

INSERT INTO categories (name, description) VALUES
('Access', 'Problems related with the access of the page'),
('Errors', 'Errors and failures of the system'),
('Asking', 'Ask something of the system'),
('Improvements', 'Suggest an improvement');

INSERT INTO labels (name, color) VALUES
('Urgent', '#ff0000'),
('Low', '#00ff00'),
('Mid', '#ffff00'),
('High', '#ff9900');

-- Example tickets for users
INSERT INTO tickets (subject, message, category_id, created_by, assigned_to, status) VALUES
-- Alex Thompson's tickets
('Login Issue', 'I cannot access my account after changing my password', 1, 1, 2, 'pending'),
('Feature Request', 'Would it be possible to add dark mode to the interface?', 4, 1, 2, 'in_progress'),

-- Emma Wilson's tickets
('Error 404', 'Getting 404 error when trying to access reports page', 2, 4, 2, 'pending'),
('Dashboard Loading Slow', 'The dashboard takes too long to load', 2, 4, 3, 'in_progress'),

-- Lucas Rodriguez's tickets
('Password Reset', 'Need help resetting my password', 1, 5, 2, 'resolved'),
('New Feature Suggestion', 'Could we add export to PDF functionality?', 4, 5, 3, 'pending');

-- Add some labels to tickets
INSERT INTO ticket_labels (ticket_id, label_id) VALUES
(1, 1), -- Urgent label for login issue
(2, 3), -- Mid priority for feature request
(3, 4), -- High priority for 404 error
(4, 3), -- Mid priority for slow loading
(5, 2), -- Low priority for password reset
(6, 3); -- Mid priority for feature suggestion

-- Default site settings
INSERT INTO settings (site_name, admin_email) VALUES
('Ticketing System', 'admin@example.com');

-- Example chat messages
INSERT INTO ticket_messages (ticket_id, user_id, message, can_reply) VALUES
-- Chat for Alex's login issue
(1, 1, 'I tried resetting my password but still cannot access', FALSE),
(1, 2, 'Could you please tell me what error message you are seeing?', TRUE),
(1, 1, 'It says "Invalid credentials"', FALSE),
(1, 2, 'Let me reset your password manually. Please check your email in 5 minutes.', TRUE),

-- Chat for Emma's 404 error
(3, 4, 'The error occurs every time I try to access /reports/monthly', FALSE),
(3, 2, 'Thanks for the details. I will check the URL configuration.', TRUE),

-- Chat for Lucas's password reset
(5, 5, 'I need to change my password urgently', FALSE),
(5, 2, 'I have sent you a password reset link to your email', TRUE),
(5, 5, 'Thank you, I was able to change it successfully', FALSE),
(5, 2, 'Great! Let me know if you need anything else', TRUE);
