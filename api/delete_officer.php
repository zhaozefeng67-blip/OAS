<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['universityId'])) {
    jsonResponse(false, 'Invalid data or missing ID/universityId');
}

$id = (int)$data['id'];
$sid = (int)$data['universityId'];

// Delete association between officer and school
$delete_sql = "DELETE FROM operator_school WHERE ID = $id AND sid = $sid";

if (!mysqli_query($conn, $delete_sql)) {
    jsonResponse(false, 'Failed Deleting Officer: ' . mysqli_error($conn));
}

jsonResponse(true, 'Successfully deleted!');
?>

