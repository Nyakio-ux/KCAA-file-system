<?php
$pageTitle = "Role Management";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/Database.php';

// Only allow admin access
if ($currentUser['role_id'] != 1) {
    header("Location: unauthorized.php");
    exit();
}

$db = new Database();
$conn = $db->connect();

// Get role ID from URL
$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch role data
$stmt = $conn->prepare("SELECT * FROM roles WHERE role_id = :role_id");
$stmt->bindParam(':role_id', $roleId);
$stmt->execute();
$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    header("Location: roles.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = trim($_POST['role_name']);
    $description = trim($_POST['description'] ?? '');
    
    // Validate
    if (empty($roleName)) {
        $error = "Role name is required";
    } else {
        // Check if role name already exists (excluding current role)
        $stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = :role_name AND role_id != :role_id");
        $stmt->bindParam(':role_name', $roleName);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Role name already exists";
        } else {
            // Update role
            $stmt = $conn->prepare("UPDATE roles SET role_name = :role_name, description = :description WHERE role_id = :role_id");
            $stmt->bindParam(':role_name', $roleName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':role_id', $roleId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Role updated successfully!";
                header("Location: roles.php");
                exit();
            } else {
                $error = "Failed to update role";
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
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Role</h2>
                    <p class="text-gray-600 dark:text-gray-400">Update role information</p>
                </div>
                <div>
                    <a href="roles.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Roles</span>
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
                        <!-- Role Name -->
                        <div>
                            <label for="role_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name*</label>
                            <input type="text" id="role_name" name="role_name" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                value="<?php echo htmlspecialchars($role['role_name']); ?>">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($role['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center">
                                <span id="submitText">Update Role</span>
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
