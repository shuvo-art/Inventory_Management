<?php

// brand_fetch.php

include('database_connection.php');

// Prepare output array
$output = array();

// Start the query
$query = "
SELECT brand.*, category.category_name FROM brand 
INNER JOIN category ON category.category_id = brand.category_id 
";

// Prepare to bind parameters for the WHERE clause
$parameters = [];

// Handling the search functionality
if (isset($_POST["search"]["value"])) {
    $searchValue = "%{$_POST["search"]["value"]}%";
    $query .= 'WHERE brand.brand_name LIKE ? OR category.category_name LIKE ? OR brand.brand_status LIKE ? ';
    array_push($parameters, $searchValue, $searchValue, $searchValue);
}

// Preparing the ORDER BY clause dynamically
if (isset($_POST["order"])) {
    $columnIndex = $_POST['order']['0']['column'];
    $orderDirection = strtoupper($_POST['order']['0']['dir']) === 'ASC' ? 'ASC' : 'DESC';
    $columnNames = ['brand.brand_id', 'category.category_name', 'brand.brand_name', 'brand.brand_status']; // Correspond to DataTables columns
    $query .= 'ORDER BY ' . $columnNames[$columnIndex] . ' ' . $orderDirection;
} else {
    $query .= 'ORDER BY brand.brand_id DESC';
}

// Adding pagination if required
if (isset($_POST["length"]) && $_POST["length"] != -1) {
    $query .= ' LIMIT ?, ?';
    array_push($parameters, intval($_POST['start']), intval($_POST['length']));
}

// Prepare and execute the statement
$stmt = $connect->prepare($query);

// Dynamic binding of parameters
if (!empty($parameters)) {
    $types = str_repeat('s', count($parameters) - 2); // All parameters are strings except the last two, which are integers
    if ($_POST["length"] != -1) {
        $types .= 'ii'; // Add integer types for LIMIT parameters
    }
    $stmt->bind_param($types, ...$parameters);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
$filtered_rows = $stmt->affected_rows;

// Fetch data
while ($row = $result->fetch_assoc()) {
    $status = $row['brand_status'] === 'active' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
    $sub_array = [
        $row['brand_id'],
        $row['category_name'],
        $row['brand_name'],
        $status,
        '<button type="button" name="update" id="' . $row["brand_id"] . '" class="btn btn-warning btn-xs update">Update</button>',
        '<button type="button" name="delete" id="' . $row["brand_id"] . '" class="btn btn-danger btn-xs delete" data-status="' . $row["brand_status"] . '">Delete</button>'
    ];
    $data[] = $sub_array;
}

// Function to get total number of records
function get_total_all_records($connect) {
    $stmt = $connect->prepare('SELECT COUNT(*) FROM brand');
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return $row[0];
}

// Preparing output
$output = [
    "draw" => intval($_POST["draw"]),
    "recordsTotal" => get_total_all_records($connect),
    "recordsFiltered" => $filtered_rows,
    "data" => $data
];

echo json_encode($output);

?>
