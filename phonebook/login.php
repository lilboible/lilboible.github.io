<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND is_active = 1");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['search_allowed'] = $user['search_allowed'];
        
        // Логирование успешного входа
        $log = $pdo->prepare("INSERT INTO access_log (user_id, action_type, ip_address) VALUES (?, 'LOGIN_SUCCESS', ?)");
        $log->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
        // Логирование неудачной попытки
        if ($user) {
            $log = $pdo->prepare("INSERT INTO access_log (user_id, action_type, ip_address) VALUES (?, 'LOGIN_FAIL', ?)");
            $log->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - Телефонный справочник</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">📞 Телефонный <span>Справочник</span></div>
            <div class="nav">
                <a href="index.php">Главная</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <h2>🔐 Вход в систему</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" name="login" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
            
            <p style="margin-top: 15px; font-size: 12px; color: #888;">
                Тестовые учетки:<br>
                admin / password (Администратор)<br>
                ivanov / password (Поиск разрешен)<br>
                petrov / password (Поиск запрещен)
            </p>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>