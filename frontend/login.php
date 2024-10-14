<?php
session_start();
require_once '../backend/config/database.php';

// Проверка, если пользователь уже залогинен
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';

// Проверка, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Получаем пользователя из базы данных
    $sql = "SELECT id, password FROM users WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Проверка пароля
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Неправильный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Подключаем CSS файл -->
</head>
<body>

<div class="container login-container">
    <div id="main_block_page">
        <div class="page_block_m">
            <h1>Вход</h1>
            
            <!-- Сообщение об ошибке -->
            <?php if ($message): ?>
                <p style="color: red;"><?= $message; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="phone" name="phone" placeholder="phone" class="login_phone">
                <input type="password" name="password" placeholder="Пароль" class="login_pwd">
                <button type="submit">Войти</button>
            </form>

            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </div>
</div>

</body>
</html>
