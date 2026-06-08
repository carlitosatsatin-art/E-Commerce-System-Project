<?php
header('Content-Type: application/json');
session_start();
require_once '../../Database/runQuery.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $user_id = $_SESSION['user_id'];

        //Check if stock is sufficient
        $sql = "SELECT stock FROM products WHERE product_id = ?";
        $product = runQuery($pdo, $sql, [$product_id])->fetch();
        if (!$product || $product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'error' => 'Insufficient stock available.']);
            exit;
        }

        // Check if product is already in cart
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
        $existingItem = runQuery($pdo, $sql, [$user_id, $product_id])->fetch();

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
            runQuery($pdo, $sql, [$newQuantity, $existingItem['cart_id']]);
        } else {
            // Add new item to cart
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            runQuery($pdo, $sql, [$user_id, $product_id, $quantity]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>