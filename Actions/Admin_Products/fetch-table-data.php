<?php
header('Content-Type: application/json');
session_start();
require_once '../../Database/runQuery.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $products = runQuery($pdo, "SELECT p.product_id, p.product_name, p.description, c.category_name AS category, c.category_id, p.price, p.stock, p.image FROM products p JOIN categories c ON p.category_id = c.category_id WHERE is_deleted = 0 ORDER BY p.created_at DESC", [], true);
        echo json_encode(['success' => true, 'data' => $products]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}