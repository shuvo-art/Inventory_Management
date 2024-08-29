<?php

include('database_connection.php');

$output = array();

// Start building the query
$query = "SELECT * FROM category ";

// Handling search functionality
if(isset($_POST["search"]["value"]))
{
    $search_value = mysqli_real_escape_string($connect, $_POST["search"]["value"]);
    $query .= 'WHERE category_name LIKE "%'.$search_value.'%" ';
    $query .= 'OR category_status LIKE "%'.$search_value.'%" ';
}

// Handling the order functionality
if(isset($_POST['order']))
{
    $column_order = $_POST['order']['0']['column'];
    $order_direction = $_POST['order']['0']['dir'];
    $query .= 'ORDER BY '.$column_order.' '.$order_direction.' ';
}
else
{
    $query .= 'ORDER BY category_id DESC ';
}

// Handling pagination
if($_POST['length'] != -1)
{
    $start = (int) $_POST['start'];
    $length = (int) $_POST['length'];
    $query .= 'LIMIT ?, ?';
}

$statement = $connect->prepare($query);

if($_POST['length'] != -1) {
    $statement->bind_param("ii", $start, $length);
}

$statement->execute();
$result = $statement->get_result();

$data = array();
$filtered_rows = mysqli_num_rows($result);

while($row = $result->fetch_assoc())
{
    $status = ($row['category_status'] == 'active') ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
    $sub_array = array();
    $sub_array[] = $row['category_id'];
    $sub_array[] = $row['category_name'];
    $sub_array[] = $status;
    $sub_array[] = '<button type="button" name="update" id="'.$row["category_id"].'" class="btn btn-warning btn-xs update">Update</button>';
    $sub_array[] = '<button type="button" name="delete" id="'.$row["category_id"].'" class="btn btn-danger btn-xs delete" data-status="'.$row["category_status"].'">Delete</button>';
    $data[] = $sub_array;
}

$output = array(
    "draw"            => intval($_POST["draw"]),
    "recordsTotal"    => $filtered_rows,
    "recordsFiltered" => get_total_all_records($connect),
    "data"            => $data
);

echo json_encode($output);

function get_total_all_records($connect)
{
    $query = "SELECT * FROM category";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->get_result();
    return mysqli_num_rows($result);
}

?>
