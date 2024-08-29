<?php
// Improved error handling and mysqli extension
$connect = mysqli_connect('localhost', 'root', '', 'id20433574_inventory');
if (!$connect) {
    die('Connection failed: ' . mysqli_connect_error());
}
mysqli_select_db($connect, 'id20433574_inventory') or die('Database selection failed: ' . mysqli_error($connect));

// Start the session
session_start();

// Register session variables using $_SESSION
$_SESSION['type'] = $_SESSION['type'] ?? null; 
$_SESSION['user_id'] = $_SESSION['user_id'] ?? null; 
?>
