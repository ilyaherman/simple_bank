<?php
require_once('../config/database.php');

class User {
    public static function register($phone, $password) {
        global $pdo;
        $hash_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (phone, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $phone, $hash_password);
        return $stmt->execute();
    }

    public static function login($phone, $password) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            return password_verify($password, $user['password']) ? $user : false;
        }
        return false;
    }
}
