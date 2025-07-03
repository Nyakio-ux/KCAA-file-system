<?php
$pageTitle = "User Categories";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/Database.php';

// Only allow admin access
if ($currentUser['role_id'] != 1) {
    header("Location: home.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryId = $_GET['delete'];
    
    // Check if category is in use
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE user_category_id = :category_id");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $_SESSION['error_message'] = "Cannot delete category - it is currently assigned to users.";
    } else {
        $stmt = $conn->prepare("DELETE FROM user_categories WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $categoryId);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User category deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete user category.";
        }
    }
    header("Location: user_categories.php");
    exit();
}

// Get all user categories with creator info
$stmt = $conn->query("
    SELECT uc.*, CONCAT(u.first_name, ' ', u.last_name) as creator_name 
    FROM user_categories uc
    JOIN users u ON uc.created_by = u.user_id
    ORDER BY uc.category_name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for messages
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">User Categories</h2>
                    <p class="text-gray-600 dark:text-gray-400">Manage user categories and permissions</p>
                </div>
                <a href="user_categories_create.php" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                    <i class="fas fa-plus"></i>
                    <span>Add User Category</span>
                </a>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Permissions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created By</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No user categories found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($category['description'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <?php if ($category['can_upload_files']): ?>
                                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">Upload</span>
                                                <?php endif; ?>
                                                <?php if ($category['can_share_files']): ?>
                                                    <span class="px-2 py-1 text-xs rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Share</span>
                                                <?php endif; ?>
                                                <?php if ($category['can_approve_files']): ?>
                                                    <span class="px-2 py-1 text-xs rounded bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">Approve</span>
                                                <?php endif; ?>
                                                <?php if ($category['can_review_files']): ?>
                                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">Review</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $category['is_active'] ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300'; ?>">
                                                <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($category['creator_name']); ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo date('M j, Y', strtotime($category['created_at'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a href="user_categories_edit.php?id=<?php echo $category['category_id']; ?>" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="user_categories.php?delete=<?php echo $category['category_id']; ?>" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this user category?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="assets/js/main.js"></script>