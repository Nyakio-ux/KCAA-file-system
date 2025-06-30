 // Toggle sidebar on mobile
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

// Dark mode toggle
document.getElementById('darkModeToggle').addEventListener('click', function() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        document.cookie = 'darkMode=false; path=/; max-age=31536000; SameSite=Lax';
    } else {
        html.classList.add('dark');
        document.cookie = 'darkMode=true; path=/; max-age=31536000; SameSite=Lax';
    }
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 768 && !sidebar.contains(event.target) && event.target !== sidebarToggle) {
        sidebar.classList.remove('active');
    }
});

// Adjust main content margin when sidebar is toggled
function adjustMainContent() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        if (sidebar.classList.contains('active')) {
            mainContent.style.marginLeft = '0';
        } else {
            mainContent.style.marginLeft = '0';
        }
    } else {
        mainContent.style.marginLeft = '16rem'; // 64 = 16rem
    }
}

// Initial adjustment
adjustMainContent();

// Adjust on window resize
window.addEventListener('resize', adjustMainContent);

// Center modal on show
$('#uploadModal').on('show.bs.modal', function() {
    $(this).css('display', 'flex');
    $(this).css('align-items', 'center');
    $(this).css('justify-content', 'center');
});

// Reset modal position when hidden
$('#uploadModal').on('hidden.bs.modal', function() {
    $(this).css('display', 'none');
});