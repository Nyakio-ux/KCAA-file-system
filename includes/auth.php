<?php
require_once 'Database.php';
require_once 'mail.php';
require_once  'notifications.php';
require_once 'config.php';

class Auth {
    private $db;
    private $mail;
    private $notification;

    public function __construct() {
        $this->db = new Database();
        $this->mail = new Mail();
        $this->notification = new Notification();
    }

    /**
     * Authenticate user with username/email and password
     */
    public function login($identifier, $password, $rememberMe = false) {
        try {
            $conn = $this->db->connect();
            
            // Check if user exists by username or email
            $stmt = $conn->prepare("
                SELECT u.*, ur.role_id, r.role_name, ur.department_id, d.department_name
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
                WHERE (u.username = :identifier OR u.email = :identifier) AND u.is_active = TRUE
            ");
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'No account found with that username/email'];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                $this->logFailedLoginAttempt($user['user_id']);
                return ['success' => false, 'message' => 'Invalid username/email or password'];
            }
            
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $this->updatePasswordHash($user['user_id'], $password);
            }
            
            $this->resetFailedLoginAttempts($user['user_id']);
            
            $this->setUserSession($user);
            
            if ($rememberMe) {
                $this->setRememberMeCookie($user['user_id']);
            }
            
            $this->logLogin($user['user_id']);
            
            return ['success' => true, 'user' => $user];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
    
    /**
     * Logout user by destroying session and cookies
     */
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        session_write_close();
        
        if (isset($_COOKIE['remember_token'])) {
            $this->deleteRememberMeToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    /**
     * Process password reset request
     */
    public function forgotPassword($usernameOrEmail) {
        try {
            $conn = $this->db->connect();
        
            $stmt = $conn->prepare("
                SELECT user_id, email, first_name, last_name 
                FROM users 
                WHERE (username = :identifier OR email = :identifier) AND is_active = TRUE
            ");
            $stmt->bindParam(':identifier', $usernameOrEmail);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'No account found with that username/email'];
            }
            
            $token = bin2hex(random_bytes(32));
            $dt = new DateTime('now', new DateTimeZone('Africa/Nairobi'));
            $dt->modify('+1 hour');
            $expiresAt = $dt->format('Y-m-d H:i:s');
            
            $stmt = $conn->prepare("
                INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                VALUES (:user_id, :token, :expires_at)
                ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)
            ");
            $stmt->bindParam(':user_id', $user['user_id']);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->execute();
            
            // Send password reset email
            $resetLink = $_ENV['APP_URL'] . '/resetpassword.php?token=' . $token . '&email=' . urlencode($user['email']);
            
            $emailData = [
                'to' => $user['email'],
                'subject' => 'Password Reset Request',
                'template' => 'password_reset',
                'data' => [
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'reset_link' => $resetLink,
                    'expiry_time' => '1 hour'
                ]
            ];
            
            $this->mail->send($emailData);
            
            return ['success' => true, 'message' => 'Password reset instructions sent to your email'];
            
        } catch (PDOException $e) {
            error_log("Forgot password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while processing your request'];
        }
    }
    
    /**
     * Reset user password with token
     */
    public function resetPassword($token, $email, $newPassword) {
        try {
            $conn = $this->db->connect();
            
            // Validate token
            $stmt = $conn->prepare("
                SELECT prt.*, u.user_id, u.email 
                FROM password_reset_tokens prt
                JOIN users u ON prt.user_id = u.user_id
                WHERE prt.token = :token AND u.email = :email AND prt.expires_at > NOW() AND u.is_active = TRUE
            ");
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tokenData) {
                return ['success' => false, 'message' => 'Invalid or expired reset token'];
            }
            
            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users SET password_hash = :password_hash 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':user_id', $tokenData['user_id']);
            $stmt->execute();
            
            // Delete used token
            $stmt = $conn->prepare("
                DELETE FROM password_reset_tokens WHERE token = :token
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            // Send password changed notification
            $user = $this->getUserById($tokenData['user_id']);
            
            // Send password changed email
            $emailData = [
                'to' => $user['email'],
                'subject' => 'Your Password Was Changed',
                'template' => 'password_changed',
                'data' => [
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'email' => $user['email']
                ]
            ];
            $this->mail->send($emailData);

            $notificationData = [
                'recipient_id' => $tokenData['user_id'],
                'title' => 'Password Changed',
                'message' => 'Your password was successfully changed.',
                'notification_type' => 'password_changed'
            ];
            
            $this->notification->create($notificationData);
            
            return ['success' => true, 'message' => 'Password reset successfully'];
            
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while resetting your password'];
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        session_start();
        
        // Check session
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberMeToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        
        if (isset($_SESSION['user_id'])) {
            return $this->getUserById($_SESSION['user_id']);
        }
        
        if (isset($_COOKIE['remember_token'])) {
            $userId = $this->validateRememberMeToken($_COOKIE['remember_token']);
            if ($userId) {
                return $this->getUserById($userId);
            }
        }
        
        return null;
    }
    
    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $conn = $this->db->connect();
            
            // Get current password hash
            $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users SET password_hash = :password_hash 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            // Send password changed notification
            $notificationData = [
                'recipient_id' => $userId,
                'title' => 'Password Changed',
                'message' => 'Your password was successfully changed.',
                'notification_type' => 'password_changed'
            ];
            
            $this->notification->create($notificationData);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing your password'];
        }
    }
    
    // ===== PRIVATE HELPER METHODS ===== //
    
    private function setUserSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['department_id'] = $user['department_id'];
        $_SESSION['department_name'] = $user['department_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }
    
    private function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        try {
            $conn = $this->db->connect();
            
            // Store token in database
            $stmt = $conn->prepare("
                INSERT INTO remember_me_tokens (user_id, token, expires_at) 
                VALUES (:user_id, :token, :expires_at)
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token', $token);
            $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expires));
            $stmt->execute();
            
            // Set cookie
            setcookie('remember_token', $token, $expires, '/', '', true, true);
            
        } catch (PDOException $e) {
            error_log("Remember me token error: " . $e->getMessage());
        }
    }
    
    private function validateRememberMeToken($token) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT rmt.user_id, u.*, ur.role_id, r.role_name, ur.department_id, d.department_name
                FROM remember_me_tokens rmt
                JOIN users u ON rmt.user_id = u.user_id
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
                WHERE rmt.token = :token AND rmt.expires_at > NOW() AND u.is_active = TRUE
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->setUserSession($user);
                return $user['user_id'];
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Remember me validation error: " . $e->getMessage());
            return false;
        }
    }
    
    private function deleteRememberMeToken($token) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                DELETE FROM remember_me_tokens WHERE token = :token
            ");
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Delete remember token error: " . $e->getMessage());
        }
    }
    
    private function getUserById($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT u.*, ur.role_id, r.role_name, ur.department_id, d.department_name
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
                WHERE u.user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    private function logLogin($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                INSERT INTO login_logs (user_id, ip_address, user_agent, login_time)
                VALUES (:user_id, :ip_address, :user_agent, NOW())
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Login log error: " . $e->getMessage());
        }
    }
    
    private function logFailedLoginAttempt($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                INSERT INTO failed_login_attempts (user_id, ip_address, attempt_time)
                VALUES (:user_id, :ip_address, NOW())
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->execute();
            
            // Check if account should be locked
            $this->checkAccountLock($userId);
            
        } catch (PDOException $e) {
            error_log("Failed login log error: " . $e->getMessage());
        }
    }
    
    private function resetFailedLoginAttempts($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                DELETE FROM failed_login_attempts WHERE user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Reset failed attempts error: " . $e->getMessage());
        }
    }
    
    private function checkAccountLock($userId) {
        try {
            $conn = $this->db->connect();
            
            // Count recent failed attempts
            $stmt = $conn->prepare("
                SELECT COUNT(*) as attempt_count 
                FROM failed_login_attempts 
                WHERE user_id = :user_id AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Lock account if too many attempts
            if ($result['attempt_count'] >= 5) {
                $stmt = $conn->prepare("
                    UPDATE users SET is_active = FALSE WHERE user_id = :user_id
                ");
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();
                
                // Notify admin or user about account lock
                $this->notifyAccountLock($userId);
            }
            
        } catch (PDOException $e) {
            error_log("Account lock check error: " . $e->getMessage());
        }
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    private function notifyAccountLock($userId) {
        $user = $this->getUserById($userId);
        
        if ($user) {
            // Send email notification
            $emailData = [
                'to' => $user['email'],
                'subject' => 'Account Locked Due to Multiple Failed Login Attempts',
                'template' => 'account_locked',
                'data' => [
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'unlock_link' => $_ENV['APP_URL'] . '/contact-support'
                ]
            ];
            
            $this->mail->send($emailData);
            
            // Create system notification for admin
            $notificationData = [
                'recipient_id' => 1, 
                'title' => 'Account Locked',
                'message' => 'User ' . $user['username'] . ' account was locked due to multiple failed login attempts.',
                'notification_type' => 'account_locked'
            ];
            
            $this->notification->create($notificationData);
        }
    }
    
    private function updatePasswordHash($userId, $password) {
        try {
            $conn = $this->db->connect();
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users SET password_hash = :password_hash 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Password hash update error: " . $e->getMessage());
        }
    }

}