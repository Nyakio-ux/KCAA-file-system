<?php
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

// Get category ID from URL
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category data
$stmt = $conn->prepare("SELECT * FROM file_categories WHERE category_id = :category_id");
$stmt->bindParam(':category_id', $categoryId);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: categories.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name']);
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    if (empty($categoryName)) {
        $error = "Category name is required";
    } else {
        // Check if category name already exists (excluding current category)
        $stmt = $conn->prepare("SELECT category_id FROM file_categories WHERE category_name = :category_name AND category_id != :category_id");
        $stmt->bindParam(':category_name', $categoryName);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Category name already exists";
        } else {
            // Update category
            $stmt = $conn->prepare("UPDATE file_categories SET category_name = :category_name, description = :description, is_active = :is_active WHERE category_id = :category_id");
            $stmt->bindParam(':category_name', $categoryName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->bindParam(':category_id', $categoryId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Category updated successfully!";
                header("Location: categories.php");
                exit();
            } else {
                $error = "Failed to update category";
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
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Category</h2>
                    <p class="text-gray-600 dark:text-gray-400">Update category information</p>
                </div>
                <div>
                    <a href="categories.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
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
                                value="<?php echo htmlspecialchars($category['category_name']); ?>">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" <?php echo $category['is_active'] ? 'checked' : ''; ?>
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active (visible for file uploads)
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center">
                                <span id="submitText">Update Category</span>
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
    document.getElementById('submitText').textContent = 'Updating...';
    document.getElementById('spinner').classList.remove('hidden');
});
</script>
