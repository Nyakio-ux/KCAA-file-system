<?php
header('Content-Type: application/json');

// Include database connection
require_once '../includes/Database.php';
require_once '../includes/FileActions.php';

// Initialize database connection
$db = new Database();
$conn = $db->connect();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Initialize FileActions
        $fileActions = new FileActions();
        
        // Get current user
        session_start();
        $uploadedBy = $_SESSION['user_id'] ?? null;
        
        if (!$uploadedBy) {
            throw new Exception('User not logged in');
        }

        // Prepare file data
        $fileData = [
            'file_name' => htmlspecialchars(trim($_POST['file_name'] ?? '')),
            'reference_no' => htmlspecialchars(trim($_POST['reference_no'] ?? '')),
            'file_path' => '', // Will be set after file upload
            'source_department_id' => intval($_POST['source_department_id'] ?? 0),
            'originator' => htmlspecialchars(trim($_POST['originator'] ?? '')),
            'destination_department_id' => intval($_POST['destination_department_id'] ?? 0),
            'receiver' => htmlspecialchars(trim($_POST['receiver'] ?? '')),
            'date_of_origination' => htmlspecialchars(trim($_POST['date_of_origination'] ?? '')),
            'comments' => htmlspecialchars(trim($_POST['comments'] ?? '')),
            'is_physical' => empty($_FILES['file']['name']),
            'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0
        ];

        // Validate required fields
        $required = ['file_name', 'reference_no', 'source_department_id', 'originator', 'destination_department_id', 'receiver', 'date_of_origination'];
        foreach ($required as $field) {
            if (empty($fileData[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Handle file upload if it's a digital file
        if (!$fileData['is_physical']) {
            $uploadDir = '../uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Check for file upload errors
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload failed with error code " . ($_FILES['file']['error'] ?? 'unknown'));
            }

            // Generate unique filename
            $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $uniqueFileName;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                throw new Exception('Failed to upload file');
            }

            // Update file path in data
            $fileData['file_path'] = $uploadFile;
        }

        // Upload file using FileActions
        $result = $fileActions->uploadFile($fileData, $uploadedBy);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'File uploaded successfully and registered in the system',
            'file_id' => $result['file_id']
        ]);

    } catch (Exception $e) {
        // Clean up uploaded file if it exists
        if (isset($uploadFile) && file_exists($uploadFile)) {
            unlink($uploadFile);
        }

        // Return error response
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>