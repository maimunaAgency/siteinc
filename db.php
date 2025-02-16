<?php
$host = 'localhost'; // Database host (usually 'localhost')
$dbname = 'maimunag_mrx'; // Database name
$username = 'maimunag_mrx1'; // Database username
$password = 'mrx1314@'; // Database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>