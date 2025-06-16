document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('resetPasswordBtn');
    const form = document.getElementById('passwordResetForm');
    
    // Password strength checking
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            if (element) {
                if (requirements[req]) {
                    element.className = 'fas fa-check text-green-400 mr-2 w-3';
                    score++;
                } else {
                    element.className = 'fas fa-times text-red-400 mr-2 w-3';
                }
            }
        });
        
        // Update strength bar
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthText');
        
        if (strengthBar && strengthText) {
            const percentage = (score / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (score < 2) {
                strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-red-500';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs text-red-400';
            } else if (score < 4) {
                strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-yellow-500';
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-xs text-yellow-400';
            } else if (score < 5) {
                strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-blue-500';
                strengthText.textContent = 'Good';
                strengthText.className = 'text-xs text-blue-400';
            } else {
                strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-green-500';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs text-green-400';
            }
        }
        
        return score === 5;
    }
    
    // Password matching
    function checkPasswordMatch() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const indicator = document.getElementById('passwordMatchIndicator');
        const icon = document.getElementById('matchIcon');
        const text = document.getElementById('matchText');
        
        if (confirmPassword.length > 0) {
            indicator.classList.remove('hidden');
            
            if (password === confirmPassword) {
                icon.className = 'fas fa-check text-green-400 mr-2';
                text.textContent = 'Passwords match';
                text.className = 'text-green-400';
                return true;
            } else {
                icon.className = 'fas fa-times text-red-400 mr-2';
                text.textContent = 'Passwords do not match';
                text.className = 'text-red-400';
                return false;
            }
        } else {
            indicator.classList.add('hidden');
            return false;
        }
    }
    
    // Enable/disable submit button
    function updateSubmitButton() {
        const isStrong = checkPasswordStrength(newPasswordInput.value);
        const isMatching = checkPasswordMatch();
        
        if (isStrong && isMatching) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled:opacity-50', 'disabled:cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled:opacity-50', 'disabled:cursor-not-allowed');
        }
    }
    
    // Event listeners
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', updateSubmitButton);
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', updateSubmitButton);
    }
    
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.className = 'fas fa-eye-slash text-sm';
            } else {
                targetInput.type = 'password';
                icon.className = 'fas fa-eye text-sm';
            }
        });
    });
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating Password...';
            
            // Re-enable after a delay if form submission fails
            setTimeout(() => {
                if (submitBtn.disabled && submitBtn.innerHTML.includes('Updating')) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }, 10000);
        });
    }
    
    // Initial check
    if (newPasswordInput && confirmPasswordInput) {
        updateSubmitButton();
    }
});