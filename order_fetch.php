<?php

include('database_connection.php');
include('function.php');

function get_total_all_records($connect) {
    $query = "SELECT COUNT(*) FROM inventory_order";
    $stmt = $connect->prepare($query);
    if (!$stmt) {
        die('MySQL prepare error: ' . $connect->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    return $row[0];
}

function buildRowArray($connect, $row) {
    $userName = ($_SESSION['type'] == 'master') ? get_user_name($connect, $row['user_id']) : '';
    return [
        $row['inventory_order_id'],
        $row['inventory_order_name'],
        $row['inventory_order_total'],
        $row['payment_status'] == 'cash' ? '<span class="label label-primary">Cash</span>' : '<span class="label label-warning">Credit</span>',
        $row['inventory_order_status'] == 'active' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>',
        $row['inventory_order_date'],
        $userName,
        '<a href="view_order.php?pdf=1&order_id=' . $row["inventory_order_id"] . '" class="btn btn-info btn-xs">View PDF</a>',
        '<button type="button" name="update" id="' . $row["inventory_order_id"] . '" class="btn btn-warning btn-xs update">Update</button>',
        '<button type="button" name="delete" id="' . $row["inventory_order_id"] . '" class="btn btn-danger btn-xs delete" data-status="' . $row["inventory_order_status"] . '">Delete</button>'
    ];
}

$output = [];
$query = "SELECT * FROM inventory_order";
$whereConditions = [];
$params = [];
$types = '';

if ($_SESSION['type'] == 'user') {
    $whereConditions[] = 'user_id = ?';
    $params[] = $_SESSION["user_id"];
    $types .= 's';
}

if (isset($_POST["search"]["value"])) {
    $search = '%' . $_POST["search"]["value"] . '%';
    $whereConditions[] = '(inventory_order_id LIKE ? OR
                           inventory_order_name LIKE ? OR
                           inventory_order_total LIKE ? OR
                           inventory_order_status LIKE ? OR
                           inventory_order_date LIKE ?)';
    $params = array_merge($params, array_fill(0, 5, $search));
    $types .= str_repeat('s', 5);
}

if (!empty($whereConditions)) {
    $query .= ' WHERE ' . implode(' AND ', $whereConditions);
}

$orderColumn = isset($_POST['order']) ? intval($_POST['order']['0']['column']) : 'inventory_order_id';
$orderDir = isset($_POST['order']) && $_POST['order']['0']['dir'] === 'asc' ? 'ASC' : 'DESC';
$query .= " ORDER BY $orderColumn $orderDir";

if (isset($_POST["length"]) && $_POST["length"] != -1) {
    $query .= ' LIMIT ?, ?';
    $params[] = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $params[] = intval($_POST['length']);
    $types .= 'ii';
}

$statement = $connect->prepare($query);
if (!$statement) {
    die('MySQL prepare error: ' . $connect->error);
}
if (!empty($params)) {
    $statement->bind_param($types, ...$params);
}
$statement->execute();
$result = $statement->get_result();
$data = [];
$filtered_rows = $result->num_rows;

while ($row = $result->fetch_assoc()) {
    $sub_array = buildRowArray($connect, $row);
    $data[] = $sub_array;
}

$totalRecords = get_total_all_records($connect);

$output = [
    "draw" => isset($_POST["draw"]) ? intval($_POST["draw"]) : 0,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

echo json_encode($output);

?>
