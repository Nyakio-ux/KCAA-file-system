<?php
$pageTitle = "Edit User";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/Database.php';

// Only allow admin and department heads to access
if ($currentUser['role_id'] != 1 && $currentUser['role_id'] != 2) {
    header("Location: home.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$userId = $_GET['id'];
$db = new Database();
$conn = $db->connect();

// Get user data
$stmt = $conn->prepare("
    SELECT u.*, ur.role_id, ur.department_id
    FROM users u
    LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
    WHERE u.user_id = :user_id
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit();
}

// Check if department head is trying to edit user from another department
if ($currentUser['role_id'] == 2 && $user['department_id'] != $currentUser['department_id']) {
    header("Location: users.php");
    exit();
}

// Get available roles and departments based on user's permissions
if ($currentUser['role_id'] == 1) {
    // Admin can assign any role and department
    $rolesStmt = $conn->query("SELECT role_id, role_name FROM roles");
    $deptStmt = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = TRUE");
} else {
    // Department head can only assign roles within their department
    $rolesStmt = $conn->prepare("
        SELECT r.role_id, r.role_name 
        FROM roles r
        WHERE r.role_id != 1  -- Can't assign admin role
        ORDER BY r.role_name
    ");
    $rolesStmt->execute();
    
    $deptStmt = $conn->prepare("
        SELECT department_id, department_name 
        FROM departments 
        WHERE department_id = :dept_id AND is_active = TRUE
    ");
    $deptStmt->bindParam(':dept_id', $currentUser['department_id']);
    $deptStmt->execute();
}

$roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user categories
$categoriesStmt = $conn->query("SELECT category_id, category_name FROM user_categories WHERE is_active = TRUE");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Validate input
        $required = ['first_name', 'last_name', 'role_id', 'department_id', 'user_category_id'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Update user
        $stmt = $conn->prepare("
            UPDATE users SET
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                user_category_id = :user_category_id,
                is_active = :is_active
            WHERE user_id = :user_id
        ");
        
        $stmt->bindValue(':first_name', $_POST['first_name']);
        $stmt->bindValue(':last_name', $_POST['last_name']);
        $stmt->bindValue(':phone', $_POST['phone'] ?? null);
        $stmt->bindValue(':user_category_id', $_POST['user_category_id']);
        $stmt->bindValue(':is_active', isset($_POST['is_active']), PDO::PARAM_BOOL);
        $stmt->bindValue(':user_id', $userId);
        
        $stmt->execute();

        // Update role if changed
        if ($user['role_id'] != $_POST['role_id'] || $user['department_id'] != $_POST['department_id']) {
            // Deactivate old role
            $stmt = $conn->prepare("
                UPDATE user_roles SET is_active = FALSE 
                WHERE user_id = :user_id AND is_active = TRUE
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            // Assign new role
            $stmt = $conn->prepare("
                INSERT INTO user_roles (
                    user_id, role_id, department_id, assigned_by, is_active
                ) VALUES (
                    :user_id, :role_id, :department_id, :assigned_by, TRUE
                )
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':role_id', $_POST['role_id']);
            $stmt->bindParam(':department_id', $_POST['department_id']);
            $stmt->bindParam(':assigned_by', $currentUser['user_id']);
            $stmt->execute();
        }

        // Handle password change if provided
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['confirm_password']) {
                throw new Exception("Passwords do not match");
            }

            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                UPDATE users SET password_hash = :password_hash 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        }

        $conn->commit();

        $_SESSION['success_message'] = "User updated successfully!";
        header("Location: users.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $errorMessage = $e->getMessage();
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
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edit User</h2>
                    <p class="text-gray-600 dark:text-gray-400">Update user information</p>
                </div>
                <a href="users.php" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Users</span>
                </a>
            </div>
            
            <?php if (isset($errorMessage)): ?>
                <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white">Personal Information</h3>
                            
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    value="<?php echo htmlspecialchars($user['first_name']); ?>">
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    value="<?php echo htmlspecialchars($user['last_name']); ?>">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                <input type="tel" id="phone" name="phone" 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Account Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white">Account Information</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <div class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <div class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                                <input type="password" id="password" name="password" 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current password</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        
                        <!-- Role and Permissions -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white">Role & Permissions</h3>
                            
                            <div>
                                <label for="user_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Category *</label>
                                <select id="user_category_id" name="user_category_id" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>" <?php echo $user['user_category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
                                <select id="role_id" name="role_id" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['role_id']; ?>" <?php echo $user['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($role['role_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department *</label>
                                <select id="department_id" name="department_id" required 
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['department_id']; ?>" <?php echo $user['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Active User</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
<script src="assets/js/main.js"></script>