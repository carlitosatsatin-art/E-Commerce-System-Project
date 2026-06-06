<!-- sanitize inputs

check if all are filled

check if password and confirm password matches, retain email and full 

then if all are ok, then proceed -->

<?php
session_start();

require_once("../../Database/db.php");

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $firstName = isset($_POST["first-name"])
            ? trim(filter_var($_POST["first-name"], FILTER_SANITIZE_SPECIAL_CHARS))
            : "";
        $lastName = isset($_POST["last-name"])
            ? trim(filter_var($_POST["last-name"], FILTER_SANITIZE_SPECIAL_CHARS))
            : "";
        $email = isset($_POST["email"])
            ? trim(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
            : "";
        $password = isset($_POST["password"])
            ? trim(filter_var($_POST["password"], FILTER_SANITIZE_SPECIAL_CHARS))
            : "";
        $cpassword = isset($_POST["cpassword"])
            ? trim(filter_var($_POST["cpassword"], FILTER_SANITIZE_SPECIAL_CHARS))
            : "";

        if (empty($firstName) && empty($lastName) && empty($email) && empty($password) && empty($cpassword)) {
            $_SESSION["registration"]["error"] = "All fields must be filled.";
            $_SESSION["reg"] = true;
            header("Location: ../login-register.php");
            exit;
        }

        if ($password != $cpassword) {
            $_SESSION["registration"]["error"] = "Passwords do not match.";
            $_SESSION["registration"]["firstName"] = $firstName;
            $_SESSION["registration"]["lastName"] = $lastName;
            $_SESSION["registration"]["email"] = $email;
            $_SESSION["reg"] = "true";
            header("Location: ../../User/sign-up.php");
            exit;
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W]).{8,}$/', $password)) {
            $_SESSION["registration"]["error"] = "Your password must be at least 8 characters and include uppercase letters, lowercase letters, a number, and a special character.";
            $_SESSION["registration"]["firstName"] = $firstName;
            $_SESSION["registration"]["lastName"] = $lastName;
            $_SESSION["registration"]["email"] = $email;
            $_SESSION["reg"] = "true";
            header("Location: ../../User/sign-up.php");
            exit;
        }

        // if success unset these variables (double method)
        unset($_SESSION["registration"]["error"], $_SESSION["registration"]["firstName"], $_SESSION["registration"]["lastName"], $_SESSION["registration"]["email"], $_SESSION["reg"]);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (:firstName, :lastName, :email, :hashedPassword)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            "firstName" => $firstName,
            "lastName" => $lastName,
            "email" => $email,
            "hashedPassword" => $hashedPassword
        ]);

        if ($result) {
            $_SESSION['successful_registration'] = true;
            header("Location: ../../User/log-in.php");
            exit;
        }
    }
} catch (PDOException $e) {
    $_SESSION["registration"]["error"] = "Your email is already registered";
    $_SESSION["reg"] = "true";
    header("Location: ../../User/sign-up.php");
    exit;
}








