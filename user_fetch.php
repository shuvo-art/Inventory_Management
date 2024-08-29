<?php
include('database_connection.php');

$query = '';
$output = array();

// Basic query
$query .= "SELECT * FROM user_details WHERE user_type = 'user' ";

// Search functionality
if(isset($_POST["search"]["value"]))
{
    $search_value = mysqli_real_escape_string($connect, $_POST["search"]["value"]);
    $query .= 'AND (user_email LIKE "%'.$search_value.'%" ';
    $query .= 'OR user_name LIKE "%'.$search_value.'%" ';
    $query .= 'OR user_status LIKE "%'.$search_value.'%") ';
}

// Order functionality
if(isset($_POST["order"]))
{
    $column_order = $_POST['order']['0']['column']; // Column index
    $direction = $_POST['order']['0']['dir']; // asc or desc
    $query .= "ORDER BY ${column_order} ${direction} ";
}
else
{
    $query .= 'ORDER BY user_id DESC ';
}

// Pagination
if($_POST["length"] != -1)
{
    $start = $_POST['start'];
    $length = $_POST['length'];
    $query .= "LIMIT ${start}, ${length}";
}

// Fetch data
$statement = $connect->prepare($query);
$statement->execute();
$result = $statement->get_result(); // Get results

$data = array();
$filtered_rows = mysqli_num_rows($result);

while($row = $result->fetch_assoc())
{
    $status = $row["user_status"] == 'Active' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
    $sub_array = array();
    $sub_array[] = $row['user_id'];
    $sub_array[] = $row['user_email'];
    $sub_array[] = $row['user_name'];
    $sub_array[] = $status;
    $sub_array[] = '<button type="button" name="update" id="'.$row["user_id"].'" class="btn btn-warning btn-xs update">Update</button>';
    $sub_array[] = '<button type="button" name="delete" id="'.$row["user_id"].'" class="btn btn-danger btn-xs delete" data-status="'.$row["user_status"].'">Delete</button>';
    $data[] = $sub_array;
}

$output = array(
    "draw"                => intval($_POST["draw"]),
    "recordsTotal"        => $filtered_rows,
    "recordsFiltered"     => get_total_all_records($connect),
    "data"                => $data
);

echo json_encode($output);

function get_total_all_records($connect)
{
    $query = "SELECT * FROM user_details WHERE user_type = 'user'";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->get_result();
    return mysqli_num_rows($result);
}
?>
