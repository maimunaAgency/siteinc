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

    // Fetch transaction data
    $stmt = $conn->prepare("SELECT id, amount, transaction_date FROM transactions WHERE user_id = :user_id ORDER BY transaction_date ASC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for Chart.js
    $labels = [];
    $data = [];
    foreach ($transactions as $transaction) {
        $labels[] = date('M j', strtotime($transaction['transaction_date'])); // Format date as "Jan 1"
        $data[] = $transaction['amount'];
    }

    // Fetch notifications
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - MRX Pay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f4f4f9;
      color: #333;
    }
    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #007BFF;
      color: #fff;
      padding: 10px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .header h1 {
      margin: 0;
    }
    .notification-icon {
      position: relative;
      cursor: pointer;
    }
    .notification-icon i {
      font-size: 1.5rem;
    }
    .notification-count {
      position: absolute;
      top: -10px;
      right: -10px;
      background: red;
      color: #fff;
      border-radius: 50%;
      padding: 5px 10px;
      font-size: 0.8rem;
    }
    .account-number-section {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 20px;
    }
    .account-number-section h2 {
      margin: 0;
      font-size: 1.5rem;
    }
    .balance-section {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 20px;
      position: relative;
    }
    .balance-section h2 {
      margin: 0;
      font-size: 2rem;
    }
    .balance-section p {
      font-size: 1.5rem;
      margin: 10px 0;
    }
    .balance-toggle {
      position: absolute;
      top: 20px;
      right: 20px;
      cursor: pointer;
    }
    .action-buttons {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }
    .action-button {
      background: #007BFF;
      color: #fff;
      border: none;
      padding: 20px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1.2rem;
      text-align: center;
    }
    .action-button:hover {
      background: #0056b3;
    }
    .profile-section {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
    }
    .profile-section h2 {
      margin-top: 0;
    }
    .profile-section label {
      display: block;
      margin-bottom: 10px;
    }
    .profile-section input {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .profile-section button {
      background: #007BFF;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }
    .chart-section {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
    }
    .chart-section h2 {
      margin-top: 0;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Header -->
    <div class="header">
      <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
      <div class="notification-icon" onclick="window.location.href='notifications.html'">
        <i class="fas fa-bell"></i>
        <span class="notification-count"><?php echo count($notifications); ?></span>
      </div>
    </div>

    <!-- Account Number -->
    <div class="account-number-section">
      <h2>Your Account Number</h2>
      <p><?php echo htmlspecialchars($user['account_number']); ?></p>
    </div>

    <!-- Balance Section -->
    <div class="balance-section">
      <h2>Your Balance</h2>
      <p id="balance">$<?php echo number_format($user['balance'], 2); ?></p>
      <i class="fas fa-eye balance-toggle" onclick="toggleBalance()"></i>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="action-button" onclick="window.location.href='transfer.html'">Transfer</button>
      <button class="action-button" onclick="window.location.href='deposit.html'">Deposit</button>
      <button class="action-button" onclick="window.location.href='withdraw.html'">Withdraw</button>
      <button class="action-button" onclick="window.location.href='earn.html'">Earn Money</button>
    </div>

    <!-- Profile Section -->
    <div class="profile-section" id="profile-section" style="display: none;">
      <h2>Update Profile</h2>
      <form id="profile-form" onsubmit="updateProfile(event)">
        <label for="full-name">Full Name</label>
        <input type="text" id="full-name" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="password">New Password</label>
        <input type="password" id="password" placeholder="Leave blank to keep current password">

        <button type="submit">Update Profile</button>
      </form>
    </div>

    <!-- Transaction Chart -->
    <div class="chart-section">
      <h2>Transaction History</h2>
      <canvas id="transactionChart"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Transaction Data
    const transactionData = {
      labels: <?php echo json_encode($labels); ?>,
      amounts: <?php echo json_encode($data); ?>
    };

    // Render Chart
    const ctx = document.getElementById('transactionChart').getContext('2d');
    const transactionChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: transactionData.labels,
        datasets: [{
          label: 'Transaction Amount',
          data: transactionData.amounts,
          borderColor: '#007BFF',
          backgroundColor: 'rgba(0, 123, 255, 0.1)',
          borderWidth: 2,
          pointRadius: 5,
          pointBackgroundColor: '#007BFF',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Amount ($)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Date'
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            enabled: true,
            mode: 'index',
            intersect: false,
            callbacks: {
              label: function(context) {
                const transaction = <?php echo json_encode($transactions); ?>[context.dataIndex];
                return `Transaction ID: ${transaction.id}, Amount: $${transaction.amount}, Date: ${transaction.transaction_date}`;
              }
            }
          }
        }
      }
    });

    // Toggle Balance Visibility
    let balanceVisible = true;
    function toggleBalance() {
      const balanceElement = document.getElementById('balance');
      const eyeIcon = document.querySelector('.balance-toggle');
      if (balanceVisible) {
        balanceElement.textContent = '******';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        balanceElement.textContent = `$${<?php echo number_format($user['balance'], 2); ?>}`;
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
      balanceVisible = !balanceVisible;
    }

    // Toggle Profile Section
    function toggleProfileSection() {
      const profileSection = document.getElementById('profile-section');
      profileSection.style.display = profileSection.style.display === 'none' ? 'block' : 'none';
    }

    // Update Profile
    function updateProfile(event) {
      event.preventDefault();
      const formData = new FormData(event.target);
      fetch('/update-profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        showPopup(data.success ? 'Success' : 'Error', data.message, data.success ? 'success' : 'error');
        if (data.success) {
          window.location.reload();
        }
      });
    }

    // Show SweetAlert2 Popup
    function showPopup(title, message, icon = 'success') {
      Swal.fire({
        title: title,
        text: message,
        icon: icon,
        confirmButtonText: 'OK'
      });
    }
  </script>
</body>
</html>