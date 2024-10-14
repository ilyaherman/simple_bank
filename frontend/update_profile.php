<?php
session_start();
require_once '../backend/config/database.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Проверка отправленных данных
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    // Валидация данных
    if (empty($name) || empty($phone)) {
        die("Все поля обязательны для заполнения");
    }

    // Обработка аватара, если он загружен
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar = $_FILES['avatar'];
        $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
        $newAvatarName = uniqid() . "." . $ext;
        $avatarPath = 'uploads/' . $newAvatarName;

        // Перемещаем загруженный файл
        if (move_uploaded_file($avatar['tmp_name'], $avatarPath)) {
            // Обновляем данные пользователя с новым аватаром
            $sql = "UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $phone, $newAvatarName, $user_id);
        } else {
            die("Ошибка загрузки аватара");
        }
    } else {
        // Обновляем данные пользователя без аватара
        $sql = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $phone, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        die("Ошибка обновления данных");
    }
} else {
    die("Неправильный метод запроса");
}
