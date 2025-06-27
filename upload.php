<?php
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kcaa';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<p class='error'>Database connection failed: " . $conn->connect_error . "</p>");
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['size'] === 0) {
        echo "<p class='error'>Error: No file uploaded.</p>";
        exit;
    }

    // Check for upload errors
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo "<p class='error'>Error: File upload failed with error code " . $_FILES['file']['error'] . "</p>";
        exit;
    }

    // Generate unique filename to prevent conflicts
    $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $uniqueFileName;

    // Move uploaded file
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo "<p class='error'>Error: Failed to upload file.</p>";
        exit;
    }

    $fileName = htmlspecialchars(trim($_POST['file_name'] ?? ''));
    $referenceNo = htmlspecialchars(trim($_POST['reference_no'] ?? ''));
    $department = htmlspecialchars(trim($_POST['department'] ?? ''));
    $originator = htmlspecialchars(trim($_POST['originator'] ?? ''));
    $destination = htmlspecialchars(trim($_POST['destination'] ?? ''));
    $receiver = htmlspecialchars(trim($_POST['receiver'] ?? ''));
    $dateOfOrigination = htmlspecialchars(trim($_POST['date_of_origination'] ?? ''));
    $comments = htmlspecialchars(trim($_POST['comments'] ?? ''));
    $dateTime = date('Y-m-d H:i:s');

 
    if (empty($fileName) || empty($referenceNo) || empty($department) || 
        empty($originator) || empty($destination) || empty($receiver) || 
        empty($dateOfOrigination)) {
        // Delete uploaded file if validation fails
        unlink($uploadFile);
        echo "<p class='error'>Error: All fields are required.</p>";
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO files (file_name, reference_no, file_path, department, originator, destination, receiver, date_of_origination, comments, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        echo "<p class='error'>Error preparing statement: " . $conn->error . "</p>";
        exit;
    }

    $stmt->bind_param("ssssssssss", $fileName, $referenceNo, $uploadFile, $department, $originator, $destination, $receiver, $dateOfOrigination, $comments, $dateTime);

    if ($stmt->execute()) {
        echo "<p class='success'>File uploaded successfully!</p>";
    } else {
        unlink($uploadFile);
        echo "<p class='error'>Error saving file metadata: " . $stmt->error . "</p>";
    }

    $stmt->close();
} else {
    echo "<p class='error'>Invalid request method.</p>";
}

$conn->close();
?>