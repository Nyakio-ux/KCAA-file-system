<?php
$pageTitle = "Create Department";
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

// Get all users for department head dropdown
$stmt = $conn->query("SELECT user_id, CONCAT(first_name, ' ', last_name) as full_name FROM users ORDER BY first_name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departmentName = trim($_POST['department_name']);
    $departmentCode = trim($_POST['department_code']);
    $description = trim($_POST['description'] ?? '');
    $headUserId = !empty($_POST['head_user_id']) ? (int)$_POST['head_user_id'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    $errors = [];
    if (empty($departmentName)) {
        $errors[] = "Department name is required";
    }
    if (empty($departmentCode)) {
        $errors[] = "Department code is required";
    }
    
    if (empty($errors)) {
        // Check if department name or code already exists
        $stmt = $conn->prepare("SELECT department_id FROM departments WHERE department_name = :name OR department_code = :code");
        $stmt->bindParam(':name', $departmentName);
        $stmt->bindParam(':code', $departmentCode);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error = "Department name or code already exists";
        } else {
            // Insert new department
            $stmt = $conn->prepare("
                INSERT INTO departments (
                    department_name, department_code, 
                    description, head_user_id, 
                    is_active
                ) VALUES (
                    :name, :code,
                    :description, :head_user_id,
                    :is_active
                )
            ");
            
            $stmt->bindParam(':name', $departmentName);
            $stmt->bindParam(':code', $departmentCode);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':head_user_id', $headUserId);
            $stmt->bindParam(':is_active', $isActive);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Department created successfully!";
                header("Location: departments.php");
                exit();
            } else {
                $error = "Failed to create department";
            }
        }
    } else {
        $error = implode("<br>", $errors);
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
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create New Department</h2>
                    <p class="text-gray-600 dark:text-gray-400">Add a new organizational department</p>
                </div>
                <div>
                    <a href="departments.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Departments</span>
                    </a>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="space-y-6">
                        <!-- Department Name -->
                        <div>
                            <label for="department_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Name*</label>
                            <input type="text" id="department_name" name="department_name" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                placeholder="Enter department name">
                        </div>
                        
                        <!-- Department Code -->
                        <div>
                            <label for="department_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Code*</label>
                            <input type="text" id="department_code" name="department_code" required 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                placeholder="Enter short code (e.g., HR, FIN, IT)">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                                placeholder="Enter department description"></textarea>
                        </div>
                        
                        <!-- Department Head -->
                        <div>
                            <label for="head_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department Head</label>
                            <select id="head_user_id" name="head_user_id" 
                                class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                <option value="">Select Department Head</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" checked
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active (visible in the system)
                            </label>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center">
                                <span id="submitText">Create Department</span>
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

<script>
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('submitText').textContent = 'Creating...';
    document.getElementById('spinner').classList.remove('hidden');
});
</script>
<script src="assets/js/main.js"></script>