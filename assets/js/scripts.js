document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar').toggleClass('active');
        $('body').toggleClass('sidebar-collapsed');
    });
    
    // Dropdown menus
    $('.dropdown-toggle').dropdown();
    
    // Tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Popovers
    $('[data-bs-toggle="popover"]').popover();
    
    // Auto-dismiss alerts
    $('.alert').delay(3000).fadeOut('slow');
    
    // Form validation
    $('form.needs-validation').on('submit', function(e) {
        if (this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Update notification count in navbar
    updateNotificationCount();
    
    // Check for new notifications every 5 minutes
    setInterval(updateNotificationCount, 300000);
});

function updateNotificationCount() {
    fetch('api/notifications/count_unread.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                let badge = document.querySelector('.notification-badge');
                if (!badge) {
                    const navItem = document.querySelector('.nav-notifications');
                    badge = document.createElement('span');
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge';
                    navItem.appendChild(badge);
                }
                badge.textContent = data.count;
            } else {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.remove();
                }
            }
        });
}