<?php
require '../connect.php';

header('Content-Type: application/json');

$sid = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;

if ($sid <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid school ID'
    ]);
    exit();
}

// Query program information for specified school (public API, no login required)
$sql = "SELECT pid, pname
        FROM program
        WHERE sid = $sid
        ORDER BY pname ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Query failed: ' . mysqli_error($conn)
    ]);
    exit();
}

$programs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $programs[] = [
        'id' => (int)$row['pid'],
        'name' => $row['pname']
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Success',
    'data' => $programs
]);
?>
