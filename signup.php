<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'maimunag_mrx';
$username = 'maimunag_mrx1';
$password = 'mrx1314@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $email = $data['email'];
    $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
        exit;
    }

    // Generate a random 11-digit account number
    $account_number = str_pad(mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, account_number) VALUES (:username, :email, :password_hash, :account_number)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':account_number', $account_number);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sign up successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sign up failed.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>