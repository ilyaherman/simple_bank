<!-- frontend/register.php -->
<?php
    // Включение скрипта для подключения к базе данных
    require_once '../backend/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/styles.css">
    <script>
        function validatePhoneNumber() {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value.trim();
            // Регулярное выражение для проверки формата номера
            const phonePattern = /^\+7\d{10}$|^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$|^8\d{10}$/;

            if (!phonePattern.test(phoneValue)) {
                alert("Номер телефона должен быть в формате +79999999999, +7 (999) 999-99-99 или 89999999999.");
                phoneInput.focus();
                return false; // Остановить отправку формы
            }
            return true; // Разрешить отправку формы
        }
    </script>
</head>
<body>
    <div class="auth-container">
        <div id="main_block_page">
            <div class="page_block_m">
            <div class="auth-box">
                <h1>Регистрация</h1>
                    <form method="POST" action="register.php" onsubmit="return validatePhoneNumber();">
                        <div class="form-group">
                            <input type="text" id="phone" name="phone" placeholder="Номер телефона" class="reg_phone" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="Пароль" class="reg_pwd" required>
                        </div>
                        <button type="submit" class="auth-btn">Зарегистрироваться</button>
                    </form>
                </div>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $phone = $_POST['phone'];
                $password = $_POST['password'];
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Дополнительная проверка на серверной стороне
                $phonePattern = "/^\+7\d{10}$|^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$|^8\d{10}$/";
                if (!preg_match($phonePattern, $phone)) {
                    echo "Номер телефона должен быть в формате +79999999999, +7 (999) 999-99-99 или 89999999999.";
                } else {
                    if ($conn) {
                        $sql = "INSERT INTO users (phone, password) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ss", $phone, $hashed_password);

                        if ($stmt->execute()) {
                            header("Location: login.php");
                            exit();
                        } else {
                            echo "Ошибка: " . $stmt->error;
                        }

                        $stmt->close();
                    } else {
                        echo "Ошибка подключения к базе данных.";
                    }
                }
            }
            ?>
            </div>
        </div>
</body>
</html>
