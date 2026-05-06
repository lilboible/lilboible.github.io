<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $login = $_POST['login'];
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = $_POST['full_name'];
        $role = $_POST['role'];
        $search_allowed = isset($_POST['search_allowed']) ? 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO users (login, password_hash, full_name, role, search_allowed) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$login, $password_hash, $full_name, $role, $search_allowed]);
        
        $success = "Пользователь создан";
    }
    
    if (isset($_POST['toggle_search'])) {
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'];
        
        $stmt = $pdo->prepare("UPDATE users SET search_allowed = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        
        $action = $new_status ? 'UNBLOCK_SEARCH' : 'BLOCK_SEARCH';
        $log = $pdo->prepare("INSERT INTO access_log (user_id, admin_id, action_type, ip_address) VALUES (?, ?, ?, ?)");
        $log->execute([$user_id, $_SESSION['user_id'], $action, $_SERVER['REMOTE_ADDR']]);
        
        $success = "Статус поиска обновлен";
    }
    
    if (isset($_POST['add_contact'])) {
        $stmt = $pdo->prepare("INSERT INTO phonebook (last_name, first_name, patronymic, phone, department, position, office, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['last_name'], $_POST['first_name'], $_POST['patronymic'],
            $_POST['phone'], $_POST['department'], $_POST['position'],
            $_POST['office'], $_SESSION['user_id']
        ]);
        $success = "Контакт добавлен";
    }
}

// Получение данных
$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();
$logs = $pdo->query("SELECT l.*, u.full_name as user_name FROM access_log l JOIN users u ON l.user_id = u.id ORDER BY l.action_time DESC LIMIT 50")->fetchAll();
$contacts = $pdo->query("SELECT * FROM phonebook ORDER BY id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Телефонный справочник</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">📞 Телефонный <span>Справочник</span></div>
            <div class="nav">
                <a href="dashboard.php">Поиск</a>
                <a href="admin.php">👑 Админ-панель</a>
                <a href="logout.php" class="btn-logout">🚪 Выйти</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Создание пользователя -->
        <div class="card">
            <h2>➕ Создание учетной записи</h2>
            <form method="POST">
                <div class="form-group"><label>Логин</label><input type="text" name="login" required></div>
                <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
                <div class="form-group"><label>ФИО</label><input type="text" name="full_name" required></div>
                <div class="form-group">
                    <label>Роль</label>
                    <select name="role">
                        <option value="employee">Сотрудник</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="search_allowed" checked> Разрешить поиск</label>
                </div>
                <button type="submit" name="create_user" class="btn btn-primary">Создать</button>
            </form>
        </div>
        
        <!-- Управление пользователями -->
        <div class="card">
            <h2>👥 Управление пользователями</h2>
            <table>
                <thead><tr><th>ID</th><th>Логин</th><th>ФИО</th><th>Роль</th><th>Поиск</th><th>Действие</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td><?= $user['role'] ?></td>
                        <td><?= $user['search_allowed'] ? '✅ Разрешен' : '❌ Запрещен' ?></td>
                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="new_status" value="<?= $user['search_allowed'] ? 0 : 1 ?>">
                                <button type="submit" name="toggle_search" class="btn <?= $user['search_allowed'] ? 'btn-warning' : 'btn-primary' ?>" style="padding: 5px 10px; font-size: 12px;">
                                    <?= $user['search_allowed'] ? '🔒 Запретить' : '✅ Разрешить' ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <span style="color: gray;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- История блокировок -->
        <div class="card">
            <h2>📜 История блокировок и доступов</h2>
            <table>
                <thead><tr><th>Время</th><th>Пользователь</th><th>Действие</th><th>IP</th><th>Детали</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['action_time'] ?></td>
                        <td><?= htmlspecialchars($log['user_name']) ?></td>
                        <td><?= $log['action_type'] ?></td>
                        <td><?= $log['ip_address'] ?></td>
                        <td><?= htmlspecialchars($log['details'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Добавление контакта -->
        <div class="card">
            <h2>📞 Добавить контакт в справочник</h2>
            <form method="POST">
                <div class="form-group"><label>Фамилия</label><input type="text" name="last_name" required></div>
                <div class="form-group"><label>Имя</label><input type="text" name="first_name" required></div>
                <div class="form-group"><label>Отчество</label><input type="text" name="patronymic"></div>
                <div class="form-group"><label>Телефон</label><input type="text" name="phone" required></div>
                <div class="form-group"><label>Отдел</label><input type="text" name="department" required></div>
                <div class="form-group"><label>Должность</label><input type="text" name="position" required></div>
                <div class="form-group"><label>Кабинет</label><input type="text" name="office"></div>
                <button type="submit" name="add_contact" class="btn btn-primary">Добавить</button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>