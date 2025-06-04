<?php
/**
 * Login Page - KCAA SmartFiles
 * 
 */

require_once 'components/KCAAuthComponents.php';

session_start();


if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    

}

KCAAuthComponents::renderHead(
    "KCAA SmartFiles - Secure Login Portal", 
    "Access your KCAA SmartFiles account. Secure file management system for Kenya Civil Aviation Authority."
);

KCAAuthComponents::renderAuthLayout([
    'icon' => 'folder-open',
    'title' => 'KCAA SmartFiles',
    'subtitle' => 'File Management Portal',
    'description' => 'Kenya Civil Aviation Authority',
    'info_title' => 'Secure Access Required',
    'info_text' => 'Use your authorized KCAA credentials to access the system'
]);

KCAAuthComponents::renderLoginForm($_SERVER['PHP_SELF']);
KCAAuthComponents::renderFooter();