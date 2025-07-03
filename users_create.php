<?php
$pageTitle = "Create User";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/users.php';

$userActions = new UserActions();
$db = new Database();

// Check permissions
if ($currentUser['role_id'] != 1 && $currentUser['role_id'] != 2) {
    header("Location: home.php");
    exit();
}

// Get departments for dropdown
$departments = [];
if ($currentUser['role_id'] == 1) { // Admin can see all departments
    $conn = $db->connect();
    $stmt = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else { // Department head can only see their department
    $departments = [
        [
            'department_id' => $currentUser['department_id'],
            'department_name' => $currentUser['department_name']
        ]
    ];
}

// Get roles based on user type
$roles = [];
if ($currentUser['role_id'] == 1) { // Admin can assign any role
    $conn = $db->connect();
    $stmt = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else { // Department head can only assign regular user role
    $roles = [
        [
            'role_id' => 3, // Assuming 3 is regular user role
            'role_name' => 'User'
        ]
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'password' => $_POST['password'],
        'phone' => $_POST['phone'] ?? '',
        'role_id' => $_POST['role_id'],
        'department_id' => $_POST['department_id'],
        'user_category_id' => $_POST['user_category_id'] ?? 1 // Default to 1 if not set
    ];
    
    $result = $userActions->createUser($userData, $currentUser['user_id']);
    
    if ($result['success']) {
        $_SESSION['success_message'] = "User created successfully!";
        header("Location: users.php");
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <!-- Page Content -->
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create New User</h2>
                    <p class="text-gray-600 dark:text-gray-400">Add a new user to the system</p>
                </div>
                <div>
                    <a href="users.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Users</span>
                    </a>
                </div>
            </div>
            
            <!-- Form Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form id="createUserForm" method="POST">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username*</label>
                                <input type="text" id="username" name="username" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter username">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email*</label>
                                <input type="email" id="email" name="email" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter email">
                            </div>
                            
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password*</label>
                                <input type="password" id="password" name="password" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter password">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                            </div>
                            
                            <!-- Confirm Password -->
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password*</label>
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Confirm password">
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-4">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name*</label>
                                <input type="text" id="first_name" name="first_name" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter first name">
                            </div>
                            
                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name*</label>
                                <input type="text" id="last_name" name="last_name" required 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter last name">
                            </div>
                            
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                    class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                    placeholder="Enter phone number">
                            </div>
                            
                            <!-- User Category (only for admin) -->
                            <?php if ($currentUser['role_id'] == 1): ?>
                                <div>
                                    <label for="user_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Category</label>
                                    <select id="user_category_id" name="user_category_id" 
                                        class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                        <?php
                                        $conn = $db->connect();
                                        $stmt = $conn->query("SELECT category_id, category_name FROM user_categories ORDER BY category_name");
                                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($categories as $category) {
                                            echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Role and Department Section -->
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Role -->
                        <div>
                            <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role*</label>
                            <select id="role_id" name="role_id" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department*</label>
                            <select id="department_id" name="department_id" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo $department['department_id']; ?>" <?php echo ($currentUser['role_id'] == 2 && $department['department_id'] == $currentUser['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($department['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Form submission feedback -->
                    <div id="formFeedback" class="hidden mt-4 p-4 rounded-md"></div>
                    
                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center">
                            <span id="submitText">Create User</span>
                            <span id="spinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const feedback = document.getElementById('formFeedback');
    
    if (password !== confirmPassword) {
        e.preventDefault();
        feedback.classList.remove('hidden');
        feedback.classList.add('bg-red-100', 'dark:bg-red-900', 'text-red-700', 'dark:text-red-300');
        feedback.textContent = 'Passwords do not match!';
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        feedback.classList.remove('hidden');
        feedback.classList.add('bg-red-100', 'dark:bg-red-900', 'text-red-700', 'dark:text-red-300');
        feedback.textContent = 'Password must be at least 8 characters!';
        return false;
    }
    
    // Show loading spinner
    document.getElementById('submitText').textContent = 'Creating...';
    document.getElementById('spinner').classList.remove('hidden');
});
</script>
