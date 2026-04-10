<?php
require 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Get search term and page number from GET parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 8; // Number of items per page
    $offset = ($page - 1) * $itemsPerPage;

    // Prepare the query for total count
    if (!empty($search)) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name LIKE :search OR description LIKE :search");
        $searchParam = "%" . $search . "%";
        $countStmt->execute(['search' => $searchParam]);
        $totalItems = $countStmt->fetchColumn();

        // Prepare the query for paginated results
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :search OR description LIKE :search LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $totalItems = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        // Prepare the query for paginated results
        $stmt = $pdo->prepare("SELECT * FROM products LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Calculate total pages
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Fetch all results
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'data' => $products,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage
        ]
    ]);
} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Handle other errors
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
