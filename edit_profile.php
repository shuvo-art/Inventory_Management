<?php
include('database_connection.php');

if(isset($_POST['user_name'])) {
    // Use prepared statements to prevent SQL Injection
    if($_POST["user_new_password"] != '') {
        $query = "UPDATE user_details SET 
                    user_name = ?, 
                    user_email = ?, 
                    user_password = ? 
                  WHERE user_id = ?";
        $password_hash = password_hash($_POST["user_new_password"], PASSWORD_DEFAULT);
        $params = [$_POST["user_name"], $_POST["user_email"], $password_hash, $_SESSION["user_id"]];
    } else {
        $query = "UPDATE user_details SET 
                    user_name = ?, 
                    user_email = ? 
                  WHERE user_id = ?";
        $params = [$_POST["user_name"], $_POST["user_email"], $_SESSION["user_id"]];
    }

    $statement = $connect->prepare($query);
    if ($statement === false) {
        die('MySQL prepare error: ' . $connect->error);
    }

    // Execute the prepared statement with bound parameters
    $result = $statement->bind_param(str_repeat('s', count($params)), ...$params);
    $statement->execute();

    if($statement->affected_rows > 0) {
        echo '<div class="alert alert-success">Profile Edited</div>';
    } else {
        // It is good to check if the update was successful or not
        echo '<div class="alert alert-info">No changes made or update failed.</div>';
    }
}
?>
