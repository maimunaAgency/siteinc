<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html'); // Redirect to login page if not logged in
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

    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - MRX Pay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f9;
      color: #333;
    }
    .profile-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .profile-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #007BFF;
      color: #fff;
      padding: 10px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .profile-header h1 {
      margin: 0;
    }
    .profile-content {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
    }
    .profile-picture {
      text-align: center;
      margin-bottom: 20px;
    }
    .profile-picture img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
    }
    .profile-details {
      text-align: center;
    }
    .profile-details p {
      margin: 10px 0;
    }
  </style>
</head>
<body>
  <div class="profile-container">
    <!-- Header -->
    <div class="profile-header">
      <h1>Profile</h1>
    </div>

    <!-- Profile Picture -->
    <div class="profile-content">
      <div class="profile-picture">
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
      </div>

      <!-- Profile Details -->
      <div class="profile-details">
        <p><strong>ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Account Number:</strong> <?php echo htmlspecialchars($user['account_number']); ?></p>
      </div>
    </div>
  </div>
</body>
</html>