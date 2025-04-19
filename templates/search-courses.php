<?php
header('Content-Type: application/json');

// 1) Connect
$conn = mysqli_connect("localhost", "root", "", "mydatabase");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 2) Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
$q     = trim($input['q'] ?? '');
$max   = 20;

if ($q === '') {
    echo json_encode([]);
    exit;
}

// 3) Step 1: exact matches
$exactStmt = mysqli_prepare($conn,
    "SELECT id, title
       FROM courses
      WHERE title = ?
      LIMIT $max"
);
mysqli_stmt_bind_param($exactStmt, "ssi", $q, $q, $max);
mysqli_stmt_execute($exactStmt);
mysqli_stmt_bind_result($exactStmt, $id, $title, $desc);

$courses = [];
$ids      = [];
while (mysqli_stmt_fetch($exactStmt)) {
    $courses[] = ['id'=>$id,'title'=>$title];
    $ids[]      = $id;
}
mysqli_stmt_close($exactStmt);

// 4) Step 2: fill with next courses up to $max
$remaining = $max - count($courses);
if ($remaining > 0) {
    // Build IN clause of already‑seen IDs (if any)
    if (count($ids) > 0) {
        $in = implode(',', array_map('intval', $ids));
        $sql = "
          SELECT id, title
            FROM courses
           WHERE id NOT IN ($in)
           LIMIT $remaining
        ";
    } else {
        $sql = "
          SELECT id, title
            FROM courses
           LIMIT $remaining
        ";
    }

    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $courses[] = [
                'id'          => (int)$row['id'],
                'title'       => $row['title'],
            ];
        }
        mysqli_free_result($res);
    }
}

mysqli_close($conn);
echo json_encode($courses);
