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
$dbname = 'maimunag_mrx';
$username = 'maimunag_mrx1';
$password = 'mrx1314@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $recipientAccountNumber = $data['accountNumber'];
    $amount = $data['amount'];

    // Check if the sender has sufficient balance
    $stmt = $conn->prepare("SELECT balance, username FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sender['balance'] < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
        exit;
    }

    // Check if the recipient account exists
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE account_number = :account_number");
    $stmt->bindParam(':account_number', $recipientAccountNumber);
    $stmt->execute();
    $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient) {
        echo json_encode(['success' => false, 'message' => 'Recipient account not found.']);
        exit;
    }

    // Deduct amount from sender
    $stmt = $conn->prepare("UPDATE users SET balance = balance - :amount WHERE id = :user_id");
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();

    // Add amount to recipient
    $stmt = $conn->prepare("UPDATE users SET balance = balance + :amount WHERE id = :recipient_id");
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':recipient_id', $recipient['id']);
    $stmt->execute();

    // Generate a unique transaction ID
    $transactionId = substr(md5(uniqid()), 0, 11); // 11-character alphanumeric ID

    // Record the transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, recipient_account_number, amount, transaction_type, transaction_id) VALUES (:user_id, :recipient_account_number, :amount, 'transfer', :transaction_id)");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':recipient_account_number', $recipientAccountNumber);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':transaction_id', $transactionId);
    $stmt->execute();

    // Return transaction details
    echo json_encode([
        'success' => true,
        'message' => 'Transfer successful!',
        'transaction' => [
            'id' => $transactionId,
            'sender' => $sender['username'],
            'receiver' => $recipient['username'],
            'amount' => $amount,
            'date' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>