<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
/**
 * Create a notification for a file action
 *
 * @param int $department_id The ID of the department to notify
 * @param int $related_id The ID of the related file or action
 * @param string $type The type of notification (e.g., 'file_upload', 'file_share')
 * @param string $message The notification message
 * @return bool True on success, false on failure
 */
function createNotification($department_id, $related_id, $type, $message) {
    $conn = Database::getConnection();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                department_id, 
                related_id, 
                type, 
                message, 
                is_read, 
                created_at
            ) VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param("iiss", $department_id, $related_id, $type, $message);
        if (!$stmt->execute()) {
            error_log("Notification creation failed: " . $stmt->error);
            return false;
        }
        $stmt->close();
        return true;

    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
        return false;
    }
}
?>
   