<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/files.php';

require_once __DIR__ . '/../helpers/notification.php';

header('Content-Type: application/json');

// Debug: Log the request method
error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
   header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Invalid method attempted: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Only POST accepted.']);
    exit;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    // Initialize FileActions
    $fileActions = new FileActions();
    
    // Prepare file data from form
    $fileData = [
        'file_name' => $_POST['file_name'] ?? '',
        'original_name' => empty($_FILES['file']['name']) ? 'Physical Document' : $_FILES['file']['name'],
        'reference_no' => $_POST['reference_no'] ?? '',
        'department' => $_POST['department'] ?? '',
        'originator' => $_POST['originator'] ?? '',
        'date_of_origination' => $_POST['date_of_origination'] ?? '',
        'destination' => $_POST['destination'] ?? '',
        'receiver' => $_POST['receiver'] ?? '',
        'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
        'comments' => $_POST['comments'] ?? '',
        'is_physical' => empty($_FILES['file']['name']) ? 1 : 0
    ];

    // Handle file upload if present
    if (!empty($_FILES['file']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileInfo = pathinfo($_FILES['file']['name']);
        $fileName = uniqid('file_', true) . '.' . $fileInfo['extension'];
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }

        $fileData['file_path'] = $fileName;
        $fileData['file_size'] = $_FILES['file']['size'];
        $fileData['file_type'] = $fileInfo['extension'];
        $fileData['mime_type'] = $_FILES['file']['type'];
    }

    // Call the uploadFile method
    $result = $fileActions->uploadFile($fileData, $_SESSION['user_id']);
    
    if ($result['success']) {
        $response = [
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_id' => $result['file_id']
        ];
    } else {
        $response = [
            'success' => false,
            'message' => $result['message']
        ];
    }

} catch (Exception $e) {
    error_log("File upload error: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);