 document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('resetSubmitBtn');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    const matchIndicator = document.getElementById('passwordMatchIndicator');
    const matchIcon = document.getElementById('matchIcon');
    const matchText = document.getElementById('matchText');
    
    // Password requirements elements
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    // Password toggle functionality
    function setupPasswordToggle(toggleId, passwordId) {
        const toggle = document.getElementById(toggleId);
        const password = document.getElementById(passwordId);
        
        if (toggle && password) {
            toggle.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }
    
    setupPasswordToggle('toggleNewPassword', 'new_password');
    setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        // Update requirement indicators
        updateRequirement(reqLength, requirements.length);
        updateRequirement(reqUppercase, requirements.uppercase);
        updateRequirement(reqLowercase, requirements.lowercase);
        updateRequirement(reqNumber, requirements.number);
        updateRequirement(reqSpecial, requirements.special);
        
        // Calculate score
        Object.values(requirements).forEach(req => {
            if (req) score++;
        });
        
        // Update strength bar
        const percentage = (score / 5) * 100;
        strengthBar.style.width = percentage + '%';
        
        // Update color and text
        if (score < 2) {
            strengthBar.className = 'h-2 rounded-full bg-red-500 transition-all duration-300';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-xs text-red-400';
        } else if (score < 4) {
            strengthBar.className = 'h-2 rounded-full bg-yellow-500 transition-all duration-300';
            strengthText.textContent = 'Medium';
            strengthText.className = 'text-xs text-yellow-400';
        } else {
            strengthBar.className = 'h-2 rounded-full bg-green-500 transition-all duration-300';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-xs text-green-400';
        }
        
        return score === 5;
    }
    
    function updateRequirement(element, met) {
        if (met) {
            element.className = 'fas fa-check text-green-400 mr-2 w-3';
        } else {
            element.className = 'fas fa-times text-red-400 mr-2 w-3';
        }
    }
    
    // Password match checker
    function checkPasswordMatch() {
        const match = newPassword.value === confirmPassword.value && confirmPassword.value !== '';
        
        if (confirmPassword.value !== '') {
            matchIndicator.classList.remove('hidden');
            
            if (match) {
                matchIcon.className = 'fas fa-check text-green-400 mr-2';
                matchText.textContent = 'Passwords match';
                matchText.className = 'text-green-400';
            } else {
                matchIcon.className = 'fas fa-times text-red-400 mr-2';
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-red-400';
            }
        } else {
            matchIndicator.classList.add('hidden');
        }
        
        return match;
    }
    
    // Validate form
    function validateForm() {
        const isStrongPassword = checkPasswordStrength(newPassword.value);
        const passwordsMatch = checkPasswordMatch();
        const isValid = isStrongPassword && passwordsMatch;
        
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-semibold rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400/50 disabled:opacity-50 disabled:cursor-not-allowed';
        }
    }
    
    // Event listeners
    newPassword.addEventListener('input', validateForm);
    confirmPassword.addEventListener('input', validateForm);
    
    // Form submission
    document.getElementById('passwordResetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!submitBtn.disabled) {
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Resetting Password...';
            submitBtn.disabled = true;
            
            // Submit form 
            setTimeout(() => {
                this.submit();
            }, 1000);
        }
    });
});