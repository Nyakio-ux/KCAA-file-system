<?php
/**
 * Password Reset Page - KCAA SmartFiles
 * 
 */

require_once 'components/KCAAuthComponents.php';
require_once 'includes/database.php';

session_start();

// Get token from URL parameter
$token = isset($_GET['token']) ? trim($_GET['token']) : '';


$valid_token = !empty($token); 

// Handle password reset form submission
if ($_POST && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'] ?? '';
    
    // Validate passwords match
    if ($new_password === $confirm_password) {
        
        $success_message = "Your password has been reset successfully!";
        $redirect_to_login = true;
    } else {
        $error_message = "Passwords do not match. Please try again.";
    }
}

// If token is invalid or missing, show error
if (!$valid_token) {
    $error_message = "Invalid or expired reset token. Please request a new password reset link.";
}

KCAAuthComponents::renderHead(
    "KCAA SmartFiles - Reset Password", 
    "Create a new password for your KCAA account."
);

KCAAuthComponents::renderAuthLayout([
    'icon' => 'lock',
    'title' => 'Reset Password',
    'subtitle' => 'KCAA SmartFiles',
    'description' => 'Create a new secure password',
    'info_title' => 'Password Reset',
    'info_text' => 'Enter your new password below'
]);
// Show success message if password was reset successfully
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
    
    // Show redirect message and auto-redirect 
    if (isset($redirect_to_login)) {
        echo '<div class="mb-6 p-4 rounded-lg bg-blue-500/10 border border-blue-400/30 text-blue-200 text-sm">';
        echo '<div class="flex items-center">';
        echo '<i class="fas fa-info-circle mr-3 text-blue-400"></i>';
        echo '<div>';
        echo '<p class="font-medium">Redirecting to login...</p>';
        echo '<p class="text-xs mt-1 text-blue-300">You will be redirected to the login page in <span id="countdown">4</span> seconds.</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<script>';
        echo 'let countdown = 4;';
        echo 'const countdownElement = document.getElementById("countdown");';
        echo 'const timer = setInterval(() => {';
        echo '    countdown--;';
        echo '    countdownElement.textContent = countdown;';
        echo '    if (countdown <= 0) {';
        echo '        clearInterval(timer);';
        echo '        window.location.href = "login";';
        echo '    }';
        echo '}, 1000);';
        echo '</script>';
    }
}

// Show error message if there's an error
if (isset($error_message)) {
    echo '<div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-400/30 text-red-200 text-sm">';
    echo '<div class="flex items-center">';
    echo '<i class="fas fa-exclamation-triangle mr-3 text-red-400"></i>';
    echo '<div>';
    echo '<p class="font-medium">Error!</p>';
    echo '<p class="text-xs mt-1 text-red-300">' . htmlspecialchars($error_message) . '</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// Only show the form if token is valid and password hasn't been reset yet if not do not show the form inputs
if ($valid_token && !isset($success_message)) {
    KCAAuthComponents::renderPasswordResetForm($_SERVER['PHP_SELF'], 'POST', $token);
} else if (!$valid_token) {
    // Show option to request new reset link
    echo '<div class="text-center space-y-4">';
    echo '<p class="text-gray-300 text-sm">Need a new reset link?</p>';
    echo '<a href="forgotpassword" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50">';
    echo '<i class="fas fa-envelope mr-2"></i>';
    echo 'Request New Reset Link';
    echo '</a>';
    echo '</div>';
}
KCAAuthComponents::renderFooter();