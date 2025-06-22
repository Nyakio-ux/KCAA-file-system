<?php
require_once 'includes/auth.php';
require_once 'includes/Authmenus.php';

$auth = new Auth();
$authMenus = new AuthMenus();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$menus = $authMenus->getMenus($currentUser['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
