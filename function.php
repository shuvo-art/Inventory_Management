<?php
//function.php

function fill_category_list($connect)
{
    $query = "SELECT category_id, category_name FROM category WHERE category_status = 'active' ORDER BY category_name ASC";
    
    // Prepare the SQL statement to avoid SQL injection if parameters were used
    $statement = $connect->prepare($query);
    
    // Execute the statement
    if (!$statement->execute()) {
        // Handle SQL execution errors
        error_log("Error executing query: " . $statement->error);
        return '<option value="">Error loading categories</option>';
    }

    // Fetch all results
    $result = $statement->get_result();

    $output = '';
    // Check if we have rows returned
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= '<option value="'.htmlspecialchars($row["category_id"]).'">'.htmlspecialchars($row["category_name"]).'</option>';
        }
    } else {
        $output = '<option value="">No categories available</option>';
    }

    return $output;
}


function fill_brand_list($connect, $category_id)
{
    // Prepare the query using placeholders for safer parameter handling
    $query = "SELECT brand_id, brand_name FROM brand 
              WHERE brand_status = 'active' AND category_id = ?
              ORDER BY brand_name ASC";
    $stmt = $connect->prepare($query);
    
    // Check if the statement was prepared successfully
    if (false === $stmt) {
        error_log('MySQL prepare error: ' . $connect->error);
        return '<option value="">Error loading brands</option>';
    }

    // Bind the integer parameter for the category_id
    $stmt->bind_param("i", $category_id);

    // Execute the prepared statement
    if (!$stmt->execute()) {
        error_log('Execute error: ' . $stmt->error);
        $stmt->close();
        return '<option value="">Error loading brands</option>';
    }

    // Get the result set from the prepared statement
    $result = $stmt->get_result();

    // Initialize the output string with the default option
    $output = '<option value="">Select Brand</option>';

    // Fetch each row and append an option to the output
    while ($row = $result->fetch_assoc()) {
        $output .= '<option value="' . htmlspecialchars($row["brand_id"]) . '">' . htmlspecialchars($row["brand_name"]) . '</option>';
    }

    // Clean up: close the statement
    $stmt->close();

    return $output;
}


function get_user_name($connect, $user_id) {
    $query = "SELECT user_name FROM user_details WHERE user_id = ?";
    $statement = $connect->prepare($query);
    if ($statement) {
        $statement->bind_param("i", $user_id);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['user_name'];
        }
        $statement->close();
    }
    return null;  // Return null if user not found or in case of error
}


function fill_product_list($connect)
{
    // Prepare the query
    $query = "SELECT product_id, product_name FROM product WHERE product_status = 'active' ORDER BY product_name ASC";
    $stmt = $connect->prepare($query);
    
    // Check if the statement was prepared successfully
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $connect->error);
        return '<option value="">Error loading products</option>';
    }

    // Execute the prepared statement
    if (!$stmt->execute()) {
        error_log("Execute error: " . $stmt->error);
        $stmt->close();
        return '<option value="">Error loading products</option>';
    }

    // Get the result set from the prepared statement
    $result = $stmt->get_result();

    // Initialize output
    $output = '<option value="">Select Product</option>';

    // Fetch data from the result set
    while ($row = $result->fetch_assoc()) {
        $output .= '<option value="' . htmlspecialchars($row["product_id"]) . '">' . htmlspecialchars($row["product_name"]) . '</option>';
    }

    // Clean up: close the statement
    $stmt->close();

    return $output;
}


function fetch_product_details($product_id, $connect)
{
    // Initialize the output array to ensure it's defined even if no rows are returned
    $output = [];

    // Prepare the SQL query using placeholders for parameters
    $query = "SELECT * FROM product WHERE product_id = ?";
    $stmt = $connect->prepare($query);

    // Check if the statement prepared successfully
    if (!$stmt) {
        // Handle error (e.g., log and return or throw an exception)
        error_log('Error preparing statement: ' . $connect->error);
        return $output;  // Return an empty array if there's an error
    }

    // Bind the integer parameter for the product_id
    $stmt->bind_param("i", $product_id);

    // Execute the query
    if (!$stmt->execute()) {
        // Handle execution errors
        error_log('Error executing statement: ' . $stmt->error);
        return $output;  // Return an empty array if there's an execution error
    }

    // Get the result set from the prepared statement
    $result = $stmt->get_result();

    // Fetch the single row from the result set
    $row = $result->fetch_assoc();

    if ($row) {
        $output['product_name'] = $row["product_name"];
        $output['quantity'] = $row["product_quantity"];
        $output['price'] = $row['product_base_price'];
        $output['tax'] = $row['product_tax'];
    }

    // Close the statement
    $stmt->close();

    return $output;
}


function available_product_quantity($connect, $product_id) {
    // Fetch product details using a separate function
    $product_data = fetch_product_details($product_id, $connect);
    if (!$product_data) {
        return 'Product details not found.';
    }

    // Prepare SQL query using bound parameters to prevent SQL injection
    $query = "
        SELECT inventory_order_product.quantity 
        FROM inventory_order_product 
        INNER JOIN inventory_order ON inventory_order.inventory_order_id = inventory_order_product.inventory_order_id
        WHERE inventory_order_product.product_id = ? AND inventory_order.inventory_order_status = 'active'
    ";

    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['quantity'];
    }

    // Calculate available quantity
    $available_quantity = intval($product_data['quantity']) - $total;

    // If available quantity is zero, update product status to 'inactive'
    if ($available_quantity == 0) {
        $update_query = "UPDATE product SET product_status = 'inactive' WHERE product_id = ?";
        $update_stmt = $connect->prepare($update_query);
        $update_stmt->bind_param("i", $product_id);
        $update_stmt->execute();
    }

    return $available_quantity;
}

function count_total_user($connect)
{
    $query = "SELECT * FROM user_details WHERE user_status='active'";
    $statement = $connect->prepare($query);
    $statement->execute();
    $statement->store_result();  // Store the result to get properties like num_rows

    return $statement->num_rows;  // Returns the number of rows
}

function count_total_category($connect)
{
    $query = "SELECT * FROM category WHERE category_status='active'";
    $statement = $connect->prepare($query);
    $statement->execute();
    $statement->store_result();
    
    return $statement->num_rows;
}

function count_total_brand($connect)
{
    $query = "SELECT * FROM brand WHERE brand_status='active'";
    $statement = $connect->prepare($query);
    if (!$statement) {
        // Handle error, possibly returning or throwing an error
        die('Prepare failed: ' . $connect->error);
    }
    $statement->execute();
    $statement->store_result();  // Store the result to access num_rows

    return $statement->num_rows;  // Return the number of rows
}

function count_total_product($connect)
{
    $query = "SELECT * FROM product WHERE product_status='active'";
    $statement = $connect->prepare($query);
    if (!$statement) {
        // Handle error properly
        die('Prepare failed: ' . $connect->error);
    }
    $statement->execute();
    $statement->store_result();  // Store the result set from the query

    return $statement->num_rows;  // Return the number of rows in the result set
}

function count_total_order_value($connect)
{
    $query = "
    SELECT SUM(inventory_order_total) AS total_order_value 
    FROM inventory_order 
    WHERE inventory_order_status='active'";
    if ($_SESSION['type'] == 'user') {
        $query .= ' AND user_id = ?';
    }

    $statement = $connect->prepare($query);
    if ($_SESSION['type'] == 'user') {
        $statement->bind_param('i', $_SESSION["user_id"]); // Assuming user_id is an integer
    }
    $statement->execute();

    $result = $statement->get_result(); // Get the mysqli_result object
    $row = $result->fetch_assoc(); // Fetch data as associative array

    if ($row) {
        return number_format($row['total_order_value'], 2);
    }
    return '0.00'; // Return a default value if no rows are returned
}

function count_total_cash_order_value($connect)
{
    $query = "
    SELECT SUM(inventory_order_total) AS total_order_value 
    FROM inventory_order 
    WHERE payment_status = 'cash' AND inventory_order_status='active'";

    if ($_SESSION['type'] == 'user') {
        $query .= ' AND user_id = ?';
    }

    $statement = $connect->prepare($query);
    if ($statement === false) {
        // Proper error handling: output the error from MySQL
        die('Prepare failed: ' . $connect->error);
    }

    if ($_SESSION['type'] == 'user') {
        $statement->bind_param('i', $_SESSION["user_id"]); // Assuming user_id is an integer
    }

    $statement->execute();
    $result = $statement->get_result(); // Retrieves a mysqli_result object
    $row = $result->fetch_assoc(); // Fetch the results as an associative array

    if ($row && $row['total_order_value'] !== null) {
        return number_format($row['total_order_value'], 2);
    }
    return '0.00'; // Provide a default return value if no rows or null values
}

function count_total_credit_order_value($connect)
{
    $query = "
    SELECT SUM(inventory_order_total) AS total_order_value 
    FROM inventory_order 
    WHERE payment_status = 'credit' AND inventory_order_status='active'";

    if ($_SESSION['type'] == 'user') {
        $query .= ' AND user_id = ?';
    }

    $statement = $connect->prepare($query);
    if ($statement === false) {
        die('Prepare failed: ' . $connect->error);
    }

    if ($_SESSION['type'] == 'user') {
        $statement->bind_param('i', $_SESSION["user_id"]); // Bind the integer 'user_id' if needed
    }

    $statement->execute();
    $result = $statement->get_result(); // get_result() to get mysqli_result object
    $row = $result->fetch_assoc(); // fetch data as associative array

    if ($row && $row['total_order_value'] !== null) {
        return number_format($row['total_order_value'], 2);
    }
    return '0.00'; // Default return if no data found
}

function get_user_wise_total_order($connect)
{
    $query = "
    SELECT sum(inventory_order.inventory_order_total) as order_total, 
    SUM(CASE WHEN inventory_order.payment_status = 'cash' THEN inventory_order.inventory_order_total ELSE 0 END) AS cash_order_total, 
    SUM(CASE WHEN inventory_order.payment_status = 'credit' THEN inventory_order.inventory_order_total ELSE 0 END) AS credit_order_total, 
    user_details.user_name 
    FROM inventory_order 
    INNER JOIN user_details ON user_details.user_id = inventory_order.user_id 
    WHERE inventory_order.inventory_order_status = 'active' 
    GROUP BY inventory_order.user_id";

    $statement = $connect->prepare($query);
    if (!$statement) {
        die('Prepare failed: ' . $connect->error);
    }
    $statement->execute();
    $result = $statement->get_result(); // Get mysqli_result object

    $output = '
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <tr>
                <th>User Name</th>
                <th>Total Order Value</th>
                <th>Total Cash Order</th>
                <th>Total Credit Order</th>
            </tr>
    ';

    while ($row = $result->fetch_assoc()) {
        $output .= '
        <tr>
            <td>'.$row['user_name'].'</td>
            <td align="right">$ '.$row["order_total"].'</td>
            <td align="right">$ '.$row["cash_order_total"].'</td>
            <td align="right">$ '.$row["credit_order_total"].'</td>
        </tr>
        ';
    }
    $output .= '</table></div>';
    return $output;
}

?>