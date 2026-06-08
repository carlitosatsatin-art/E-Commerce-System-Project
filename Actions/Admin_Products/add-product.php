<?php
header('Content-Type: application/json');
session_start();
require_once '../../Database/runQuery.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $file = $_FILES['image'];
        $sql = "INSERT INTO products (product_name, description, category_id, price, stock) VALUES (:name, :desc, :cat, :price, :stock)";
        $params = [
            ':name' => $_POST['name'],
            ':desc' => $_POST['desc'],
            ':cat' => $_POST['category_id'],
            ':price' => $_POST['price'],
            ':stock' => $_POST['stock']
        ];
        runQuery($pdo, $sql, $params);

        $productID = $pdo->lastInsertId();

        //making image upload more secure by validating extension
        $allowedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowedExtensions)) {

            echo json_encode([
                'success' => false,
                'message' => 'Only JPG, PNG, and WEBP are allowed.'
            ]);
            exit;
        }

        $uploadDirectory = '../../uploads/product_pics/';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $fileName =
            'product_' .
            time() .
            '.' .
            $extension;

        $fullPath = $uploadDirectory . $fileName;

        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $fullPath
            )
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image.'
            ]);
            exit;
        }

        //relative path for db
        $dbPath = 'uploads/product_pics/' . $fileName;

        //update db
        $sql = "
        UPDATE products
        SET image = :image
        WHERE product_id = :product_id
";

        runQuery($pdo, $sql, [
            ':image' => $dbPath,
            ':product_id' => $productID
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Product image updated successfully.',
            'path' => $dbPath
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}