<?php
require_once('../config/database.php');

class Transaction {
    public static function makeTransaction($from_user_id, $to_user_id, $amount) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO transactions (from_user_id, to_user_id, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $from_user_id, $to_user_id, $amount);
        return $stmt->execute();
    }
    
    public static function getTransactions($user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE from_user_id = ? OR to_user_id = ?");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
