<?php
header('Content-Type: application/json');
session_start();
require_once '../../Database/runQuery.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productID = $_POST['product_id'];

        //DELETE IMAGE FIRST IF IT EXISTS
        $oldImageSql = "SELECT image FROM products WHERE product_id = :product_id";
        $oldImageParams = [':product_id' => $productID];
        $oldImageResult = runQuery($pdo, $oldImageSql, $oldImageParams);
        $oldImage = $oldImageResult->fetch();

        if ($oldImage && !empty($oldImage['image'])) {
            $oldImagePath = '../../' . $oldImage['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        //SOFT DELETE PRODUCT
        $sql = "UPDATE products SET is_deleted = 1 WHERE product_id = :product_id";
        $params = [':product_id' => $productID];
        runQuery($pdo, $sql, $params);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}