<?php
require 'config.php';
checkAdminAuth();

// Query university information
$sql = "SELECT s.sid, s.school_name, s.QS_rank, s.description, s.website,
               r.country, r.city, s.image
        FROM school s
        LEFT JOIN region r ON s.rid = r.rid
        ORDER BY s.QS_rank ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$universities = [];

while ($row = mysqli_fetch_assoc($result)) {
    $logo = null;
    if ($row['image']) {
        $logo = 'data:image/png;base64,' . base64_encode($row['image']);
    }
    
    $universities[] = [
        'id' => $row['sid'],
        'name' => $row['school_name'],
        'country' => $row['country'],
        'city' => $row['city'],
        'ranking' => $row['QS_rank'] ? (int)$row['QS_rank'] : 0,
        'description' => $row['description'],
        'logo' => $logo,
        'website' => $row['website'] ?: ''
    ];
}

jsonResponse(true, 'Success', $universities);
?>