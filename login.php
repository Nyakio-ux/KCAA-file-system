<?php
/**
 * KCAA SmartFiles - Login Page
 * Secure login interface for KCAA file management system
 */

require_once 'components/AuthHead.php';
require_once 'components/AuthLayout.php';
require_once 'components/FormComponents.php';
require_once 'components/AuthFooter.php';
require_once 'includes/auth.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    
    $identifier = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    $result = $auth->login($identifier, $password, $rememberMe);
    
    if ($result['success']) {
        header('Location: home.php');
        exit;
    } else {
        $error_message = $result['message'];
    }
}


AuthHead::render("KCAA SmartFiles - Login", "Secure login to KCAA file management system");

AuthLayout::render([
    'icon' => 'sign-in-alt',
    'title' => 'KCAA SmartFiles',
    'subtitle' => 'Secure Login',
    'description' => 'Kenya Civil Aviation Authority',
    'info_title' => 'Authorized Access Only',
    'info_text' => 'Please enter your KCAA credentials to access the system'
]);

if (isset($error_message)): ?>
    <div class="mb-4 p-4 bg-red-500/10 border border-red-400/30 rounded-lg text-red-200 text-sm">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-red-400"></i>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    </div>
<?php endif; ?>
<form id="loginForm" action="" method="POST" class="space-y-6">
    <?php
        FormComponents::renderInputField([
            'type' => 'text',
            'name' => 'username',
            'id' => 'username',
            'label' => 'Username',
            'placeholder' => 'Enter your username or email',
            'icon' => 'user',
            'required' => true,
            'autocomplete' => 'username',
            'value' => $_POST['username'] ?? ''
        ]);

        FormComponents::renderInputField([
            'type' => 'password',
            'name' => 'password',
            'id' => 'password',
            'label' => 'Password',
            'placeholder' => 'Enter your password',
            'icon' => 'lock',
            'required' => true,
            'show_toggle' => true,
            'autocomplete' => 'current-password'
            
        ]);
    ?>

    <div class="flex items-center justify-between text-sm">
        <?php
            FormComponents::renderCheckboxField([
                'name' => 'remember_me',
                'id' => 'rememberMe',
                'label' => 'Remember me',
                'checked' => isset($_POST['remember_me'])
            ]);

            FormComponents::renderLink([
                'href' => 'forgotpassword.php',
                'text' => 'Forgot password?'
            ]);
        ?>
    </div>
    
    <?php
        FormComponents::renderSubmitButton([
            'text' => 'Access System',
            'icon' => 'sign-in-alt'
        ]);
    ?>
</form>
<div class="mt-6 text-center">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-slate-800 text-gray-400">Need help?</span>
        </div>
    </div>
    
    <div class="mt-4 text-sm text-gray-400">
        For technical support, contact your system administrator
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const submitButton = loginForm.querySelector('button[type="submit"]');
        
        loginForm.addEventListener('submit', function(e) {
            const originalContent = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Authenticating...
            `;
            setTimeout(() => {
                if (!loginForm.checkValidity()) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalContent;
                }
            }, 1000);
        });
    });
</script>
<?php
    AuthFooter::render();
?>