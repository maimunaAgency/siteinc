<?php
require 'vendor/autoload.php'; // Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // Generate a random 6-digit OTP
    $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Save OTP to the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, otp) VALUES (:username, :email, :password_hash, :otp)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':otp', $otp);
    $stmt->execute();

    // Send OTP via email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Replace with your email
    $mail->Password = 'your-email-password'; // Replace with your email password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your-email@gmail.com', 'MRX Pay');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP for MRX Pay';
    $mail->Body = "Your OTP is: <b>$otp</b>";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'OTP sent successfully!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $e->getMessage()]);
}
?>