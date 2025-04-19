<?php
header('Content-Type: application/json');

// 1) Connect to MySQL
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
mysqli_set_charset($conn, "utf8mb4");

// 2) Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
$q = trim($input['q'] ?? '');
$max = 20;

if ($q === '') {
    echo json_encode([]);
    exit;
}

// 3) Search with partial matches
$searchTerm = "%$q%";
$stmt = mysqli_prepare($conn,
    "SELECT id, title FROM courses WHERE title LIKE ? ORDER BY title LIMIT ?"
);
mysqli_stmt_bind_param($stmt, "si", $searchTerm, $max);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'Query execution failed: ' . mysqli_error($conn)]);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}
mysqli_stmt_bind_result($stmt, $id, $title);

$courses = [];
while (mysqli_stmt_fetch($stmt)) {
    $courses[] = ['id' => (int)$id, 'title' => $title];
}
mysqli_stmt_close($stmt);

// 4) Fill with additional courses if needed
$remaining = $max - count($courses);
if ($remaining > 0) {
    $ids = array_map(function($course) { return $course['id']; }, $courses);
    if (count($ids) > 0) {
        $in = implode(',', array_map('intval', $ids));
        $stmt = mysqli_prepare($conn,
            "SELECT id, title FROM courses WHERE id NOT IN ($in) ORDER BY title LIMIT ?"
        );
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT id, title FROM courses ORDER BY title LIMIT ?"
        );
    }
    mysqli_stmt_bind_param($stmt, "i", $remaining);
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(['error' => 'Fallback query failed: ' . mysqli_error($conn)]);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_bind_result($stmt, $id, $title);
    while (mysqli_stmt_fetch($stmt)) {
        $courses[] = ['id' => (int)$id, 'title' => $title];
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
echo json_encode($courses);