<?php

include('database_connection.php');

if (isset($_POST['btn_action'])) {
    if ($_POST['btn_action'] == 'Add') {
        $query = "INSERT INTO category (category_name) VALUES (?)";
        $statement = $connect->prepare($query);
        $statement->bind_param("s", $_POST["category_name"]);
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo 'Category Name Added';
        }
    }

    if ($_POST['btn_action'] == 'fetch_single') {
        $query = "SELECT * FROM category WHERE category_id = ?";
        $statement = $connect->prepare($query);
        $statement->bind_param("i", $_POST["category_id"]);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();

        echo json_encode($row);
    }

    if ($_POST['btn_action'] == 'Edit') {
        $query = "UPDATE category SET category_name = ? WHERE category_id = ?";
        $statement = $connect->prepare($query);
        $statement->bind_param("si", $_POST["category_name"], $_POST["category_id"]);
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo 'Category Name Edited';
        }
    }

    if ($_POST['btn_action'] == 'delete') {
        $status = ($_POST['status'] == 'active') ? 'inactive' : 'active';
        $query = "UPDATE category SET category_status = ? WHERE category_id = ?";
        $statement = $connect->prepare($query);
        $statement->bind_param("si", $status, $_POST["category_id"]);
        $statement->execute();

        if ($statement->affected_rows > 0) {
            echo 'Category status changed to ' . $status;
        }
    }
}

?>
