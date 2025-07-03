<?php
require_once 'includes/auth.php';
require_once 'includes/files.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: files.php");
    exit();
}

if (empty($_POST['file_id']) || empty($_POST['department_id'])) {
    $_SESSION['error_message'] = "Missing required fields";
    header("Location: files.php");
    exit();
}

$fileId = (int)$_POST['file_id'];
$departmentId = (int)$_POST['department_id'];
$message = $_POST['message'] ?? '';

$fileActions = new FileActions();
$result = $fileActions->shareFile($fileId, $departmentId, $currentUser['user_id'], $message);

if ($result['success']) {
    $_SESSION['success_message'] = "File shared successfully";
} else {
    $_SESSION['error_message'] = $result['message'];
}

header("Location: view_file.php?id=" . $fileId);
exit();
?>