<?php
require_once __DIR__ . '/Database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($data) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                INSERT INTO notifications (
                    recipient_id, sender_id, title, message, 
                    notification_type, related_file_id, is_read, created_at
                ) VALUES (
                    :recipient_id, :sender_id, :title, :message, 
                    :notification_type, :related_file_id, :is_read, NOW()
                )
            ");
            
            $stmt->bindParam(':recipient_id', $data['recipient_id']);
            $stmt->bindValue(':sender_id', $data['sender_id'] ?? null);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':message', $data['message']);
            $stmt->bindParam(':notification_type', $data['notification_type']);
            $stmt->bindValue(':related_file_id', $data['related_file_id'] ?? null);
            $stmt->bindValue(':is_read', $data['is_read'] ?? false);
            
            $stmt->execute();
            
            return $conn->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function markAsRead($notificationId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE notification_id = :notification_id
            ");
            $stmt->bindParam(':notification_id', $notificationId);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Notification mark as read error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 10) {
        try {
            $conn = $this->db->connect();
            
            $sql = "
                SELECT * FROM notifications 
                WHERE recipient_id = :user_id
            ";
            
            if ($unreadOnly) {
                $sql .= " AND is_read = FALSE";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get user notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUnreadCount($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM notifications 
                WHERE recipient_id = :user_id AND is_read = FALSE
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Get unread notifications count error: " . $e->getMessage());
            return 0;
        }
    }
}