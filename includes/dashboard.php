<?php
require_once 'database.php';
require_once 'auth.php';
require_once 'users.php';

class Dashboard {
    private $db;
    private $auth;
    private $userActions;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->userActions = new UserActions();
    }


    /**
     * Get user by ID with all related data
     */
    private function getUserById($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT 
                    u.*, 
                    ur.role_id, 
                    r.role_name, 
                    ur.department_id, 
                    d.department_name,
                    uc.category_name as user_category
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
                LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
                WHERE u.user_id = :user_id
                GROUP BY u.user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get dashboard data based on user role
     */
    public function getDashboardData($userId) {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $roleId = $user['role_id'] ?? null;
            $departmentId = $user['department_id'] ?? null;
            
            // Get data based on user role
            switch ($roleId) {
                case 1: // Admin
                    return $this->getAdminDashboardData();
                case 2: // Department Head
                    return $this->getDepartmentHeadDashboardData($userId, $departmentId);
                default: // Regular User
                    return $this->getUserDashboardData($userId, $departmentId);
            }
            
        } catch (PDOException $e) {
            error_log("Dashboard error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to load dashboard data'];
        }
    }
    
    /**
     * Get admin dashboard data
     */
    private function getAdminDashboardData() {
        $conn = $this->db->connect();
        
        // Get user statistics
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN is_active = FALSE THEN 1 ELSE 0 END) as inactive_users,
                (SELECT COUNT(*) FROM user_roles WHERE role_id = 1) as admin_users,
                (SELECT COUNT(*) FROM user_roles WHERE role_id = 2) as dept_head_users,
                (SELECT COUNT(*) FROM user_roles WHERE role_id = 3) as regular_users
            FROM users
        ");
        $stmt->execute();
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get file statistics
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_files,
                SUM(file_size) as total_storage,
                (SELECT COUNT(*) FROM file_shares) as total_shares,
                (SELECT COUNT(*) FROM file_approvals WHERE status_id = 4) as approved_files,
                (SELECT COUNT(*) FROM file_approvals WHERE status_id = 5) as rejected_files
            FROM files
        ");
        $stmt->execute();
        $fileStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get recent activities
        $stmt = $conn->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.notification_type,
                n.created_at,
                u.username as sender_username
            FROM notifications n
            LEFT JOIN users u ON n.sender_id = u.user_id
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get department statistics
        $stmt = $conn->prepare("
            SELECT 
                d.department_id,
                d.department_name,
                COUNT(DISTINCT ur.user_id) as user_count,
                COUNT(DISTINCT f.file_id) as file_count,
                COUNT(DISTINCT fs.share_id) as share_count
            FROM departments d
            LEFT JOIN user_roles ur ON d.department_id = ur.department_id AND ur.is_active = TRUE
            LEFT JOIN files f ON d.department_id = f.source_department_id
            LEFT JOIN file_shares fs ON d.department_id = fs.shared_from_dept
            GROUP BY d.department_id, d.department_name
        ");
        $stmt->execute();
        $departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'user_stats' => $userStats,
            'file_stats' => $fileStats,
            'recent_activities' => $recentActivities,
            'department_stats' => $departmentStats
        ];
    }
    
    /**
     * Get department head dashboard data
     */
    private function getDepartmentHeadDashboardData($userId, $departmentId) {
        $conn = $this->db->connect();
        
        // Get department info
        $stmt = $conn->prepare("
            SELECT 
                d.department_id,
                d.department_name,
                d.description,
                CONCAT(u.first_name, ' ', u.last_name) as head_name,
                COUNT(DISTINCT ur.user_id) as member_count
            FROM departments d
            LEFT JOIN users u ON d.head_user_id = u.user_id
            LEFT JOIN user_roles ur ON d.department_id = ur.department_id AND ur.is_active = TRUE
            WHERE d.department_id = :department_id
            GROUP BY d.department_id, d.department_name, d.description, u.first_name, u.last_name
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $departmentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get department files
        $stmt = $conn->prepare("
            SELECT 
                f.file_id,
                f.file_name,
                f.original_name,
                fc.category_name,
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
                f.upload_date,
                f.file_size,
                ws.status_name as current_status
            FROM files f
            JOIN users u ON f.uploaded_by = u.user_id
            LEFT JOIN file_categories fc ON f.category_id = fc.category_id
            LEFT JOIN (
                SELECT file_id, status_id 
                FROM file_approvals 
                WHERE department_id = :department_id
                ORDER BY created_at DESC
                LIMIT 1
            ) fa ON f.file_id = fa.file_id
            LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
            WHERE f.source_department_id = :department_id
            ORDER BY f.upload_date DESC
            LIMIT 10
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $departmentFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get pending approvals
        $stmt = $conn->prepare("
            SELECT 
                fa.approval_id,
                f.file_id,
                f.file_name,
                f.original_name,
                d.department_name as source_department,
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
                fa.created_at as request_date,
                ws.status_name
            FROM file_approvals fa
            JOIN files f ON fa.file_id = f.file_id
            JOIN departments d ON f.source_department_id = d.department_id
            JOIN users u ON f.uploaded_by = u.user_id
            JOIN workflow_statuses ws ON fa.status_id = ws.status_id
            WHERE fa.department_id = :department_id
            AND fa.status_id IN (1, 2, 3, 6) -- Pending states
            ORDER BY fa.created_at DESC
            LIMIT 10
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $pendingApprovals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get department members
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                u.username,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                uc.category_name as user_category,
                ur.assigned_at as joined_date,
                u.is_active
            FROM user_roles ur
            JOIN users u ON ur.user_id = u.user_id
            LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
            WHERE ur.department_id = :department_id
            AND ur.is_active = TRUE
            ORDER BY ur.assigned_at DESC
            LIMIT 10
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $departmentMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'department_info' => $departmentInfo,
            'department_files' => $departmentFiles,
            'pending_approvals' => $pendingApprovals,
            'department_members' => $departmentMembers
        ];
    }
    
    /**
     * Get regular user dashboard data
     */
    private function getUserDashboardData($userId, $departmentId) {
        $conn = $this->db->connect();
        
        // Get user's department info
        $stmt = $conn->prepare("
            SELECT 
                d.department_id,
                d.department_name,
                d.description,
                CONCAT(u.first_name, ' ', u.last_name) as head_name
            FROM departments d
            LEFT JOIN users u ON d.head_user_id = u.user_id
            WHERE d.department_id = :department_id
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $departmentInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get user's files
        $stmt = $conn->prepare("
            SELECT 
                f.file_id,
                f.file_name,
                f.original_name,
                fc.category_name,
                f.upload_date,
                f.file_size,
                ws.status_name as current_status
            FROM files f
            LEFT JOIN file_categories fc ON f.category_id = fc.category_id
            LEFT JOIN (
                SELECT file_id, status_id 
                FROM file_approvals 
                WHERE department_id = :department_id
                ORDER BY created_at DESC
                LIMIT 1
            ) fa ON f.file_id = fa.file_id
            LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
            WHERE f.uploaded_by = :user_id
            ORDER BY f.upload_date DESC
            LIMIT 10
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $userFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get shared files
        $stmt = $conn->prepare("
            SELECT 
                f.file_id,
                f.file_name,
                f.original_name,
                d.department_name as shared_from,
                CONCAT(u.first_name, ' ', u.last_name) as shared_by,
                fs.share_date,
                fs.share_message
            FROM file_shares fs
            JOIN files f ON fs.file_id = f.file_id
            JOIN departments d ON fs.shared_from_dept = d.department_id
            JOIN users u ON fs.shared_by = u.user_id
            WHERE fs.shared_to_dept = :department_id
            ORDER BY fs.share_date DESC
            LIMIT 10
        ");
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->execute();
        $sharedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get notifications
        $stmt = $conn->prepare("
            SELECT 
                notification_id,
                title,
                message,
                notification_type,
                created_at,
                is_read
            FROM notifications
            WHERE recipient_id = :user_id
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'department_info' => $departmentInfo,
            'user_files' => $userFiles,
            'shared_files' => $sharedFiles,
            'notifications' => $notifications
        ];
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        try {
            $conn = $this->db->connect();
            
            $query = "
                SELECT 
                    n.notification_id,
                    n.title,
                    n.message,
                    n.notification_type,
                    n.created_at,
                    n.is_read,
                    n.read_at,
                    u.username as sender_username,
                    CONCAT(u.first_name, ' ', u.last_name) as sender_name
                FROM notifications n
                LEFT JOIN users u ON n.sender_id = u.user_id
                WHERE n.recipient_id = :user_id
            ";
            
            if ($unreadOnly) {
                $query .= " AND n.is_read = FALSE";
            }
            
            $query .= " ORDER BY n.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            
            if ($limit) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['success' => true, 'notifications' => $notifications];
            
        } catch (PDOException $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve notifications'];
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId, $userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = TRUE, read_at = NOW() 
                WHERE notification_id = :notification_id 
                AND recipient_id = :user_id
            ");
            $stmt->bindParam(':notification_id', $notificationId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Mark notification as read error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to mark notification as read'];
        }
    }
    
    /**
     * Get user activity log
     */
    public function getUserActivityLog($userId, $limit = 10) {
        try {
            $conn = $this->db->connect();
            
            // Combine login logs and file access logs
            $query = "
                (SELECT 
                    'login' as activity_type,
                    login_time as activity_time,
                    ip_address,
                    user_agent,
                    NULL as action,
                    NULL as related_file_id
                FROM login_logs
                WHERE user_id = :user_id)
                
                UNION ALL
                
                (SELECT 
                    'file_access' as activity_type,
                    access_time as activity_time,
                    ip_address,
                    user_agent,
                    action,
                    file_id as related_file_id
                FROM file_access_logs
                WHERE user_id = :user_id)
                
                ORDER BY activity_time DESC
                LIMIT :limit
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enhance file access activities with file names
            foreach ($activities as &$activity) {
                if ($activity['activity_type'] === 'file_access' && $activity['related_file_id']) {
                    $fileStmt = $conn->prepare("
                        SELECT file_name, original_name 
                        FROM files 
                        WHERE file_id = :file_id
                    ");
                    $fileStmt->bindParam(':file_id', $activity['related_file_id']);
                    $fileStmt->execute();
                    $fileInfo = $fileStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($fileInfo) {
                        $activity['file_name'] = $fileInfo['file_name'];
                        $activity['original_name'] = $fileInfo['original_name'];
                    }
                }
            }
            
            return ['success' => true, 'activities' => $activities];
            
        } catch (PDOException $e) {
            error_log("Get user activity log error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve activity log'];
        }
    }
    
    /**
     * Get department files with filtering
     */
    public function getDepartmentFiles($departmentId, $filters = [], $page = 1, $perPage = 10) {
        try {
            $conn = $this->db->connect();
            
            // Base query
            $query = "
                SELECT 
                    f.file_id,
                    f.file_name,
                    f.original_name,
                    fc.category_name,
                    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
                    f.upload_date,
                    f.file_size,
                    f.is_confidential,
                    ws.status_name as current_status
                FROM files f
                JOIN users u ON f.uploaded_by = u.user_id
                LEFT JOIN file_categories fc ON f.category_id = fc.category_id
                LEFT JOIN (
                    SELECT file_id, status_id 
                    FROM file_approvals 
                    WHERE department_id = :department_id
                    ORDER BY created_at DESC
                    LIMIT 1
                ) fa ON f.file_id = fa.file_id
                LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
                WHERE f.source_department_id = :department_id
            ";
            
            // Add filters
            $params = [':department_id' => $departmentId];
            
            if (!empty($filters['category_id'])) {
                $query .= " AND f.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            
            if (!empty($filters['uploaded_by'])) {
                $query .= " AND f.uploaded_by = :uploaded_by";
                $params[':uploaded_by'] = $filters['uploaded_by'];
            }
            
            if (isset($filters['is_confidential'])) {
                $query .= " AND f.is_confidential = :is_confidential";
                $params[':is_confidential'] = $filters['is_confidential'];
            }
            
            if (!empty($filters['status_id'])) {
                $query .= " AND fa.status_id = :status_id";
                $params[':status_id'] = $filters['status_id'];
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (f.original_name LIKE :search OR f.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Add sorting
            $sortField = $filters['sort'] ?? 'f.upload_date';
            $sortOrder = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $query .= " ORDER BY $sortField $sortOrder";
            
            // Add pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :offset, :per_page";
            $params[':offset'] = $offset;
            $params[':per_page'] = $perPage;
            
            // Execute query
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM files f WHERE f.source_department_id = :department_id";
            
            if (!empty($filters['category_id'])) {
                $countQuery .= " AND f.category_id = :category_id";
            }
            
            if (!empty($filters['uploaded_by'])) {
                $countQuery .= " AND f.uploaded_by = :uploaded_by";
            }
            
            if (isset($filters['is_confidential'])) {
                $countQuery .= " AND f.is_confidential = :is_confidential";
            }
            
            if (!empty($filters['status_id'])) {
                $countQuery .= " AND EXISTS (
                    SELECT 1 FROM file_approvals fa 
                    WHERE fa.file_id = f.file_id 
                    AND fa.department_id = :department_id 
                    AND fa.status_id = :status_id
                )";
            }
            
            if (!empty($filters['search'])) {
                $countQuery .= " AND (f.original_name LIKE :search OR f.description LIKE :search)";
            }
            
            $stmt = $conn->prepare($countQuery);
            
            // Bind only the params that exist in the count query
            foreach ($params as $key => $value) {
                if (strpos($countQuery, $key) !== false) {
                    $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $stmt->bindValue($key, $value, $paramType);
                }
            }
            
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'files' => $files,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
            
        } catch (PDOException $e) {
            error_log("Get department files error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve department files'];
        }
    }
}