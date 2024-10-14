<?php
require_once('../models/User.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'register') {
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        if (User::register($phone, $password)) {
            echo json_encode(["status" => "success", "message" => "User registered successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Registration failed"]);
        }
    } elseif ($action === 'login') {
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $user = User::login($phone, $password);
        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(["status" => "success", "message" => "Login successful"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
        }
    }
}
