<?php
session_start(); // Start the session
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'mrx_pay_db';
$username = 'mrx_pay_user';
$password = 'your_password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $fullName = $_POST['full-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Update profile
    $sql = "UPDATE users SET full_name = :full_name, email = :email";
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql .= ", password_hash = :password_hash";
    }
    $sql .= " WHERE id = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':email', $email);
    if (!empty($password)) {
        $stmt->bindParam(':password_hash', $password_hash);
    }
    $stmt->bindParam(':user_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>