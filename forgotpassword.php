<?php
/**
 * KCAA SmartFiles - Forgot Password Page
 * Password reset interface for KCAA file management system
 */

require_once 'components/AuthHead.php';
require_once 'components/AuthLayout.php';
require_once 'components/FormComponents.php';
require_once 'components/AuthFooter.php';
require_once 'includes/auth.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    
    $usernameOrEmail = $_POST['username_or_email'] ?? '';
    
    $result = $auth->forgotPassword($usernameOrEmail);
    
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}


AuthHead::render("KCAA SmartFiles - Forgot Password", "Reset your KCAA account password");

AuthLayout::render([
    'icon' => 'key',
    'title' => 'KCAA SmartFiles',
    'subtitle' => 'Password Recovery',
    'description' => 'Kenya Civil Aviation Authority',
    'info_title' => 'Reset Your Password',
    'info_text' => 'Enter your username or email to receive password reset instructions'
]);

if (isset($success_message)): ?>
    <div class="mb-4 p-4 bg-green-500/10 border border-green-400/30 rounded-lg text-green-200 text-sm">
        <div class="flex items-start">
            <i class="fas fa-check-circle mr-3 text-green-400 mt-0.5"></i>
            <div>
                <div class="font-medium mb-1">Password Reset Instructions Sent</div>
                <div><?php echo htmlspecialchars($success_message); ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="mb-4 p-4 bg-red-500/10 border border-red-400/30 rounded-lg text-red-200 text-sm">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-red-400"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (!isset($success_message)): ?>
<form id="forgotPasswordForm" action="" method="POST" class="space-y-6">
    <?php
        FormComponents::renderInputField([
            'type' => 'text',
            'name' => 'username_or_email',
            'id' => 'usernameOrEmail',
            'label' => 'Username or Email Address',
            'placeholder' => 'Enter your username or email',
            'icon' => 'user',
            'required' => true,
            'autocomplete' => 'username',
            'value' => $_POST['username_or_email'] ?? '',
            'help_text' => 'Enter either your KCAA username or the email address associated with your account'
        ]);
    ?>
    
    <?php
        FormComponents::renderSubmitButton([
            'text' => 'Send Reset Instructions',
            'icon' => 'paper-plane'
        ]);
    ?>
</form>
<?php else: ?>
<div class="space-y-4">
    <div class="bg-blue-500/10 border border-blue-400/30 rounded-lg p-4 text-blue-200 text-sm">
        <div class="flex items-start">
            <i class="fas fa-envelope mr-3 text-blue-400 mt-0.5"></i>
            <div>
                <div class="font-medium mb-2">What happens next?</div>
                <ul class="text-xs space-y-1 text-blue-300">
                    <li>• Check your email inbox for reset instructions</li>
                    <li>• Don't forget to check your spam/junk folder</li>
                    <li>• Click the secure link in the email to reset your password</li>
                    <li>• Create a strong new password for your account</li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php
        FormComponents::renderSubmitButton([
            'text' => 'Send Another Reset Email',
            'icon' => 'redo',
            'classes' => 'from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800'
        ]);
    ?>
</div>
<?php endif; ?>

<div class="mt-6 text-center">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-slate-800 text-gray-400">Remember your password?</span>
        </div>
    </div>
    
    <div class="mt-4">
        <?php
            FormComponents::renderLink([
                'href' => 'login.php',
                'text' => 'Back to Login',
                'classes' => 'inline-flex items-center text-orange-400 hover:text-orange-300 hover:underline transition-colors text-sm'
            ]);
        ?>
    </div>
</div>

<div class="mt-6 text-center">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-slate-800 text-gray-400">Need additional help?</span>
        </div>
    </div>
    
    <div class="mt-4 text-sm text-gray-400">
        <div class="mb-2">If you continue to have trouble accessing your account:</div>
        <div class="space-y-1 text-xs">
            <div>• Contact your system administrator</div>
            <div>• Visit the KCAA IT Help Desk</div>
            <div>• Call the technical support line</div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }, 10000);
        });
    }
});
</script>

<?php
    AuthFooter::render();
?>