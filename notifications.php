<?php
$pageTitle = "Notifications";
$pageScript = 'notifications.js';
require_once 'userincludes/header.php';

require_once 'includes/dashboard.php';
$dashboard = new Dashboard();

// Get user notifications
$notifications = $dashboard->getUserNotifications($currentUser['user_id'], 20, false);
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <!-- Notifications Content -->
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Notifications</h2>
                    <p class="text-gray-600 dark:text-gray-400">Your recent notifications</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="markAllRead" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-check-circle"></i>
                        <span>Mark All as Read</span>
                    </button>
                </div>
            </div>
            
            <div class="space-y-6">
                <?php if (empty($notifications['notifications'])): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                        <div class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4">
                            <i class="fas fa-bell-slash text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No notifications</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">When you receive notifications, they'll appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($notifications['notifications'] as $notification): ?>
                                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 notification-item 
                                    <?php echo $notification['is_read'] ? '' : 'bg-blue-50 dark:bg-blue-900/20'; ?>" 
                                    data-id="<?php echo $notification['notification_id']; ?>">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full <?php echo $notification['is_read'] ? 'bg-gray-100 dark:bg-gray-700' : 'bg-primary-100 dark:bg-primary-900'; ?> flex items-center justify-center <?php echo $notification['is_read'] ? 'text-gray-600 dark:text-gray-300' : 'text-primary-600 dark:text-primary-300'; ?>">
                                            <i class="fas fa-<?php echo getNotificationIcon($notification['notification_type']); ?>"></i>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium text-gray-800 dark:text-white">
                                                    <?php echo htmlspecialchars($notification['title']); ?>
                                                    <?php if (!$notification['is_read']): ?>
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300">
                                                            New
                                                        </span>
                                                    <?php endif; ?>
                                                </h4>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?php echo formatDate($notification['created_at']); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                                                <?php if ($notification['sender_name']): ?>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-user-circle mr-1"></i>
                                                        <?php echo htmlspecialchars($notification['sender_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-robot mr-1"></i>
                                                        System Notification
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="assets/js/main.js"></script>

<?php
function getNotificationIcon($type) {
    switch (strtolower($type)) {
        case 'file_uploaded': return 'file-upload';
        case 'file_approved': return 'file-check';
        case 'file_rejected': return 'file-exclamation';
        case 'file_shared': return 'share-alt';
        case 'mention': return 'at';
        case 'message': return 'envelope';
        case 'system': return 'cog';
        default: return 'bell';
    }
}

// Helper function to format date
function formatDate($dateString) {
    return date('M j, Y g:i A', strtotime($dateString));
}
?>
