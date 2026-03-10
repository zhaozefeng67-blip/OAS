<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    jsonResponse(false, 'Invalid data or missing ID');
}

$id = (int)$data['id'];

// Delete university (due to foreign key constraints, related programs and operator_programs will also be deleted)
$delete_sql = "DELETE FROM school WHERE sid = $id";

if (!mysqli_query($conn, $delete_sql)) {
    jsonResponse(false, 'Failed Deleting School: ' . mysqli_error($conn));
}

jsonResponse(true, 'Successfully deleted!');
?>

