<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

$from_user_id = $_SESSION['user_id'];
$to_user_id = $_POST['to_user_id'] ?? '';
$amount = $_POST['amount'] ?? '';

if (empty($to_user_id) || empty($amount) || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

// Проверяем наличие пользователя-получателя
$sql = "SELECT id, balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $to_user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipient = $result->fetch_assoc();

if (!$recipient) {
    echo json_encode(['status' => 'error', 'message' => 'Recipient not found']);
    exit();
}

// Проверяем баланс отправителя
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $from_user_id);
$stmt->execute();
$result = $stmt->get_result();
$sender = $result->fetch_assoc();

if ($sender['balance'] < $amount) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient funds']);
    exit();
}

// Начинаем транзакцию
$conn->begin_transaction();

try {
    // Снимаем средства у отправителя
    $sql = "UPDATE users SET balance = balance - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $from_user_id);
    $stmt->execute();

    // Добавляем средства получателю
    $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $to_user_id);
    $stmt->execute();

    // Добавляем запись о транзакции для отправителя
    $sql = "INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $description_sender = "Transfer to user #$to_user_id";
    $stmt->bind_param("ids", $from_user_id, -$amount, $description_sender);
    $stmt->execute();

    // Добавляем запись о транзакции для получателя
    $sql = "INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $description_recipient = "Received transfer from user #$from_user_id";
    $stmt->bind_param("ids", $to_user_id, $amount, $description_recipient);
    $stmt->execute();

    // Подтверждаем транзакцию
    $conn->commit();

    // Успешный ответ
    echo json_encode(['status' => 'success', 'message' => "Transfer of $$amount to user #$to_user_id completed successfully."]);
} catch (Exception $e) {
    // Откат транзакции в случае ошибки
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Transfer failed']);
}

?>