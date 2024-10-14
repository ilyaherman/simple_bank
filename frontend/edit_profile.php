<?php
session_start();
require_once '../backend/config/database.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$sql = "SELECT name, phone, avatar, balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Пользователь не найден");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="css/styles.css?v2">
</head>
<body>
<div id="container">
    <div id="header">
        <div class="navbar">
            <div class="menu_left">
                <a href="dashboard.php">Профиль</a>
            </div>
            <div class="menu_right">
                <a href="logout.php">Выход</a>
            </div>
        </div>
    </div>

    <div id="content_main">
        <div class="sidebar">
            <div class="profile_card_left">
                <h2>Информация</h2>
                <div class="info_card_user">
                    <div class="avatar_main">
                        <img src="<?= htmlspecialchars($user['avatar'] ?: 'assets/images/default-avatar.png') ?>" alt="Аватар" class="avatar">
                    </div>
                    <div class="info_user_block">
                        <p>Имя: <?= htmlspecialchars($user['name']) ?></p>
                        <p>Телефон: <?= htmlspecialchars($user['phone']) ?></p>
                        <p>Баланс: $<?= htmlspecialchars($user['balance']) ?></p> 
                    </div>
                </div>
            </div> 

        </div>

        <div class="content_right">
            <div class="wlc_block">
                <h1>Редактирования профиля, <?= htmlspecialchars($user['name']) ?></h1>
            </div>

            <!-- Форма редактирования профиля -->
            <div class="profile_edit_form">
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" class="name_block_bar" value="<?= htmlspecialchars($user['name']) ?>" required>

                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" class="number_top" value="<?= htmlspecialchars($user['phone']) ?>" required>

                    <label for="avatar">Изменить аватар:</label>
                    <input type="file" id="avatar" name="avatar" class="number_top">

                    <button type="submit" class="btn">Сохранить изменения</button>
                </form>
            </div>

        </div>
    </div>
</div>
</body>
</html>
