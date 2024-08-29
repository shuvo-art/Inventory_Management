<?php

include('database_connection.php');
include('function.php');

if (isset($_POST['btn_action'])) {
    $output = [];
    switch ($_POST['btn_action']) {
        case 'load_brand':
            echo fill_brand_list($connect, $_POST['category_id']);
            break;

        case 'Add':
            $query = "INSERT INTO product (
                category_id, brand_id, product_name, product_description, product_quantity,
                product_unit, product_base_price, product_tax, product_enter_by, product_status, product_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";

            $stmt = $connect->prepare($query);
            $stmt->bind_param(
                "iissssidi",
                $_POST['category_id'], $_POST['brand_id'], $_POST['product_name'], $_POST['product_description'],
                $_POST['product_quantity'], $_POST['product_unit'], $_POST['product_base_price'],
                $_POST['product_tax'], $_SESSION["user_id"]
            );

            if ($stmt->execute()) {
                echo 'Product Added';
            } else {
                echo 'Failed to add product';
            }
            break;

        case 'product_details':
            $query = "SELECT p.*, c.category_name, b.brand_name, u.user_name FROM product p
                      INNER JOIN category c ON c.category_id = p.category_id
                      INNER JOIN brand b ON b.brand_id = p.brand_id
                      INNER JOIN user_details u ON u.user_id = p.product_enter_by
                      WHERE p.product_id = ?";

            $stmt = $connect->prepare($query);
            $stmt->bind_param("i", $_POST["product_id"]);
            $stmt->execute();
            $result = $stmt->get_result();

            $output = '<div class="table-responsive"><table class="table table-bordered">';
            while ($row = $result->fetch_assoc()) {
                $status = $row['product_status'] == 'active' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
                $output .= "<tr><td>Product Name</td><td>{$row["product_name"]}</td></tr>
                            <tr><td>Product Description</td><td>{$row["product_description"]}</td></tr>
                            <tr><td>Category</td><td>{$row["category_name"]}</td></tr>
                            <tr><td>Brand</td><td>{$row["brand_name"]}</td></tr>
                            <tr><td>Available Quantity</td><td>{$row["product_quantity"]} {$row["product_unit"]}</td></tr>
                            <tr><td>Base Price</td><td>{$row["product_base_price"]}</td></tr>
                            <tr><td>Tax (%)</td><td>{$row["product_tax"]}</td></tr>
                            <tr><td>Enter By</td><td>{$row["user_name"]}</td></tr>
                            <tr><td>Status</td><td>$status</td></tr>";
            }
            $output .= '</table></div>';
            echo $output;
            break;

        case 'fetch_single':
            $query = "SELECT * FROM product WHERE product_id = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("i", $_POST["product_id"]);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $row["brand_select_box"] = fill_brand_list($connect, $row["category_id"]);
            echo json_encode($row);
            break;

        case 'Edit':
            $query = "UPDATE product SET
                category_id = ?, brand_id = ?, product_name = ?, product_description = ?, product_quantity = ?,
                product_unit = ?, product_base_price = ?, product_tax = ?
                WHERE product_id = ?";

            $stmt = $connect->prepare($query);
            $stmt->bind_param(
                "iissssidi",
                $_POST['category_id'], $_POST['brand_id'], $_POST['product_name'],
                $_POST['product_description'], $_POST['product_quantity'], $_POST['product_unit'],
                $_POST['product_base_price'], $_POST['product_tax'], $_POST['product_id']
            );

            if ($stmt->execute()) {
                echo 'Product Details Edited';
            } else {
                echo 'Failed to edit product';
            }
            break;

        case 'delete':
            $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
            $query = "UPDATE product SET product_status = ? WHERE product_id = ?";
            $stmt = $connect->prepare($query);
            $stmt->bind_param("si", $status, $_POST["product_id"]);

            if ($stmt->execute()) {
                echo 'Product status changed to ' . $status;
            } else {
                echo 'Failed to change product status';
            }
            break;
    }
}

?>
