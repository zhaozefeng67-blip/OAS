<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    jsonResponse(false, 'Invalid data or missing ID');
}

// Validate required fields
if (empty($data['name']) || empty($data['country'])) {
    jsonResponse(false, 'You MUST input name and country!');
}

$id = (int)$data['id'];

// 1. Process region information
$country = $conn->real_escape_string($data['country']);
$city = isset($data['city']) && $data['city'] ? $conn->real_escape_string($data['city']) : null;

// Check if region already exists
$region_sql = "SELECT rid FROM region WHERE country = '$country'";
if ($city) {
    $region_sql .= " AND city = '$city'";
} else {
    $region_sql .= " AND city IS NULL";
}

$region_result = mysqli_query($conn, $region_sql);

if (mysqli_num_rows($region_result) > 0) {
    $region_row = mysqli_fetch_assoc($region_result);
    $rid = $region_row['rid'];
} else {
    // Insert new region
    $insert_region_sql = "INSERT INTO region (country, city) VALUES ('$country', " . ($city ? "'$city'" : "NULL") . ")";
    if (!mysqli_query($conn, $insert_region_sql)) {
        jsonResponse(false, 'Failed Inserting Region: ' . mysqli_error($conn));
    }
    $rid = mysqli_insert_id($conn);
}

// 2. Update university information
$name = $conn->real_escape_string($data['name']);
$ranking = isset($data['ranking']) ? (int)$data['ranking'] : 0;
$description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
$website = isset($data['website']) ? $conn->real_escape_string($data['website']) : '';

// Process image (if it's base64)
$image_update = '';
if (!empty($data['logo'])) {
    // Remove base64 prefix
    $base64_string = $data['logo'];
    if (strpos($base64_string, 'base64,') !== false) {
        $base64_string = explode('base64,', $base64_string)[1];
    }
    $image = mysqli_real_escape_string($conn, base64_decode($base64_string));
    $image_update = ", image = '$image'";
}

$update_school_sql = "UPDATE school SET 
                      rid = $rid, 
                      school_name = '$name', 
                      QS_rank = $ranking, 
                      description = '$description',
                      website = '$website'
                      $image_update
                      WHERE sid = $id";

if (!mysqli_query($conn, $update_school_sql)) {
    jsonResponse(false, 'Failed Updating School: ' . mysqli_error($conn));
}

jsonResponse(true, 'Successfully updated!', ['id' => $id]);
?>

