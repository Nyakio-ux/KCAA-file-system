<?php
/**
 * Password Recovery Page - KCAA SmartFiles
 * 
 */

require_once 'components/KCAAuthComponents.php';

// Start session if needed
session_start();

// Handle password reset form submission
if ($_POST && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $success_message = "Password reset link has been sent to your email!";
}

// Render the password recovery page
KCAAuthComponents::renderHead(
    "KCAA SmartFiles - Password Recovery", 
    "Reset your KCAA account password securely."
);

KCAAuthComponents::renderAuthLayout([
    'icon' => 'key',
    'title' => 'Password Recovery',
    'subtitle' => 'KCAA SmartFiles',
    'description' => 'Reset your account password',
    'info_title' => 'Password Reset Instructions',
    'info_text' => 'Enter your email to receive a password reset link'
]);

// Show success message if form was submitted
if (isset($success_message)) {
    echo '<div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-400/30 text-green-200 text-sm">';
    echo '<div class="flex items-center">';
    echo '<i class="fas fa-check-circle mr-3 text-green-400"></i>';
    echo '<div>';
    echo '<p class="font-medium">Success!</p>';
    echo '<p class="text-xs mt-1 text-green-300">' . htmlspecialchars($success_message) . '</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

KCAAuthComponents::renderPasswordRecoveryForm($_SERVER['PHP_SELF']);
KCAAuthComponents::renderFooter();