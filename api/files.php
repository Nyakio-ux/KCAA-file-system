<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/files.php';

// Initialize session
session_start();

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    error_log("No user ID in session");
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$fileActions = new FileActions();

try {
    // Initialize file data from POST
    $fileData = [
        'file_name' => $_POST['file_name'] ?? '',
        // 'original_name' => $_POST['original_name'] ?? '',
        'department' => $_POST['department'] ?? '',
        'reference_no' => $_POST['reference_no'] ?? '',
        'originator' => $_POST['originator'] ?? '',
        'receiver' => $_POST['receiver'] ?? '',
        'date_of_origination' => $_POST['date_of_origination'] ?? '',
        'destination' => $_POST['destination'] ?? '',
        'comments' => $_POST['comments'] ?? '',
        'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
        'is_physical' => empty($_FILES['file']['name'])
    ];

    $originalName = '';
    // Handle digital file upload
    if (!empty($_FILES['file']['name'])) {
        error_log("File upload detected: " . print_r($_FILES, true));

        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                error_log("Failed to create upload directory");
                throw new Exception("Failed to create upload directory");
            }
            chmod($uploadDir, 0777);
        }

        $originalName = $_FILES['file']['name'];
        $fileName = uniqid() . '_' . basename($originalName);
        $targetPath = $uploadDir . $fileName;

        error_log("Attempting to move file to: " . $targetPath);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            error_log("File moved successfully");

            $fileData['file_path'] = $targetPath;
            $fileData['file_size'] = $_FILES['file']['size'];
            $fileData['file_type'] = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileData['mime_type'] = $_FILES['file']['type'];

            // Set original_name and file_name if not given
            if (empty($fileData['original_name'])) {
                $fileData['original_name'] = $originalName;
            }
            if (empty($fileData['file_name'])) {
                $fileData['file_name'] = $originalName;
            }
        } else {
            error_log("Failed to move uploaded file. Upload error code: " . $_FILES['file']['error']);
            throw new Exception("Failed to move uploaded file");
        }
    }

    // Final validation for file_name
    if (empty($fileData['file_name'])) {
        if ($fileData['is_physical']) {
            throw new Exception("Required field 'file_name' is missing for physical file.");
        } else if (!empty($originalName)) {
            $fileData['file_name'] = $originalName;
        } else {
            throw new Exception("Required field 'file_name' is missing.");
        }
    }

    // Upload file logic
    error_log("Uploading file with data: " . print_r($fileData, true));
    $result = $fileActions->uploadFile($fileData, $userId);

    if ($result['success']) {
        http_response_code(201);
        error_log("File upload successful: " . print_r($result, true));
    } else {
        http_response_code(400);
        error_log("File upload failed: " . print_r($result, true));
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("File upload error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]));
}
?>
