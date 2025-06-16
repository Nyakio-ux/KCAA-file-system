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