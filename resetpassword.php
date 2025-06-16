<?php
/**
 * KCAA SmartFiles - Password Reset Page
 * Secure password reset interface for KCAA file management system
 */

require_once 'components/AuthHead.php';
require_once 'components/AuthLayout.php';
require_once 'components/FormComponents.php';
require_once 'components/PasswordComponents.php';
require_once 'components/AuthFooter.php';
require_once 'includes/auth.php';

session_start();

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        $error_message = "Passwords do not match";
    } else {
        $result = $auth->resetPassword($token, $email, $newPassword);
        
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

AuthHead::render("KCAA SmartFiles - Reset Password", "Create a new password for your KCAA account");

AuthLayout::render([
    'icon' => 'lock',
    'title' => 'KCAA SmartFiles',
    'subtitle' => 'Create New Password',
    'description' => 'Kenya Civil Aviation Authority',
    'info_title' => 'Password Reset',
    'info_text' => 'Choose a strong password to secure your account'
]);

// Handle success message
if (isset($success_message)): ?>
    <div class="mb-4 p-4 bg-green-500/10 border border-green-400/30 rounded-lg text-green-200 text-sm">
        <div class="flex items-start">
            <i class="fas fa-check-circle mr-3 text-green-400 mt-0.5"></i>
            <div>
                <div class="font-medium mb-1">Password Successfully Reset</div>
                <div><?php echo htmlspecialchars($success_message); ?></div>
            </div>
        </div>
    </div>
    
    <div class="mt-6 text-center">
        <?php
            FormComponents::renderLink([
                'href' => 'login.php',
                'text' => 'Continue to Login',
                'classes' => 'inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 no-underline'
            ]);
        ?>
    </div>
<?php else: ?>

<?php if (isset($error_message)): ?>
    <div class="mb-4 p-4 bg-red-500/10 border border-red-400/30 rounded-lg text-red-200 text-sm">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-red-400"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    </div>
<?php endif; ?>

<form id="passwordResetForm" action="" method="POST" class="space-y-6">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
    
    <?php
        FormComponents::renderInputField([
            'type' => 'password',
            'name' => 'new_password',
            'id' => 'new_password',
            'label' => 'New Password',
            'placeholder' => 'Enter your new password',
            'icon' => 'lock',
            'required' => true,
            'show_toggle' => true,
            'autocomplete' => 'new-password',
            'minlength' => 8
        ]);
        
        PasswordComponents::renderPasswordStrengthIndicator('new_password');
    ?>

    <?php
        FormComponents::renderInputField([
            'type' => 'password',
            'name' => 'confirm_password',
            'id' => 'confirm_password',
            'label' => 'Confirm New Password',
            'placeholder' => 'Confirm your new password',
            'icon' => 'lock',
            'required' => true,
            'show_toggle' => true,
            'autocomplete' => 'new-password',
            'minlength' => 8
        ]);
        
        PasswordComponents::renderPasswordMatchIndicator();
    ?>

    <?php
        PasswordComponents::renderPasswordRequirements();
    ?>
    
    <?php
        FormComponents::renderSubmitButton([
            'text' => 'Update Password',
            'icon' => 'check',
            'id' => 'resetPasswordBtn',
            'disabled' => true
        ]);
    ?>
</form>

<?php endif; ?>

<div class="mt-6 text-center">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-slate-800 text-gray-400">Need help?</span>
        </div>
    </div>
    
    <div class="mt-4 space-y-2">
        <?php
            FormComponents::renderLink([
                'href' => 'login.php',
                'text' => 'Back to Login',
                'classes' => 'inline-flex items-center text-orange-400 hover:text-orange-300 hover:underline transition-colors text-sm mr-4'
            ]);
            
            FormComponents::renderLink([
                'href' => 'forgotpassword.php',
                'text' => 'Request New Reset Link',
                'classes' => 'inline-flex items-center text-gray-400 hover:text-gray-300 hover:underline transition-colors text-sm'
            ]);
        ?>
    </div>
    
    <div class="mt-4 text-sm text-gray-400">
        For technical support, contact your system administrator
    </div>
</div>
<script src="assets/js/resetpassword.js"></script>

<?php
    AuthFooter::render();
?>