<?php
session_start();
$pageTitle = "Department Users";
require_once 'includes/auth.php';
require_once 'includes/Database.php';
$db = new Database();
$conn = $db->connect();

$currentUser = $_SESSION['user_id'] ?? null;
if (!$currentUser) {
    header("Location: login.php");
    exit();
}

// Get current user's department from user_roles
$stmt = $conn->prepare("SELECT department_id FROM user_roles WHERE user_id = :user_id AND is_active = 1 LIMIT 1");
$stmt->bindParam(':user_id', $currentUser);
$stmt->execute();
$userRole = $stmt->fetch(PDO::FETCH_ASSOC);
$dept_id = $userRole['department_id'] ?? null;

if (!$dept_id) {
    header("Location: departments.php");
    exit();
}

// Fetch users in the same department (join users and user_roles)
$stmt = $conn->prepare("
    SELECT u.user_id, u.first_name, u.last_name, u.username, u.email, ur.role_id, r.role_name
    FROM users u
    INNER JOIN user_roles ur ON u.user_id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.role_id
    WHERE ur.department_id = :dept_id AND ur.is_active = 1
    ORDER BY u.first_name, u.last_name
");
$stmt->bindParam(':dept_id', $dept_id);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - KCAA Smart Files</title>
   
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <?php require_once 'userincludes/sidebar.php'; ?>
    <?php require_once 'userincludes/topnav.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Department Users</h2>
                    <p class="text-gray-600 dark:text-gray-400">View users in your department</p>
                </div>
            </div>

            <!-- User List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://via.placeholder.com/40" alt="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['role_name'] ?? 'N/A'); ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php require_once 'includes/scripts.php'; ?>
</body>
</html>