<?php
// diagnostic.php - Place this in your api folder temporarily
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$diagnostics = [];

// Check PHP version
$diagnostics['php_version'] = PHP_VERSION;

// Check session
session_start();
$diagnostics['session_status'] = session_status();
$diagnostics['session_id'] = session_id();
$diagnostics['user_id_in_session'] = $_SESSION['user_id'] ?? 'NOT_SET';

// Check file paths
$diagnostics['current_directory'] = __DIR__;
$diagnostics['config_file_exists'] = file_exists('../includes/config.php');
$diagnostics['files_class_exists'] = file_exists('../includes/files.php');

// Check uploads directory
$uploadDir = '../uploads/';
$diagnostics['uploads_dir_exists'] = is_dir($uploadDir);
$diagnostics['uploads_dir_writable'] = is_dir($uploadDir) ? is_writable($uploadDir) : false;

// Check request details
$diagnostics['request_method'] = $_SERVER['REQUEST_METHOD'];
$diagnostics['content_type'] = $_SERVER['CONTENT_TYPE'] ?? 'not_set';
$diagnostics['post_data'] = $_POST;
$diagnostics['files_data'] = $_FILES;

// Try to include config
try {
    if (file_exists('../includes/config.php')) {
        require_once '../includes/config.php';
        $diagnostics['config_loaded'] = true;
        
        // Check database connection if available
        if (isset($pdo) || class_exists('PDO')) {
            $diagnostics['pdo_available'] = true;
        }
    } else {
        $diagnostics['config_loaded'] = false;
    }
} catch (Exception $e) {
    $diagnostics['config_error'] = $e->getMessage();
}

// Try to include files class
try {
    if (file_exists('../includes/files.php')) {
        require_once '../includes/files.php';
        $diagnostics['files_class_loaded'] = true;
        $diagnostics['fileactions_exists'] = class_exists('FileActions');
    } else {
        $diagnostics['files_class_loaded'] = false;
    }
} catch (Exception $e) {
    $diagnostics['files_class_error'] = $e->getMessage();
}

// Check server permissions
$diagnostics['server_info'] = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit')
];

echo json_encode([
    'success' => true,
    'diagnostics' => $diagnostics
], JSON_PRETTY_PRINT);
?>