<?php
session_start();
require_once '../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$sql = "SELECT name, phone, balance, avatar, pin_code FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Проверяем, есть ли данные
if (!$user) {
    die("Пользователь не найден");
}


// Обработка формы загрузки аватарки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar'];

    // Проверка, чтобы файл был загружен успешно
    if ($avatar['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Директория для загрузки
        $target_file = $target_dir . basename($avatar["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Проверка формата файла (только JPG, PNG и GIF)
        if (in_array($imageFileType, ['jpg', 'png', 'gif'])) {
            // Перемещение загруженного файла в целевую директорию
            if (move_uploaded_file($avatar["tmp_name"], $target_file)) {
                // Обновление аватарки в базе данных
                $sql = "UPDATE users SET avatar = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $target_file, $user_id);
                if ($stmt->execute()) {
                    $user['avatar'] = $target_file; // Обновляем локальную переменную
                    $message = "Аватарка успешно загружена";
                } else {
                    $message = "Ошибка при обновлении аватарки";
                }
            } else {
                $message = "Ошибка при перемещении файла";
            }
        } else {
            $message = "Поддерживаются только форматы JPG, PNG и GIF";
        }
    } else {
        $message = "Ошибка загрузки файла";
    }
}

// Обработка обновления имени
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $new_name = trim($_POST['name']);
    if (!empty($new_name)) {
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_name, $user_id);
        if ($stmt->execute()) {
            $user['name'] = $new_name; // Обновляем локальную переменную
            $message = "Имя успешно обновлено";
        } else {
            $message = "Ошибка при обновлении имени";
        }
    } else {
        $message = "Имя не может быть пустым";
    }
}

// Обработка пополнения баланса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);

    if ($amount > 0) {
        // Обновляем баланс в базе данных
        $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $amount, $user_id);
        if ($stmt->execute()) {
            $user['balance'] += $amount; 
            $message = "Баланс успешно пополнен на $$amount.";
        } else {
            $message = "Ошибка при пополнении баланса";
        }
    } else {
        $message = "Сумма должна быть положительной";
    }
}

// Обработка перевода средств
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'], $_POST['transfer_amount'])) {
    $receiver_phone = trim($_POST['phone']);
    $transfer_amount = floatval($_POST['transfer_amount']);

    if (!empty($receiver_phone) && $transfer_amount > 0) {
        // Получаем ID получателя по номеру телефона
        $sql = "SELECT id, name, balance FROM users WHERE phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $receiver_phone);
        $stmt->execute();
        $receiver_result = $stmt->get_result();
        $receiver = $receiver_result->fetch_assoc();

        // Проверяем, существует ли получатель
        if ($receiver) {
            if ($user['balance'] >= $transfer_amount) {
                // Обновляем баланс отправителя
                $sql = "UPDATE users SET balance = balance - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $transfer_amount, $user_id);
                $stmt->execute();

                // Обновляем баланс получателя
                $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $transfer_amount, $receiver['id']);
                $stmt->execute();

                // Добавляем запись о транзакции
                $description = "Перевод средств пользователю " . htmlspecialchars($receiver['name']);
                $sql = "INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ids", $user_id, $transfer_amount, $description);
                $stmt->execute();

                // Обновляем локальные переменные
                $user['balance'] -= $transfer_amount; // Обновляем баланс отправителя
                $message = "Перевод успешно выполнен на $transfer_amount$ к пользователю " . htmlspecialchars($receiver['name']) . ".";
            } else {
                $message = "Недостаточно средств для перевода";
            }
        } else {
            $message = "Пользователь с таким номером телефона не найден";
        }
    } else {
        $message = "Неверные данные для перевода";
    }
}

// Проверка обновления имени при переводе
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $new_name = trim($_POST['name']);

    if (!empty($new_name)) {
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_name, $user_id);
        if ($stmt->execute()) {
            $user['name'] = $new_name; // Обновляем локальную переменную
            $message = "Имя успешно обновлено.";
        } else {
            $message = "Ошибка при обновлении имени.";
        }
    } else {
        $message = "Имя не может быть пустым.";
    }
}

// Получаем последние транзакции
$sql = "SELECT amount, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - <?= htmlspecialchars($user['name']) ?></title>
    <link rel="stylesheet" href="css/styles.css?v3">
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

            <div class="menu_left_under_card">
                <a href="edit_profile.php">Редактировать профиль</a>
            </div>
        </div>

        <div class="content_right">
            <div class="wlc_block">
                <h1>Добро пожаловать, <?= htmlspecialchars($user['name']) ?></h1>
            </div>

            <div class="balance_block">
                <h2>Пополнить баланс</h2>
                <form method="POST" action="">
                    <input type="number" name="amount" placeholder="Сумма" class="number_top" required>
                    <button type="submit" class="btn_main_c_p">Пополнить</button>
                </form>
            </div>

            <div class="update_balance_block">
                <h2>Перевести средства</h2>
                <form method="POST" action="">
                    <input type="text" name="phone" placeholder="Номер телефона получателя" class="number_phone_down" required>
                    <input type="number" name="transfer_amount" placeholder="Сумма перевода" class="summ_down" required>
                    <button type="submit" class="btn_main_c_p">Перевести</button>
                </form>
            </div>

            <?php if (isset($message)): ?>
            <div id="modalMessage" class="modal" data-message="<?= ($message) ?>">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <p id="modalText"><?= htmlspecialchars($message) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="transactions">
                <h2>Последние транзакции</h2>
                <ul>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <li>
                                <?= htmlspecialchars($transaction['created_at']) ?> - $<?= htmlspecialchars($transaction['amount']) ?> (<?= htmlspecialchars($transaction['description']) ?>)
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Нет транзакций.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/msg.js"></script>
<script type="text/javascript" src="js/ava.js"></script>
</body>
</html>
