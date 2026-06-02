<?php
session_start();
require_once '../db_config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    
    if (!empty($ids)) {
        // Prepare placeholders
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        
        $stmt = $conn->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => count($ids) . ' leads deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No leads selected.']);
    }
}
$conn->close();
?>
