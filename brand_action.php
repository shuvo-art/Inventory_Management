<?php

include('database_connection.php');

if (isset($_POST['btn_action'])) {
    switch ($_POST['btn_action']) {
        case 'Add':
            $query = "INSERT INTO brand (category_id, brand_name) VALUES (?, ?)";
            $statement = $connect->prepare($query);
            $statement->bind_param("is", $_POST["category_id"], $_POST["brand_name"]);
            $statement->execute();
            echo $statement->affected_rows > 0 ? 'Brand Name Added' : 'Error adding brand';
            break;

        case 'fetch_single':
            $query = "SELECT * FROM brand WHERE brand_id = ?";
            $statement = $connect->prepare($query);
            $statement->bind_param("i", $_POST["brand_id"]);
            $statement->execute();
            $result = $statement->get_result();
            echo json_encode($result->fetch_assoc());
            break;

        case 'Edit':
            $query = "UPDATE brand SET category_id = ?, brand_name = ? WHERE brand_id = ?";
            $statement = $connect->prepare($query);
            $statement->bind_param("isi", $_POST["category_id"], $_POST["brand_name"], $_POST["brand_id"]);
            $statement->execute();
            echo $statement->affected_rows > 0 ? 'Brand Name Edited' : 'Error editing brand';
            break;

        case 'delete':
            $status = ($_POST['status'] == 'active') ? 'inactive' : 'active';
            $query = "UPDATE brand SET brand_status = ? WHERE brand_id = ?";
            $statement = $connect->prepare($query);
            $statement->bind_param("si", $status, $_POST["brand_id"]);
            $statement->execute();
            echo $statement->affected_rows > 0 ? "Brand status changed to $status" : 'Error changing status';
            break;

        default:
            echo 'Invalid Action';
    }
}

?>
