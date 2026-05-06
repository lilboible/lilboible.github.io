<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Телефонный справочник</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="logo">📞 Телефонный <span>Справочник</span></div>
            <div class="nav">
                <a href="index.php">Главная</a>
                <a href="login.php">Войти</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="org-info">
            <h1>ООО "ТехноКомпания"</h1>
            <p>Мы создаем инновационные решения для бизнеса с 2010 года</p>
        </div>

        <div class="card">
            <h2>📌 О компании</h2>
            <p>Наша компания занимается разработкой программного обеспечения, IT-консалтингом и внедрением цифровых решений. Мы ценим каждого сотрудника и создаем комфортные условия для работы.</p>
        </div>

        <div class="card">
            <h2>🏢 Отделы и руководители</h2>
            <?php
            $stmt = $pdo->query("SELECT * FROM departments");
            while ($dept = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <div class="department-card">
                <h3><?= htmlspecialchars($dept['name']) ?></h3>
                <p><strong>Руководитель:</strong> <?= htmlspecialchars($dept['head_name']) ?></p>
                <p><?= htmlspecialchars($dept['description']) ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>© 2024 Телефонный справочник. Все права защищены.</p>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>