<?php
header('Content-Type: application/json');

// Connect to MySQL (replace with your LAMPP details)
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get raw JSON data
$modules_json = file_get_contents('php://input');

// Prepare SQL to insert the set of modules
$stmt = mysqli_prepare($conn, "INSERT INTO posts (modules) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $modules_json);
$success = mysqli_stmt_execute($stmt);

if (!$success) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save modules']);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// Get the auto-incremented id
$set_id = mysqli_insert_id($conn);

// Clean up
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Return success response
echo json_encode(['message' => 'Saved successfully', 'id' => $set_id]);
?>