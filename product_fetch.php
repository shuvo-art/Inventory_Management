<?php

// product_fetch.php

include('database_connection.php');
include('function.php');

$output = array();
$query = "
    SELECT product.*, brand.brand_name, category.category_name, user_details.user_name FROM product 
    INNER JOIN brand ON brand.brand_id = product.brand_id
    INNER JOIN category ON category.category_id = product.category_id 
    INNER JOIN user_details ON user_details.user_id = product.product_enter_by 
";

// Preparing parameters for safe query execution
$param = array();
if (isset($_POST["search"]["value"])) {
    $searchValue = '%' . $_POST["search"]["value"] . '%';
    $query .= ' WHERE brand.brand_name LIKE ? OR category.category_name LIKE ? OR product.product_name LIKE ? OR product.product_quantity LIKE ? OR user_details.user_name LIKE ? OR product.product_id LIKE ?';
    $param = array_fill(0, 6, $searchValue); // Fill array with search value for each parameter
}

if (isset($_POST['order'])) {
    $orderColumn = array('product_id', 'category_name', 'brand_name', 'product_name', 'product_quantity', 'user_name', 'product_status');
    $query .= ' ORDER BY ' . $orderColumn[$_POST['order']['0']['column']] . ' ' . ($_POST['order']['0']['dir'] === 'asc' ? 'ASC' : 'DESC');
} else {
    $query .= ' ORDER BY product_id DESC';
}

if ($_POST['length'] != -1) {
    $query .= ' LIMIT ?, ?';
    array_push($param, intval($_POST['start']), intval($_POST['length']));
}

$stmt = $connect->prepare($query);
$stmt->bind_param(str_repeat('s', count($param)), ...$param);
$stmt->execute();
$result = $stmt->get_result();

$data = array();
$filtered_rows = mysqli_num_rows($result);

while ($row = $result->fetch_assoc()) {
    $status = $row['product_status'] == 'active' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
    $sub_array = array();
    $sub_array[] = $row['product_id'];
    $sub_array[] = $row['category_name'];
    $sub_array[] = $row['brand_name'];
    $sub_array[] = $row['product_name'];
    $sub_array[] = available_product_quantity($connect, $row["product_id"]) . ' ' . $row["product_unit"];
    $sub_array[] = $row['user_name'];
    $sub_array[] = $status;
    $sub_array[] = '<button type="button" name="view" id="' . $row["product_id"] . '" class="btn btn-info btn-xs view">View</button>';
    $sub_array[] = '<button type="button" name="update" id="' . $row["product_id"] . '" class="btn btn-warning btn-xs update">Update</button>';
    $sub_array[] = '<button type="button" name="delete" id="' . $row["product_id"] . '" class="btn btn-danger btn-xs delete" data-status="' . $row["product_status"] . '">Delete</button>';
    $data[] = $sub_array;
}

function get_total_all_records($connect)
{
    $query = 'SELECT COUNT(*) FROM product';
    $result = $connect->query($query);
    $row = $result->fetch_row();
    return $row[0];
}

$output = array(
    "draw"              => intval($_POST["draw"]),
    "recordsTotal"      => $filtered_rows,
    "recordsFiltered"   => get_total_all_records($connect),
    "data"              => $data
);

echo json_encode($output);
?>
