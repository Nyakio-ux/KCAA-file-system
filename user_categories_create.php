<?php
$pageTitle = "Create User Category";
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name']);
    $description = trim($_POST['description'] ?? '');
    $canUpload = isset($_POST['can_upload_files']) ? 1 : 0;
    $canShare = isset($_POST['can_share_files']) ? 1 : 0;
    $canApprove = isset($_POST['can_approve_files']) ? 1 : 0;
    $canReview = isset($_POST['can_review_files']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($categoryName)) {
        $error = "Category name is required";
    } else {
        // Check if category already exists
        $stmt = $conn->prepare("SELECT category_id FROM user_categories WHERE category_name = :category_name");
        $stmt->bindParam(':category_name', $categoryName);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Category name already exists";
        } else {
            // Insert new category
            $stmt = $conn->prepare("
                INSERT INTO user_categories (
                    category_name, description, 
                    can_upload_files, can_share_files, 
                    can_approve_files, can_review_files,
                    is_active, created_by
                ) VALUES (
                    :category_name, :description,
                    :can_upload, :can_share,
                    :can_approve, :can_review,
                    :is_active, :created_by
                )
            ");
            
            $stmt->bindParam(':category_name', $categoryName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':can_upload', $canUpload);
            $stmt->bindParam(':can_share', $canShare);
            $stmt->bindParam(':can_approve', $canApprove);
            $stmt->bindParam(':can_review', $canReview);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->bindParam(':created_by', $currentUser['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "User category created successfully!";
                header("Location: user_categories.php");
                exit();
            } else {
                $error = "Failed to create user category";
            }
        }
    }
}
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create New User Category</h2>
                    <p class="text-gray-600 dark:text-gray-400">Define user permissions and access levels</p>
                </div>
                <div>
                    <a href="user_categories.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Categories</span>
                    </a>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="space-y-6">
                        <!-- Category Name -->
                        <div>
                            <label for="category_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Name*</label>
                            <input type="text" id="category_name" name="category_name" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                placeholder="Enter category name">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea id="description" name="description" rows="2" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                placeholder="Enter category description"></textarea>
                        </div>
                        
                        <!-- Permissions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input id="can_upload_files" name="can_upload_files" type="checkbox" checked
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                    <label for="can_upload_files" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Can upload files
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="can_share_files" name="can_share_files" type="checkbox"
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                    <label for="can_share_files" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Can share files
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="can_approve_files" name="can_approve_files" type="checkbox"
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                    <label for="can_approve_files" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Can approve files
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="can_review_files" name="can_review_files" type="checkbox" checked
                                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                    <label for="can_review_files" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Can review files
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" checked
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active (available for user assignment)
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center">
                                <span id="submitText">Create User Category</span>
                                <span id="spinner" class="hidden ml-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
<script src="assets/js/main.js"></script>
<script>
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('submitText').textContent = 'Creating...';
    document.getElementById('spinner').classList.remove('hidden');
});
</script>