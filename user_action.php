<?php
include('database_connection.php');

if (isset($_POST['btn_action'])) {
    if ($_POST['btn_action'] == 'Add') {
        $query = "INSERT INTO user_details (user_email, user_password, user_name, user_type, user_status) VALUES (?, ?, ?, 'user', 'active')";
        $statement = $connect->prepare($query);
        $password_hash = password_hash($_POST["user_password"], PASSWORD_DEFAULT);
        $statement->bind_param("sss", $_POST["user_email"], $password_hash, $_POST["user_name"]);
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo 'New User Added';
        }
    }

    if ($_POST['btn_action'] == 'fetch_single') {
        $query = "SELECT * FROM user_details WHERE user_id = ?";
        $statement = $connect->prepare($query);
        $statement->bind_param("i", $_POST["user_id"]);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        echo json_encode($row);
    }

    if ($_POST['btn_action'] == 'Edit') {
        if ($_POST['user_password'] != '') {
            $query = "UPDATE user_details SET user_name = ?, user_email = ?, user_password = ? WHERE user_id = ?";
            $password_hash = password_hash($_POST["user_password"], PASSWORD_DEFAULT);
            $statement = $connect->prepare($query);
            $statement->bind_param("sssi", $_POST["user_name"], $_POST["user_email"], $password_hash, $_POST["user_id"]);
        } else {
            $query = "UPDATE user_details SET user_name = ?, user_email = ? WHERE user_id = ?";
            $statement = $connect->prepare($query);
            $statement->bind_param("ssi", $_POST["user_name"], $_POST["user_email"], $_POST["user_id"]);
        }
        
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo 'User Details Edited';
        }
    }

    if ($_POST['btn_action'] == 'delete') {
        $status = ($_POST['status'] == 'Active') ? 'Inactive' : 'Active';
        $query = "UPDATE user_details SET user_status = ? WHERE user_id = ?";
        $statement = $connect->prepare($query);
        $statement->bind_param("si", $status, $_POST["user_id"]);
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo "User Status changed to " . $status;
        }
    }
}
?>
