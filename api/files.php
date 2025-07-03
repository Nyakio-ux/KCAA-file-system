<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/files.php';
require_once '../includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$fileActions = new FileActions();

// Get form data
$fileData = [
    'file_name' => $_POST['file_name'] ?? '',
    'original_name' => !empty($_FILES['file']['name']) ? $_FILES['file']['name'] : $_POST['file_name'] ?? '',
    'source_department_id' => $_POST['department'] ?? '',
    'reference_number' => $_POST['reference_no'] ?? '',
    'originator' => $_POST['originator'] ?? '',
    'receiver' => $_POST['receiver'] ?? '',
    'date_of_origination' => $_POST['date_of_origination'] ?? '',
    'destination_department_id' => $_POST['destination'] ?? '',
    'destination_contact' => $_POST['receiver'] ?? '',
    'comments' => $_POST['comments'] ?? '',
    'is_confidential' => isset($_POST['is_confidential']),
    'is_physical' => empty($_FILES['file']['name'])
];

// Handle file upload if present
if (!empty($_FILES['file']['name'])) {
    $uploadDir = 'uploads/' . date('Y/m/d') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $fileData['file_path'] = $targetPath;
        $fileData['file_size'] = $_FILES['file']['size'];
        $fileData['file_type'] = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $fileData['mime_type'] = $_FILES['file']['type'];
    } else {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Failed to upload file']));
    }
}

// Process the file
$result = $fileActions->uploadFile($fileData, $auth->getUserId());

if ($result['success']) {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);