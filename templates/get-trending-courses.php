<?php
header('Content-Type: application/json');

// 1) Connect to MySQL (adjust credentials & DB name as needed)
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 2) Fetch top 5 courses (id + title)
$sql = "SELECT id, title
        FROM courses
        ORDER BY id DESC
        LIMIT 5";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit;
}

// 3) Build the JSON array, making sure to cast id to an integer
$courses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = [
        'id'    => (int)$row['id'],
        'title' => $row['title']
    ];
}

// 4) Clean up and return
mysqli_free_result($result);
mysqli_close($conn);

echo json_encode($courses);
