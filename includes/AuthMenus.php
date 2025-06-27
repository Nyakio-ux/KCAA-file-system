<?php
require_once 'auth.php';
require_once 'dashboard.php';
require_once 'database.php';

class AuthMenus {
    private $db;
    private $auth;
    private $dashboard;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->dashboard = new Dashboard();
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
     * Get all menus based on user role
     */
    public function getMenus($userId) {
        $user = $this->getUserById($userId);
        if (!$user) {
            return [];
        }
        
        $roleId = $user['role_id'] ?? null;
        $departmentId = $user['department_id'] ?? null;
        
        $commonMenus = $this->getCommonMenus();
        
        switch ($roleId) {
            case 1: // Admin
                return array_merge($commonMenus, $this->getAdminMenus());
            case 2: // Department Head
                return array_merge($commonMenus, $this->getDepartmentHeadMenus($departmentId));
            default: // Regular User
                return array_merge($commonMenus, $this->getUserMenus($departmentId));
        }
    }
    
    /**
     * Menus common to all users
     */
    private function getCommonMenus() {
        return [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'url' => 'dashboard.php',
                'permission' => true
            ],
            'files' => [
                'title' => 'My Files',
                'icon' => 'fas fa-file',
                'url' => 'myfiles.php',
                'permission' => true
            ],
            'shared' => [
                'title' => 'Shared Files',
                'icon' => 'fas fa-share-alt',
                'url' => 'sharedfiles.php',
                'permission' => true
            ],
            'profile' => [
                'title' => 'My Profile',
                'icon' => 'fas fa-user',
                'url' => 'profile.php',
                'permission' => true
            ],
            'notifications' => [
                'title' => 'Notifications',
                'icon' => 'fas fa-bell',
                'url' => 'notifications.php',
                'permission' => true
            ],
            'logout' => [
                'title' => 'Logout',
                'icon' => 'fas fa-sign-out-alt',
                'url' => 'logout.php',
                'permission' => true
            ]
        ];
    }
    
    /**
     * Admin-only menus
     */
    private function getAdminMenus() {
        return [
            'users' => [
                'title' => 'User Management',
                'icon' => 'fas fa-users',
                'url' => 'users.php',
                'permission' => true,
                'submenus' => [
                    'all_users' => [
                        'title' => 'All Users',
                        'url' => 'users.php'
                    ],
                    'create_user' => [
                        'title' => 'Create User',
                        'url' => 'users_create.php'
                    ],
                    'user_roles' => [
                        'title' => 'User Roles',
                        'url' => 'user_roles.php'
                    ]
                ]
            ],
            'departments' => [
                'title' => 'Departments',
                'icon' => 'fas fa-building',
                'url' => 'departments.php',
                'permission' => true
            ],
            'categories' => [
                'title' => 'Categories',
                'icon' => 'fas fa-tags',
                'url' => 'categories.php',
                'permission' => true,
                'submenus' => [
                    'file_categories' => [
                        'title' => 'File Categories',
                        'url' => 'file_categories.php'
                    ],
                    'user_categories' => [
                        'title' => 'User Categories',
                        'url' => 'user_categories.php'
                    ]
                ]
            ],
            'reports' => [
                'title' => 'Reports',
                'icon' => 'fas fa-chart-bar',
                'url' => 'reports.php',
                'permission' => true,
                'submenus' => [
                    'user_activity' => [
                        'title' => 'User Activity',
                        'url' => 'reports_user_activity.php'
                    ],
                    'file_activity' => [
                        'title' => 'File Activity',
                        'url' => 'reports_file_activity.php'
                    ],
                    'system_logs' => [
                        'title' => 'System Logs',
                        'url' => 'reports_system_logs.php'
                    ]
                ]
            ],
            'settings' => [
                'title' => 'System Settings',
                'icon' => 'fas fa-cog',
                'url' => 'settings.php',
                'permission' => true
            ]
        ];
    }
    
    /**
     * Department Head menus
     */
    private function getDepartmentHeadMenus($departmentId) {
        return [
            'department' => [
                'title' => 'Department',
                'icon' => 'fas fa-users-cog',
                'url' => 'department.php?id='.$departmentId,
                'permission' => true,
                'submenus' => [
                    'members' => [
                        'title' => 'Members',
                        'url' => 'department_members.php?id='.$departmentId
                    ],
                    'invitations' => [
                        'title' => 'Invitations',
                        'url' => 'department_invitations.php?id='.$departmentId
                    ],
                    'settings' => [
                        'title' => 'Settings',
                        'url' => 'department_settings.php?id='.$departmentId
                    ]
                ]
            ],
            'approvals' => [
                'title' => 'Approvals',
                'icon' => 'fas fa-check-circle',
                'url' => 'approvals.php',
                'permission' => true,
                'submenus' => [
                    'pending' => [
                        'title' => 'Pending',
                        'url' => 'approvals_pending.php'
                    ],
                    'approved' => [
                        'title' => 'Approved',
                        'url' => 'approvals_approved.php'
                    ],
                    'rejected' => [
                        'title' => 'Rejected',
                        'url' => 'approvals_rejected.php'
                    ]
                ]
            ],
            'department_reports' => [
                'title' => 'Reports',
                'icon' => 'fas fa-chart-pie',
                'url' => 'department_reports.php?id='.$departmentId,
                'permission' => true
            ]
        ];
    }
    
    /**
     * Regular user menus
     */
    private function getUserMenus($departmentId) {
        return [
            'department' => [
                'title' => 'My Department',
                'icon' => 'fas fa-users',
                'url' => 'department.php?id='.$departmentId,
                'permission' => true
            ],
            'upload' => [
    'title' => 'Upload File',
    'icon' => 'fas fa-upload',
    'url' => '#', 
    'permission' => true,
    'attributes' => [
        'data-toggle' => 'modal',
        'data-target' => '#uploadModal'
    ]
]
        ];
    }
    
    /**
     * Check if user has permission to access a page
     */
    public function checkPermission($userId, $page) {
        $menus = $this->getMenus($userId);
        
        // Check main menus
        foreach ($menus as $menu) {
            if (isset($menu['url']) && basename($menu['url']) === $page) {
                return $menu['permission'] ?? false;
            }
            
            // Check submenus
            if (isset($menu['submenus'])) {
                foreach ($menu['submenus'] as $submenu) {
                    if (basename($submenu['url']) === $page) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}