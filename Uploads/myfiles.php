<?php
$pageTitle = "File Management";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/files.php';

$fileActions = new FileActions();
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$stmt = $conn->prepare("SELECT * FROM files2 ORDER BY uploaded_at DESC");
$stmt->execute();
$allFiles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Set up filters based on user role
$filters = [];
if ($currentUser['role_id'] != 1) { // Not admin
    $filters['department_id'] = $currentUser['department_id'];
}

// Add search filter if provided
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

// Add status filter if provided
if (!empty($_GET['status'])) {
    $filters['status_id'] = (int)$_GET['status'];
}

// Get files data
$filesData = $fileActions->getAllFiles($currentPage, $perPage, $filters);
$files = $filesData['files'];
$totalFiles = $filesData['total'];
$totalPages = $filesData['total_pages'];

// Get workflow statuses for filter dropdown
$workflowStatuses = $fileActions->getWorkflowStatuses();
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <!-- File Management Content -->
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">File Management</h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        <?php if ($currentUser['role_id'] == 1): ?>
                            All files in the system
                        <?php else: ?>
                            Files in <?php echo htmlspecialchars($currentUser['department_name']); ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Search Box -->
                    <div class="relative">
                        <form method="GET" action="files.php" class="flex items-center">
                            <input type="text" name="search" placeholder="Search files..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                                   class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </form>
                    </div>
                    
                    <!-- Status Filter Dropdown -->
                    <div class="relative">
                        <form method="GET" action="files.php" class="flex items-center">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <select name="status" onchange="this.form.submit()"
                                    class="appearance-none pl-3 pr-8 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Statuses</option>
                                <?php foreach ($workflowStatuses as $status): ?>
                                    <option value="<?php echo $status['status_id']; ?>"
                                        <?php echo (isset($_GET['status']) && $_GET['status'] == $status['status_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
                        </form>
                    </div>
                    
                    <!-- Upload Button -->
                    <button class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200"
                            data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload"></i>
                        <span>Upload File</span>
                    </button>
                </div>
            </div>
            
            <!-- Files Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uploaded By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (empty($files)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No files found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($files as $file): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                    <i class="fas fa-file"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-800 dark:text-white">
                                                        <?php echo htmlspecialchars($file['original_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        <?php echo htmlspecialchars($file['reference_number'] ?? 'N/A'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($file['category_name'] ?? 'Uncategorized'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($file['source_department']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($file['uploaded_by_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo date('M j, Y', strtotime($file['upload_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($file['workflow_statuses'])): ?>
                                                <?php 
                                                    $statuses = explode(', ', $file['workflow_statuses']);
                                                    $lastStatus = end($statuses);
                                                    $badgeColor = getStatusBadge($lastStatus);
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $badgeColor; ?>-100 dark:bg-<?php echo $badgeColor; ?>-900 text-<?php echo $badgeColor; ?>-800 dark:text-<?php echo $badgeColor; ?>-300">
                                                    <?php echo $lastStatus; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-300">
                                                    No Status
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="view_file.php?id=<?php echo $file['file_id']; ?>" 
                                                   class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <?php if ($currentUser['role_id'] == 1 || $currentUser['user_id'] == $file['uploaded_by']): ?>
                                                    <a href="edit_file.php?id=<?php echo $file['file_id']; ?>" 
                                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="delete_file.php?id=<?php echo $file['file_id']; ?>" 
                                                       class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                                                       onclick="return confirm('Are you sure you want to delete this file?');">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing <span class="font-medium"><?php echo (($currentPage - 1) * $perPage) + 1; ?></span>
                                    to <span class="font-medium"><?php echo min($currentPage * $perPage, $totalFiles); ?></span>
                                    of <span class="font-medium"><?php echo $totalFiles; ?></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <?php if ($currentPage > 1): ?>
                                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>"
                                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>"
                                           class="<?php echo $i == $currentPage ? 'bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'; ?> relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo !empty($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>"
                                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once 'modals/uploadModal.php'; ?>
<script src="assets/js/main.js"></script>
