<?php
require_once 'Database.php';
require_once 'mail.php';
require_once 'notifications.php';

class UserActions {
    private $db;
    private $mail;
    private $notification;

    public function __construct() {
    $this->db = new Database();
    $this->mail = new Mail();  
    $this->notification = new Notification();
}

    /**
     * Create a new user with notification
     */
    public function createUser($userData, $createdBy) {
        try {
            $conn = $this->db->connect();
            
            // Validate required fields
            $required = ['username', 'email', 'first_name', 'last_name', 'password', 'role_id', 'department_id'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Required field '$field' is missing"];
                }
            }
            
            // Check if username or email already exists
            $stmt = $conn->prepare("
                SELECT user_id FROM users 
                WHERE username = :username OR email = :email
            ");
            $stmt->bindParam(':username', $userData['username']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->beginTransaction();
            
            // Insert user
            $stmt = $conn->prepare("
                INSERT INTO users (
                    username, email, password_hash, first_name, last_name, 
                    phone, user_category_id, is_active
                ) VALUES (
                    :username, :email, :password_hash, :first_name, :last_name, 
                    :phone, :user_category_id, :is_active
                )
            ");
            
            $stmt->bindValue(':username', $userData['username']);
            $stmt->bindValue(':email', $userData['email']);
            $stmt->bindValue(':password_hash', $passwordHash);
            $stmt->bindValue(':first_name', $userData['first_name']);
            $stmt->bindValue(':last_name', $userData['last_name']);
            $stmt->bindValue(':phone', $userData['phone'] ?? null);
            $stmt->bindValue(':user_category_id', $userData['user_category_id'] ?? 1);
            $stmt->bindValue(':is_active', $userData['is_active'] ?? true, PDO::PARAM_BOOL);
            
            $stmt->execute();
            $userId = $conn->lastInsertId();
            
            // Assign role
            $stmt = $conn->prepare("
                INSERT INTO user_roles (
                    user_id, role_id, department_id, assigned_by, is_active
                ) VALUES (
                    :user_id, :role_id, :department_id, :assigned_by, TRUE
                )
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':role_id', $userData['role_id']);
            $stmt->bindParam(':department_id', $userData['department_id']);
            $stmt->bindParam(':assigned_by', $createdBy);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Get creator info for notifications
            $creator = $this->getUserById($createdBy);
            $creatorName = $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'System Administrator';
            
            // Send welcome email 
            $this->sendWelcomeEmail($userData);
            
            // Send notification to creator
            $this->sendUserCreatedEmailToAdmin($creator, $userData);
            
            // Create system notification for the new user
            $notificationData = [
                'recipient_id' => $userId,
                'sender_id' => $createdBy,
                'title' => 'Welcome to the System',
                'message' => 'Your account has been created successfully.',
                'notification_type' => 'account_created'
            ];
            $this->notification->create($notificationData);
            
            // Notify admin/creator about the new user
            $adminNotification = [
                'recipient_id' => $createdBy,
                'title' => 'New User Created',
                'message' => "User {$userData['username']} was successfully created.",
                'notification_type' => 'user_created'
            ];
            $this->notification->create($adminNotification);
            
            return ['success' => true, 'user_id' => $userId];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Create user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }
    
    /**
     * Update user information
     */
    public function updateUser($userId, $updateData, $updatedBy) {
        try {
            $conn = $this->db->connect();
            
            // Get current user data
            $currentUser = $this->getUserById($userId);
            if (!$currentUser) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Start building the query
            $query = "UPDATE users SET ";
            $params = [];
            $updates = [];
            
            // Process updatable fields
            $updatableFields = ['first_name', 'last_name', 'phone', 'user_category_id', 'is_active'];
            foreach ($updatableFields as $field) {
                if (isset($updateData[$field])) {
                    $updates[] = "$field = :$field";
                    $params[":$field"] = $updateData[$field];
                }
            }
            
            // Special handling for password
            if (!empty($updateData['password'])) {
                $passwordHash = password_hash($updateData['password'], PASSWORD_DEFAULT);
                $updates[] = "password_hash = :password_hash";
                $params[":password_hash"] = $passwordHash;
            }
            
            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            
            $query .= implode(', ', $updates) . " WHERE user_id = :user_id";
            $params[':user_id'] = $userId;
            
            // Execute update
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            
            // Handle role/department updates if provided
            if (isset($updateData['role_id']) || isset($updateData['department_id'])) {
                $this->updateUserRole($userId, 
                    $updateData['role_id'] ?? null, 
                    $updateData['department_id'] ?? null, 
                    $updatedBy
                );
            }
            
            // Get updater info
            $updater = $this->getUserById($updatedBy);
            $updaterName = $updater ? $updater['first_name'] . ' ' . $updater['last_name'] : 'System Administrator';
            
            // Send email notification to user about the update
            $this->sendAccountUpdatedEmail($currentUser, $updaterName);
            
            // Send email notification to admin
            $this->sendUserUpdatedEmailToAdmin($updater, $currentUser);
            
            // Create notification for the updated user
            $notificationData = [
                'recipient_id' => $userId,
                'sender_id' => $updatedBy,
                'title' => 'Account Updated',
                'message' => 'Your account information has been updated.',
                'notification_type' => 'account_updated'
            ];
            $this->notification->create($notificationData);
            
            // Notify admin about the update
            $adminNotification = [
                'recipient_id' => $updatedBy,
                'title' => 'User Updated',
                'message' => "User {$currentUser['username']} was updated.",
                'notification_type' => 'user_updated'
            ];
            $this->notification->create($adminNotification);
            
            return ['success' => true, 'message' => 'User updated successfully'];
            
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }
    
    /**
     * Deactivate a user account
     */
    public function deactivateUser($userId, $deactivatedBy) {
        try {
            $conn = $this->db->connect();
            
            // Get user data first
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Deactivate user
            $stmt = $conn->prepare("
                UPDATE users SET is_active = FALSE 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Deactivate all roles
            $stmt = $conn->prepare("
                UPDATE user_roles SET is_active = FALSE 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Get deactivator info
            $deactivator = $this->getUserById($deactivatedBy);
            $deactivatorName = $deactivator ? $deactivator['first_name'] . ' ' . $deactivator['last_name'] : 'System Administrator';
            
            // Send deactivation email to user
            $this->sendAccountDeactivatedEmail($user, $deactivatorName);
            
            // Send notification to admin
            $this->sendUserDeactivatedEmailToAdmin($deactivator, $user);
            
            // Create notification for the deactivated user
            $notificationData = [
                'recipient_id' => $userId,
                'sender_id' => $deactivatedBy,
                'title' => 'Account Deactivated',
                'message' => 'Your account has been deactivated by an administrator.',
                'notification_type' => 'account_deactivated'
            ];
            $this->notification->create($notificationData);
            
            // Notify admin about the deactivation
            $adminNotification = [
                'recipient_id' => $deactivatedBy,
                'title' => 'User Deactivated',
                'message' => "User {$user['username']} was deactivated.",
                'notification_type' => 'user_deactivated'
            ];
            $this->notification->create($adminNotification);
            
            return ['success' => true, 'message' => 'User deactivated successfully'];
            
        } catch (PDOException $e) {
            error_log("Deactivate user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to deactivate user'];
        }
    }
    
    /**
     * Reactivate a user account
     */
    public function reactivateUser($userId, $reactivatedBy) {
        try {
            $conn = $this->db->connect();
            
            // Get user data first
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Reactivate user
            $stmt = $conn->prepare("
                UPDATE users SET is_active = TRUE 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Reactivate the primary role (you might want to modify this based on your needs)
            $stmt = $conn->prepare("
                UPDATE user_roles SET is_active = TRUE 
                WHERE user_id = :user_id 
                ORDER BY user_role_id ASC 
                LIMIT 1
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Get reactivator info
            $reactivator = $this->getUserById($reactivatedBy);
            $reactivatorName = $reactivator ? $reactivator['first_name'] . ' ' . $reactivator['last_name'] : 'System Administrator';
            
            // Send reactivation email to user
            $this->sendAccountReactivatedEmail($user, $reactivatorName);
            
            // Send notification to admin
            $this->sendUserReactivatedEmailToAdmin($reactivator, $user);
            
            // Create notification for the reactivated user
            $notificationData = [
                'recipient_id' => $userId,
                'sender_id' => $reactivatedBy,
                'title' => 'Account Reactivated',
                'message' => 'Your account has been reactivated by an administrator.',
                'notification_type' => 'account_reactivated'
            ];
            $this->notification->create($notificationData);
            
            // Notify admin about the reactivation
            $adminNotification = [
                'recipient_id' => $reactivatedBy,
                'title' => 'User Reactivated',
                'message' => "User {$user['username']} was reactivated.",
                'notification_type' => 'user_reactivated'
            ];
            $this->notification->create($adminNotification);
            
            return ['success' => true, 'message' => 'User reactivated successfully'];
            
        } catch (PDOException $e) {
            error_log("Reactivate user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reactivate user'];
        }
    }
    
    /**
     * Get user by ID with all related data
     */
    public function getUserById($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT 
                    u.*, 
                    uc.category_name as user_category,
                    GROUP_CONCAT(DISTINCT r.role_name SEPARATOR ', ') as roles,
                    GROUP_CONCAT(DISTINCT d.department_name SEPARATOR ', ') as departments
                FROM users u
                LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
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
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $perPage = 10, $filters = []) {
        try {
            $conn = $this->db->connect();
            
            // Build base query
            $query = "
                SELECT 
                    u.user_id, u.username, u.email, 
                    CONCAT(u.first_name, ' ', u.last_name) as full_name,
                    uc.category_name as user_category,
                    GROUP_CONCAT(DISTINCT r.role_name SEPARATOR ', ') as roles,
                    GROUP_CONCAT(DISTINCT d.department_name SEPARATOR ', ') as departments,
                    u.is_active, u.created_at
                FROM users u
                LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
            ";
            
            // Add filters
            $where = [];
            $params = [];
            
            if (!empty($filters['search'])) {
                $search = "%{$filters['search']}%";
                $where[] = "(u.username LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
                $params[':search'] = $search;
            }
            
            if (isset($filters['is_active'])) {
                $where[] = "u.is_active = :is_active";
                $params[':is_active'] = $filters['is_active'];
            }
            
            if (!empty($filters['role_id'])) {
                $where[] = "ur.role_id = :role_id";
                $params[':role_id'] = $filters['role_id'];
            }
            
            if (!empty($filters['department_id'])) {
                $where[] = "ur.department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }
            
            if (!empty($where)) {
                $query .= " WHERE " . implode(" AND ", $where);
            }
            
            $query .= " GROUP BY u.user_id";
            
            // Add sorting
            $sortField = $filters['sort'] ?? 'u.created_at';
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
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "
                SELECT COUNT(DISTINCT u.user_id) as total 
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
            ";
            
            if (!empty($where)) {
                $countQuery .= " WHERE " . implode(" AND ", $where);
            }
            
            $stmt = $conn->prepare($countQuery);
            
            // Remove LIMIT params for count query
            unset($params[':offset']);
            unset($params[':per_page']);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
            
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve users'];
        }
    }
    
    /**
     * Update user role and department assignment
     */
    private function updateUserRole($userId, $roleId, $departmentId, $updatedBy) {
        try {
            $conn = $this->db->connect();
            
            // Check if the user has an active role assignment
            $stmt = $conn->prepare("
                SELECT user_role_id FROM user_roles 
                WHERE user_id = :user_id AND is_active = TRUE
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $existingRole = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingRole) {
                // Update existing role
                $stmt = $conn->prepare("
                    UPDATE user_roles 
                    SET role_id = COALESCE(:role_id, role_id),
                        department_id = COALESCE(:department_id, department_id),
                        assigned_by = :assigned_by
                    WHERE user_role_id = :user_role_id
                ");
                $stmt->bindParam(':user_role_id', $existingRole['user_role_id']);
                $stmt->bindParam(':role_id', $roleId);
                $stmt->bindParam(':department_id', $departmentId);
                $stmt->bindParam(':assigned_by', $updatedBy);
                $stmt->execute();
            } else {
                // Create new role assignment
                $stmt = $conn->prepare("
                    INSERT INTO user_roles (
                        user_id, role_id, department_id, assigned_by, is_active
                    ) VALUES (
                        :user_id, :role_id, :department_id, :assigned_by, TRUE
                    )
                ");
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':role_id', $roleId);
                $stmt->bindParam(':department_id', $departmentId);
                $stmt->bindParam(':assigned_by', $updatedBy);
                $stmt->execute();
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Update user role error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===== EMAIL NOTIFICATION METHODS ===== //
    
    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail($userData) {
        $emailData = [
            'to' => $userData['email'],
            'subject' => 'Welcome to Our System',
            'template' => 'welcome_email',
            'data' => [
                'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'username' => $userData['username'],
                'login_link' => $_ENV['APP_URL'] . '/login',
                'support_email' => $_ENV['SUPPORT_EMAIL']
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send notification to admin about new user creation
     */
    private function sendUserCreatedEmailToAdmin($admin, $userData) {
        $emailData = [
            'to' => $admin['email'],
            'subject' => 'New User Account Created',
            'template' => 'user_created_admin_notification',
            'data' => [
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'new_user_name' => $userData['first_name'] . ' ' . $userData['last_name'],
                'new_user_email' => $userData['email'],
                'new_user_username' => $userData['username'],
                'created_at' => date('Y-m-d H:i')
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send account updated email to user
     */
    private function sendAccountUpdatedEmail($user, $updaterName) {
        $emailData = [
            'to' => $user['email'],
            'subject' => 'Your Account Has Been Updated',
            'template' => 'account_updated_notification',
            'data' => [
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'updated_by' => $updaterName,
                'update_time' => date('Y-m-d H:i'),
                'support_link' => $_ENV['APP_URL'] . '/support'
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send user updated notification to admin
     */
    private function sendUserUpdatedEmailToAdmin($admin, $user) {
        $emailData = [
            'to' => $admin['email'],
            'subject' => 'User Account Updated',
            'template' => 'user_updated_admin_notification',
            'data' => [
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_email' => $user['email'],
                'update_time' => date('Y-m-d H:i')
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send account deactivated email to user
     */
    private function sendAccountDeactivatedEmail($user, $deactivatorName) {
        $emailData = [
            'to' => $user['email'],
            'subject' => 'Your Account Has Been Deactivated',
            'template' => 'account_deactivated_notification',
            'data' => [
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'deactivated_by' => $deactivatorName,
                'deactivation_time' => date('Y-m-d H:i'),
                'support_contact' => $_ENV['SUPPORT_EMAIL']
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send user deactivated notification to admin
     */
    private function sendUserDeactivatedEmailToAdmin($admin, $user) {
        $emailData = [
            'to' => $admin['email'],
            'subject' => 'User Account Deactivated',
            'template' => 'user_deactivated_admin_notification',
            'data' => [
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_email' => $user['email'],
                'deactivation_time' => date('Y-m-d H:i')
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send account reactivated email to user
     */
    private function sendAccountReactivatedEmail($user, $reactivatorName) {
        $emailData = [
            'to' => $user['email'],
            'subject' => 'Your Account Has Been Reactivated',
            'template' => 'account_reactivated_notification',
            'data' => [
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'reactivated_by' => $reactivatorName,
                'reactivation_time' => date('Y-m-d H:i'),
                'login_link' => $_ENV['APP_URL'] . '/login'
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send user reactivated notification to admin
     */
    private function sendUserReactivatedEmailToAdmin($admin, $user) {
        $emailData = [
            'to' => $admin['email'],
            'subject' => 'User Account Reactivated',
            'template' => 'user_reactivated_admin_notification',
            'data' => [
                'admin_name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'user_email' => $user['email'],
                'reactivation_time' => date('Y-m-d H:i')
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Invite a new user to a department
     */
    public function inviteUserToDepartment($email, $departmentId, $userCategoryId, $invitedBy, $message = '') {
        try {
            $conn = $this->db->connect();
            
            // Check if department exists
            $stmt = $conn->prepare("SELECT department_id FROM departments WHERE department_id = :department_id");
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Department not found'];
            }
            
            // Check if user category exists
            $stmt = $conn->prepare("SELECT category_id FROM user_categories WHERE category_id = :category_id");
            $stmt->bindParam(':category_id', $userCategoryId);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'User category not found'];
            }
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            
            // Set expiration (7 days from now)
            $dt = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
            $dt->modify('+7 days');
            $expiresAt = $dt->format('Y-m-d H:i:s');
            
            // Check for existing pending invitation
            $stmt = $conn->prepare("
                SELECT invitation_id FROM department_user_invitations 
                WHERE email = :email AND department_id = :department_id AND status = 'pending'
            ");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'A pending invitation already exists for this email and department'];
            }
            
            // Create invitation
            $stmt = $conn->prepare("
                INSERT INTO department_user_invitations (
                    department_id, invited_by, email, user_category_id, 
                    invitation_token, invitation_message, expires_at
                ) VALUES (
                    :department_id, :invited_by, :email, :user_category_id, 
                    :invitation_token, :invitation_message, :expires_at
                )
            ");
            
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->bindParam(':invited_by', $invitedBy);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_category_id', $userCategoryId);
            $stmt->bindParam(':invitation_token', $token);
            $stmt->bindParam(':invitation_message', $message);
            $stmt->bindParam(':expires_at', $expiresAt);
            
            $stmt->execute();
            $invitationId = $conn->lastInsertId();
            
            // Send invitation email
            $this->sendInvitationEmail($email, $token, $message);
            
            // Get inviter info
            $inviter = $this->getUserById($invitedBy);
            if ($inviter) {
                // Send confirmation email to inviter
                $this->sendInvitationConfirmationEmail($inviter, $email, $departmentId);
            }
            
            // Create notification for the inviter
            $notificationData = [
                'recipient_id' => $invitedBy,
                'title' => 'Invitation Sent',
                'message' => "Invitation sent to $email for department ID $departmentId",
                'notification_type' => 'invitation_sent'
            ];
            $this->notification->create($notificationData);
            
            return [
                'success' => true, 
                'invitation_id' => $invitationId,
                'token' => $token,
                'expires_at' => $expiresAt
            ];
            
        } catch (PDOException $e) {
            error_log("Invite user error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send invitation'];
        }
    }
    
    /**
     * Process invitation acceptance
     */
    public function acceptInvitation($token, $userData) {
        try {
            $conn = $this->db->connect();
            
            // Get invitation
            $stmt = $conn->prepare("
                SELECT * FROM department_user_invitations 
                WHERE invitation_token = :token 
                AND status = 'pending'
                AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$invitation) {
                return ['success' => false, 'message' => 'Invalid or expired invitation'];
            }
            
            // Check if email matches
            if (strtolower($userData['email']) !== strtolower($invitation['email'])) {
                return ['success' => false, 'message' => 'Email does not match invitation'];
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Create user account
            $userData['user_category_id'] = $invitation['user_category_id'];
            $createResult = $this->createUser($userData, $invitation['invited_by']);
            
            if (!$createResult['success']) {
                $conn->rollBack();
                return $createResult;
            }
            
            $userId = $createResult['user_id'];
            
            // Update invitation
            $stmt = $conn->prepare("
                UPDATE department_user_invitations 
                SET status = 'accepted', 
                    responded_at = NOW(),
                    created_user_id = :user_id
                WHERE invitation_id = :invitation_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':invitation_id', $invitation['invitation_id']);
            $stmt->execute();
            
            // Assign to department (handled in createUser through role assignment)
            
            // Commit transaction
            $conn->commit();
            
            // Notify department head
            $notificationData = [
                'recipient_id' => $invitation['invited_by'],
                'title' => 'Invitation Accepted',
                'message' => "{$userData['email']} has accepted your invitation and joined the department.",
                'notification_type' => 'invitation_accepted'
            ];
            $this->notification->create($notificationData);
            
            return ['success' => true, 'user_id' => $userId];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Accept invitation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process invitation'];
        }
    }
    
    /**
     * Send invitation email
     */
    private function sendInvitationEmail($email, $token, $message = '') {
        $inviteLink = $_ENV['APP_URL'] . '/accept-invitation?token=' . $token;
        
        $emailData = [
            'to' => $email,
            'subject' => 'Invitation to Join Our System',
            'template' => 'invitation_email',
            'data' => [
                'invite_link' => $inviteLink,
                'message' => $message,
                'expiration_days' => 7
            ]
        ];
        
        $this->mail->send($emailData);
    }
    
    /**
     * Send invitation confirmation email to inviter
     */
    private function sendInvitationConfirmationEmail($inviter, $inviteeEmail, $departmentId) {
        $emailData = [
            'to' => $inviter['email'],
            'subject' => 'Invitation Confirmation',
            'template' => 'invitation_confirmation',
            'data' => [
                'inviter_name' => $inviter['first_name'] . ' ' . $inviter['last_name'],
                'invitee_email' => $inviteeEmail,
                'department_id' => $departmentId,
                'invitation_date' => date('Y-m-d H:i')
            ]
        ];
        
        $this->mail->send($emailData);
    }

     public function getAllDepartments() {
        // Assuming you have a PDO connection $this->db or similar
        // Replace with your actual DB connection logic if needed
        //$db = $this->db ?? (new Database())->getConnection();
        //$stmt = $db->query("SELECT department_id, department_name FROM departments ORDER BY department_name ASC");
        ////return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}