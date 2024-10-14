<?php
// backend/db_connect.php
$servername = "localhost";  // Имя сервера базы данных
$username = "root";         // Имя пользователя базы данных
$password = "";             // Пароль для базы данных (оставь пустым, если нет)
$dbname = "simple_bank";    // Имя базы данных

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
