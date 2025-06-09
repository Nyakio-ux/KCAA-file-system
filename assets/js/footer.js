// Display current time
function updateTime() {
    const now = new Date();
    if (document.getElementById('currentTime')) {
        document.getElementById('currentTime').textContent = now.toLocaleString();
    }
}
updateTime();
setInterval(updateTime, 1000);

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
});