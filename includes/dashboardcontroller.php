<?php
require_once 'dashboard.php';

class DashboardController {
    private $dashboard;
    
    public function __construct() {
        $this->dashboard = new Dashboard();
    }
    
    public function getDashboardData() {
        
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->dashboard->getDashboardData($userId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    public function getNotifications($unreadOnly = false) {
        
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->dashboard->getUserNotifications($userId, 10, $unreadOnly);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    public function markNotificationAsRead($notificationId) {
       
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->dashboard->markNotificationAsRead($notificationId, $userId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    public function getActivityLog() {
        
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->dashboard->getUserActivityLog($userId, 10);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    public function getDepartmentFiles($departmentId, $filters = [], $page = 1, $perPage = 10) {
    
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }
        
        $result = $this->dashboard->getDepartmentFiles($departmentId, $filters, $page, $perPage);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}