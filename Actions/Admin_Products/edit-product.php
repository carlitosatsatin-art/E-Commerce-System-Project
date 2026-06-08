<?php
header('Content-Type: application/json');
session_start();
require_once '../../Database/runQuery.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $file = $_FILES['image'];
        $sql = "UPDATE products SET product_name = :name, description = :desc, category_id = :cat, price = :price, stock = :stock WHERE product_id = :product_id";
        $params = [
            ':name' => $_POST['name'],
            ':desc' => $_POST['desc'],
            ':cat' => $_POST['category_id'],
            ':price' => $_POST['price'],
            ':stock' => $_POST['stock'],
            ':product_id' => $_POST['product_id']
        ];
        runQuery($pdo, $sql, $params);

        $productID = $_POST['product_id'];

        //DELETE OLD IMAGE FIRST IF IT EXISTS
        //if there is no file uploaded, meaning not edited
        if ($file['error'] === UPLOAD_ERR_OK) {
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
        }

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