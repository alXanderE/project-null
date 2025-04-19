<?php
header('Content-Type: application/json');

// 1) Connect to MySQL
$conn = mysqli_connect("localhost","root","","mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error'=>'DB connection failed']);
    exit;
}

// 2) Get and validate id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Missing or invalid id']);
    exit;
}
$id = (int) $_GET['id'];

// 3) Fetch quizzes_json from courses table
$stmt = mysqli_prepare($conn,
    "SELECT lectures_json FROM courses WHERE title = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $lectures_json);
if (!mysqli_stmt_fetch($stmt)) {
    http_response_code(404);
    echo json_encode(['error'=>'Course not found']);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

// 4) Decode and repackage
$lectures = json_decode($lectures_json, true);
echo json_encode([
    'title'    => $title,
    'lectures' => $lectures
]);