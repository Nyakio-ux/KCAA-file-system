<?php
$pageTitle = "My Profile";
require_once 'userincludes/header.php';

require_once 'includes/auth.php';
require_once 'includes/dashboard.php';
$dashboard = new Dashboard();

// Get user profile data
$profileUserId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUser['user_id'];
$profileData = $dashboard->getUserProfile($profileUserId);

// Check if user exists
if (!$profileData || !isset($profileData['user'])) {
    header('Location: home.php');
    exit;
}

$user = $profileData['user'];
$roles = $profileData['roles'] ?? [];
$categories = $profileData['categories'] ?? [];

$primaryRole = $roles[0] ?? [];
$departmentName = $primaryRole['department_name'] ?? 'No department';
$roleName = $primaryRole['role_name'] ?? 'No role';
$userCategory = $categories[0]['category_name'] ?? 'No category';


if ($currentUser['role_id'] != 1 && $currentUser['user_id'] != $profileUserId) {
    header('Location: home.php');
    exit;
}

// Get user activities
$activities = $dashboard->getUserActivityLog($user['user_id'], 5);
$activities = $activities['success'] ? $activities['activities'] : [];
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <!-- Profile Content -->
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">User Profile</h2>
                    <p class="text-gray-600 dark:text-gray-400">Manage and view user information</p>
                </div>
                
                <?php if ($currentUser['user_id'] == $user['user_id'] || $currentUser['role_id'] == 1): ?>
                    <a href="profile_edit.php?id=<?php echo $user['user_id']; ?>" 
                       class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex flex-col items-center">
                        <div class="relative mb-4">
                            <img src="assets/images/default-avatar.png" class="w-32 h-32 rounded-full object-cover" alt="Profile Image">
                            <?php if ($user['is_active'] ?? false): ?>
                                <span class="absolute bottom-0 right-0 bg-green-500 rounded-full w-4 h-4 border-2 border-white dark:border-gray-800"></span>
                            <?php else: ?>
                                <span class="absolute bottom-0 right-0 bg-red-500 rounded-full w-4 h-4 border-2 border-white dark:border-gray-800"></span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white text-center">
                            <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-center">@<?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                        
                        <div class="flex flex-wrap justify-center gap-2 mt-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300">
                                <?php echo htmlspecialchars($roleName); ?>
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-300">
                                <?php echo htmlspecialchars($departmentName); ?>
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                <?php echo htmlspecialchars($userCategory); ?>
                            </span>
                        </div>
                        
                        <div class="flex space-x-4 mt-6">
                            <a href="mailto:<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="text-gray-500 hover:text-primary-600 dark:hover:text-primary-400">
                                <i class="fas fa-envelope text-xl"></i>
                            </a>
                            <?php if (!empty($user['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>" class="text-gray-500 hover:text-primary-600 dark:hover:text-primary-400">
                                    <i class="fas fa-phone text-xl"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Contact Information</h4>
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-3 text-gray-400 dark:text-gray-500"></i>
                                <span class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone mr-3 text-gray-400 dark:text-gray-500"></i>
                                <span class="text-gray-800 dark:text-white">
                                    <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?>
                                </span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-calendar-alt mr-3 text-gray-400 dark:text-gray-500"></i>
                                <span class="text-gray-800 dark:text-white">
                                    Joined <?php echo !empty($user['created_at']) ? formatDate($user['created_at']) : 'Unknown'; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Profile Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Profile Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($user['first_name'] ?? ''); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($user['last_name'] ?? ''); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($user['username'] ?? ''); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo ($user['is_active'] ?? false) ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300'; ?>">
                                        <?php echo ($user['is_active'] ?? false) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Category</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($userCategory); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($departmentName); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Log -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white">Recent Activity</h3>
                        </div>
                        <div class="p-6">
                            <?php if (empty($activities)): ?>
                                <div class="text-center py-8">
                                    <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-history text-3xl"></i>
                                    </div>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recent activity</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">User activity will appear here</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="flex items-start p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                <i class="fas fa-<?php echo getActivityIcon($activity['activity_type'] ?? ''); ?>"></i>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h4 class="text-sm font-medium text-gray-800 dark:text-white">
                                                        <?php echo ucfirst($activity['activity_type'] ?? 'activity'); ?>
                                                        <?php if (($activity['activity_type'] ?? '') === 'file_access' && isset($activity['original_name'])): ?>
                                                            - <?php echo htmlspecialchars($activity['original_name']); ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        <?php echo !empty($activity['activity_time']) ? formatDate($activity['activity_time']) : 'Unknown time'; ?>
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <?php echo $activity['ip_address'] ?? 'Unknown IP'; ?> • <?php echo $activity['user_agent'] ?? 'Unknown device'; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="user_activity.php?id=<?php echo $user['user_id']; ?>" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">
                                        View all activity
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Helper functions
function formatDate($dateString) {
    return date('M j, Y g:i A', strtotime($dateString));
}

function getActivityIcon($activityType) {
    switch (strtolower($activityType)) {
        case 'login': return 'sign-in-alt';
        case 'logout': return 'sign-out-alt';
        case 'file_upload': return 'file-upload';
        case 'file_access': return 'file-download';
        case 'file_share': return 'share-alt';
        case 'profile_update': return 'user-edit';
        default: return 'history';
    }
}
?>

