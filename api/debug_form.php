<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'php_version' => PHP_VERSION,
    'session_status' => session_status(),
    'session_data' => $_SESSION ?? 'No session data',
    'post_data' => $_POST,
    'files_data' => $_FILES,
    'server_info' => [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'request_uri' => $_SERVER['REQUEST_URI']
    ],
    'file_checks' => [
        'config_exists' => file_exists('../includes/config.php'),
        'files_exists' => file_exists('../includes/files.php'),
        'upload_dir_exists' => is_dir('../uploads/'),
        'upload_dir_writable' => is_writable('../uploads/'),
    ],
    'php_settings' => [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'file_uploads' => ini_get('file_uploads') ? 'enabled' : 'disabled'
    ]
]);
?>