<?php
header('Content-Type: application/json');

// 1) Connect to MySQL
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 2) Read incoming JSON body
$input = json_decode(file_get_contents('php://input'), true);
$q = trim($input['q'] ?? '');

if ($q === '') {
    // No query → return empty array
    echo json_encode([]);
    exit;
}

// 3) Prepare and run a LIKE query
$sql  = "SELECT id, title, description
         FROM courses
         WHERE title LIKE ? OR description LIKE ?
         LIMIT 20";
$stmt = mysqli_prepare($conn, $sql);
$like = "%{$q}%";
mysqli_stmt_bind_param($stmt, "ss", $like, $like);
$ok = mysqli_stmt_execute($stmt);

if (! $ok) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_result($stmt, $id, $title, $description);

// 4) Collect results
$courses = [];
while (mysqli_stmt_fetch($stmt)) {
    $courses[] = [
        'id'          => $id,
        'title'       => $title,
        'description' => $description
    ];
}

// 5) Cleanup and output
mysqli_stmt_close($stmt);
mysqli_close($conn);
echo json_encode($courses);
