<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$search_allowed = $_SESSION['search_allowed'];
$role = $_SESSION['role'];
$search_results = [];
$search_performed = false;

// Проверка поиска
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    if (!$search_allowed && $role !== 'admin') {
        $error = "❌ Вам запрещен доступ к поиску. Обратитесь к администратору.";
        
        // Запись в лог
        $log = $pdo->prepare("INSERT INTO access_log (user_id, action_type, details, ip_address) VALUES (?, 'BLOCK_SEARCH', ?, ?)");
        $log->execute([$user_id, 'Попытка поиска при запрете', $_SERVER['REMOTE_ADDR']]);
    } else {
        $search_performed = true;
        $query = $_POST['query'] ?? '';
        
        $sql = "SELECT * FROM phonebook WHERE 
                last_name LIKE ? OR 
                first_name LIKE ? OR 
                phone LIKE ? OR 
                department LIKE ?";
        $like = "%$query%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$like, $like, $like, $like]);
        $search_results = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск - Телефонный справочник</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">📞 Телефонный <span>Справочник</span></div>
            <div class="nav">
                <a href="dashboard.php">Поиск</a>
                <?php if ($role === 'admin'): ?>
                    <a href="admin.php">👑 Админ-панель</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-logout">🚪 Выйти</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>👋 Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h2>
            <p>Статус доступа к поиску: 
                <?php if ($search_allowed || $role === 'admin'): ?>
                    <span style="color: green;">✅ Разрешен</span>
                <?php else: ?>
                    <span style="color: red;">❌ Запрещен</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="card">
            <h2>🔍 Поиск сотрудника</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="search-box">
                <input type="text" name="query" placeholder="Введите фамилию, имя, телефон или отдел..." required>
                <button type="submit" name="search" class="btn btn-primary">🔍 Искать</button>
            </form>
            
            <?php if ($search_performed): ?>
                <h3>Результаты поиска (<?= count($search_results) ?>)</h3>
                <?php if (count($search_results) > 0): ?>
                    <table>
                        <thead>
                            <tr><th>ФИО</th><th>Телефон</th><th>Отдел</th><th>Должность</th><th>Кабинет</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $contact): ?>
                            <tr>
                                <td><?= htmlspecialchars($contact['last_name'] . ' ' . $contact['first_name'] . ' ' . $contact['patronymic']) ?></td>
                                <td><?= htmlspecialchars($contact['phone']) ?></td>
                                <td><?= htmlspecialchars($contact['department']) ?></td>
                                <td><?= htmlspecialchars($contact['position']) ?></td>
                                <td><?= htmlspecialchars($contact['office']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Ничего не найдено</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>