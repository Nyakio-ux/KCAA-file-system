document.addEventListener('DOMContentLoaded', function() {
    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.getAttribute('data-id');
            
            fetch('api/notifications/mark_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.remove('unread');
                    const badge = this.querySelector('.badge');
                    if (badge) {
                        badge.remove();
                    }
                    
                    // Update notification count in navbar if exists
                    const notificationCount = document.querySelector('.notification-count');
                    if (notificationCount) {
                        const count = parseInt(notificationCount.textContent) - 1;
                        if (count > 0) {
                            notificationCount.textContent = count;
                        } else {
                            notificationCount.remove();
                        }
                    }
                }
            });
        });
    });
    
    // Mark all notifications as read
    document.getElementById('markAllRead').addEventListener('click', function() {
        fetch('api/notifications/mark_all_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const badge = item.querySelector('.badge');
                    if (badge) {
                        badge.remove();
                    }
                });
                
                // Remove notification count in navbar
                const notificationCount = document.querySelector('.notification-count');
                if (notificationCount) {
                    notificationCount.remove();
                }
            }
        });
    });
});