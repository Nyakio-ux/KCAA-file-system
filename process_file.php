<?php
require_once 'includes/files.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to perform this action.";
    header("Location: login.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fileActions = new FileActions();
    $uploadedBy = $_SESSION['user_id'];
    
    try {
        // Handle file registration
        if ($_POST['action'] === 'register_file') {
            // Validate required fields
            $requiredFields = [
                'file_name', 'reference_no', 'department', 
                'originator', 'receiver', 'date_of_origination', 'destination'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception("Missing required fields: " . implode(', ', $missingFields));
            }
            
            // Prepare file data
            $fileData = [
                'file_name' => $_POST['file_name'],
                'original_name' => $_POST['file_name'], // Using same name for original
                'reference_no' => $_POST['reference_no'],
                'department' => $_POST['department'],
                'originator' => $_POST['originator'],
                'receiver' => $_POST['receiver'],
                'date_of_origination' => $_POST['date_of_origination'],
                'destination' => $_POST['destination'],
                'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
                'comments' => $_POST['comments'] ?? '',
                'is_physical' => empty($_FILES['file']['name']) ? 1 : 0
            ];
            
            // Handle file upload if present
            if (!empty($_FILES['file']['name'])) {
                $uploadDir = 'uploads/files/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
                $targetPath = $uploadDir . $fileName;
                
                // Validate file
                $allowedTypes = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel', 
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                
                $fileType = $_FILES['file']['type'];
                $fileSize = $_FILES['file']['size'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception("Invalid file type. Only PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX are allowed.");
                }
                
                if ($fileSize > 10485760) { // 10MB
                    throw new Exception("File size exceeds 10MB limit.");
                }
                
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    throw new Exception("Failed to upload file.");
                }
                
                $fileData['file_path'] = $targetPath;
                $fileData['file_size'] = $fileSize;
                $fileData['file_type'] = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $fileData['mime_type'] = $fileType;
            }
            
            // Register the file
            $result = $fileActions->uploadFile($fileData, $uploadedBy);
            
            if ($result['success']) {
                $_SESSION['success'] = "File registered successfully!";
                $_SESSION['new_file_id'] = $result['file_id'];
            } else {
                // If there was a file uploaded but registration failed, delete it
                if (!empty($fileData['file_path']) && file_exists($fileData['file_path'])) {
                    unlink($fileData['file_path']);
                }
                throw new Exception($result['message']);
            }
            
            // Redirect back to the files page
            header("Location: files.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: myfiles.php");
    exit;
}