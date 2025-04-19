<?php
header('Content-Type: application/json');

// 1) Connect to MySQL
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 2) Read raw JSON payload
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// 3) Validate required fields
if (!isset($data['title']) || !isset($data['quizzes'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing title or quizzes']);
    exit;
}

$title       = $data['title'];
$quizzes_json = json_encode($data['quizzes']); // turn quizzes array back to JSON string

// 4) Insert into `courses` table
//    Make sure you have:
//      CREATE TABLE courses (
//        id INT AUTO_INCREMENT PRIMARY KEY,
//        title VARCHAR(255),
//        quizzes_json LONGTEXT
//      );
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO courses (title, quizzes_json) VALUES (?, ?)"
);
mysqli_stmt_bind_param($stmt, "ss", $title, $quizzes_json);
$success = mysqli_stmt_execute($stmt);

if (!$success) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save course']);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// 5) Return the new course ID
$course_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode([
    'message'   => 'Course saved successfully',
    'course_id' => $course_id
]);
