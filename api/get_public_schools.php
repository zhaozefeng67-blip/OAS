<?php
require '../connect.php';

header('Content-Type: application/json');

// Query university information (public API, no login required)
$sql = "SELECT s.sid, s.school_name
        FROM school s
        ORDER BY s.school_name ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Query failed: ' . mysqli_error($conn)
    ]);
    exit();
}

$schools = [];

while ($row = mysqli_fetch_assoc($result)) {
    $schools[] = [
        'id' => (int)$row['sid'],
        'name' => $row['school_name']
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Success',
    'data' => $schools
]);
?>
