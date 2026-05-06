-- Создание БД
CREATE DATABASE IF NOT EXISTS phonebook;
USE phonebook;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    search_allowed BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица телефонного справочника
CREATE TABLE phonebook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    phone VARCHAR(20) NOT NULL,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    office VARCHAR(20),
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Таблица логов блокировок
CREATE TABLE access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NULL,
    action_type ENUM('BLOCK_SEARCH', 'UNBLOCK_SEARCH', 'LOGIN_FAIL', 'LOGIN_SUCCESS', 'MANUAL_BLOCK') NOT NULL,
    action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    details TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Отделы и руководители
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    head_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Вставка тестовых данных
INSERT INTO users (login, password_hash, full_name, role, search_allowed) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Администратор Системы', 'admin', TRUE),
('ivanov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Иванов Иван Иванович', 'employee', TRUE),
('petrov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Петров Петр Петрович', 'employee', FALSE);

INSERT INTO phonebook (last_name, first_name, patronymic, phone, department, position, office, created_by) VALUES
('Иванов', 'Иван', 'Иванович', '+7 (123) 456-78-90', 'IT-отдел', 'Старший разработчик', '101', 1),
('Петров', 'Петр', 'Петрович', '+7 (123) 456-78-91', 'Бухгалтерия', 'Бухгалтер', '202', 1),
('Сидорова', 'Анна', 'Сергеевна', '+7 (123) 456-78-92', 'Отдел кадров', 'Менеджер', '303', 1);

INSERT INTO departments (name, head_name, description) VALUES
('IT-отдел', 'Иванов Иван Иванович', 'Разработка и поддержка программного обеспечения'),
('Бухгалтерия', 'Петрова Елена Васильевна', 'Финансовый учет и отчетность'),
('Отдел кадров', 'Сидорова Анна Сергеевна', 'Управление персоналом');

-- Пароли для тестов (все 'password' хэшированы)
-- admin / password
-- ivanov / password  
-- petrov / password