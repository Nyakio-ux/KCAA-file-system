<?php
$pageTitle = "View User";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/Database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$userId = $_GET['id'];
$db = new Database();
$conn = $db->connect();

// Get user data with role and department info
$stmt = $conn->prepare("
    SELECT 
        u.*, 
        uc.category_name as user_category,
        r.role_name, 
        d.department_name,
        CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
        u.created_at as account_created
    FROM users u
    LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
    LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
    LEFT JOIN roles r ON ur.role_id = r.role_id
    LEFT JOIN departments d ON ur.department_id = d.department_id
    LEFT JOIN users creator ON ur.assigned_by = creator.user_id
    WHERE u.user_id = :user_id
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit();
}

// Check if department head is trying to view user from another department
if ($currentUser['role_id'] == 2 && $user['department_id'] != $currentUser['department_id']) {
    header("Location: users.php");
    exit();
}

// Get user's login history
$stmt = $conn->prepare("
    SELECT login_time, ip_address 
    FROM login_logs 
    WHERE user_id = :user_id 
    ORDER BY login_time DESC 
    LIMIT 5
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$loginHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">User Details</h2>
                    <p class="text-gray-600 dark:text-gray-400">View user information and activity</p>
                </div>
                <div class="flex space-x-2">
                    <?php if ($currentUser['role_id'] == 1 || ($currentUser['role_id'] == 2 && $user['department_id'] == $currentUser['department_id'])): ?>
                        <a href="users_edit.php?id=<?php echo $userId; ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                            <i class="fas fa-edit"></i>
                            <span>Edit User</span>
                        </a>
                    <?php endif; ?>
                    <a href="users.php" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Users</span>
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- User Profile -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex flex-col items-center">
                        <div class="h-24 w-24 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-3xl font-bold text-primary-600 dark:text-primary-300 mb-4">
                            <?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($user['user_category']); ?></p>
                        
                        <div class="mt-4 w-full space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="font-medium <?php echo $user['is_active'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Role:</span>
                                <span class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['role_name']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Department:</span>
                                <span class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['department_name']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Account Created:</span>
                                <span class="font-medium text-gray-800 dark:text-white"><?php echo date('M j, Y', strtotime($user['account_created'])); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Created By:</span>
                                <span class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['created_by_name']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">Contact Information</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Username</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                            <p class="font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">Recent Activity</h3>
                    
                    <?php if (empty($loginHistory)): ?>
                        <p class="text-gray-500 dark:text-gray-400">No recent login activity</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($loginHistory as $login): ?>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">Logged in</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            IP: <?php echo htmlspecialchars($login['ip_address']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="assets/js/main.js"></script>