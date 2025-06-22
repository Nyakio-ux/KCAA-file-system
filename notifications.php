<?php
$pageTitle = "Notifications";
$pageScript = 'notifications.js';
require_once 'userincludes/header.php';

require_once 'includes/dashboard.php';
$dashboard = new Dashboard();

// Get user notifications
$notifications = $dashboard->getUserNotifications($currentUser['user_id'], 20, false);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Notifications</h1>
    </div>
    <div class="col-md-6 text-end">
        <button id="markAllRead" class="btn btn-outline-secondary">
            <i class="fas fa-check-circle"></i> Mark All as Read
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($notifications['notifications'])): ?>
            <p class="text-muted">No notifications</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications['notifications'] as $notification): ?>
                    <a href="#" class="list-group-item list-group-item-action notification-item 
                        <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                        data-id="<?php echo $notification['notification_id']; ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h5>
                            <small><?php echo formatDate($notification['created_at']); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small>
                            <?php if ($notification['sender_name']): ?>
                                From: <?php echo htmlspecialchars($notification['sender_name']); ?>
                            <?php else: ?>
                                System Notification
                            <?php endif; ?>
                        </small>
                        <?php if (!$notification['is_read']): ?>
                            <span class="badge bg-primary float-end">New</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'userincludes/footer.php';
?>