<?php
$pageTitle = "View File";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/files.php';

if (!isset($_GET['id'])) {
    header("Location: files.php");
    exit();
}

$fileId = (int)$_GET['id'];
$fileActions = new FileActions();
$file = $fileActions->getFileByIdDetailed($fileId);

if (!$file) {
    $_SESSION['error_message'] = "File not found";
    header("Location: files.php");
    exit();
}

// Check if user has permission to view this file
if ($currentUser['role_id'] != 1 && $currentUser['department_id'] != $file['source_department_id']) {
    $_SESSION['error_message'] = "You don't have permission to view this file";
    header("Location: files.php");
    exit();
}
?>

<div class="flex h-full">
    <?php require_once 'userincludes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
        <?php require_once 'userincludes/topnav.php'; ?>
        
        <!-- File View Content -->
        <main class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">File Details</h2>
                    <p class="text-gray-600 dark:text-gray-400">Viewing file: <?php echo htmlspecialchars($file['original_name']); ?></p>
                </div>
                
                <div class="flex space-x-2">
                    <a href="files.php" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Files</span>
                    </a>
                    
                    <?php if ($currentUser['role_id'] == 1 || $currentUser['user_id'] == $file['uploaded_by']): ?>
                        <a href="edit_file.php?id=<?php echo $file['file_id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200">
                            <i class="fas fa-edit"></i>
                            <span>Edit File</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- File Details Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Main File Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Basic Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">File Name</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['original_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reference Number</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['reference_number'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['category_name'] ?? 'Uncategorized'); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upload Date</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo date('M j, Y H:i', strtotime($file['upload_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">File Metadata</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">File Size</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo formatBytes($file['file_size']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">File Type</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['file_type']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Confidential</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo $file['is_confidential'] ? 'Yes' : 'No'; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Physical File</p>
                                    <p class="text-gray-800 dark:text-white"><?php echo $file['is_physical'] ? 'Yes' : 'No'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Description</h3>
                        <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['description'] ?? 'No description provided'); ?></p>
                    </div>
                    
                    <?php if (!empty($file['comments'])): ?>
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Comments</h3>
                            <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['comments']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Status and Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Status & Actions</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Status</p>
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
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Source Department</p>
                            <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['source_department']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Uploaded By</p>
                            <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['uploaded_by_name']); ?></p>
                        </div>
                        
                        <?php if ($file['is_physical']): ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Physical Location</p>
                                <p class="text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['physical_location'] ?? 'Not specified'); ?></p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Movement History</p>
                                <p class="text-gray-800 dark:text-white"><?php echo $file['movement_count']; ?> movements</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-2">
                                <?php if (!$file['is_physical']): ?>
                                    <a href="<?php echo htmlspecialchars($file['file_path']); ?>" 
                                       class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200"
                                       download="<?php echo htmlspecialchars($file['original_name']); ?>">
                                        <i class="fas fa-download"></i>
                                        <span>Download</span>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($currentUser['role_id'] == 2 || $currentUser['role_id'] == 1): ?>
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors duration-200"
                                            data-bs-toggle="modal" data-bs-target="#shareModal">
                                        <i class="fas fa-share-alt"></i>
                                        <span>Share</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs for additional information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px">
                        <button class="tab-button active px-4 py-3 text-sm font-medium text-primary-600 dark:text-primary-400 border-b-2 border-primary-500 dark:border-primary-400" 
                                data-tab="workflow">
                            Workflow History
                        </button>
                        <?php if ($file['is_physical']): ?>
                            <button class="tab-button px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600" 
                                    data-tab="movements">
                                Movement History
                            </button>
                        <?php endif; ?>
                        <button class="tab-button px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600" 
                                data-tab="access">
                            Access Logs
                        </button>
                    </nav>
                </div>
                
                <!-- Workflow History Tab -->
                <div id="workflow-tab" class="tab-content p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Workflow History</h3>
                    <?php if (!empty($file['approval_history'])): ?>
                        <div class="space-y-4">
                            <?php foreach ($file['approval_history'] as $approval): ?>
                                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo getStatusBadge($approval['status_name']); ?>-100 dark:bg-<?php echo getStatusBadge($approval['status_name']); ?>-900 text-<?php echo getStatusBadge($approval['status_name']); ?>-800 dark:text-<?php echo getStatusBadge($approval['status_name']); ?>-300">
                                                <?php echo $approval['status_name']; ?>
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                <?php echo date('M j, Y H:i', strtotime($approval['updated_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($approval['department_name']); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($approval['review_comments'])): ?>
                                        <div class="mt-2 text-sm text-gray-800 dark:text-gray-300">
                                            <p class="font-medium">Review Comments:</p>
                                            <p><?php echo htmlspecialchars($approval['review_comments']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($approval['approval_comments'])): ?>
                                        <div class="mt-2 text-sm text-gray-800 dark:text-gray-300">
                                            <p class="font-medium">Approval Comments:</p>
                                            <p><?php echo htmlspecialchars($approval['approval_comments']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 dark:text-gray-400">No workflow history available</p>
                    <?php endif; ?>
                </div>
                
                <!-- Movement History Tab -->
                <?php if ($file['is_physical']): ?>
                    <div id="movements-tab" class="tab-content p-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Movement History</h3>
                        <?php if (!empty($file['movement_history'])): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">From</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">To</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Moved By</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($file['movement_history'] as $movement): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                    <?php echo date('M j, Y H:i', strtotime($movement['movement_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($movement['from_department'] ?? 'External'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($movement['to_department']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($movement['moved_by_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    <?php echo htmlspecialchars($movement['notes'] ?? 'No notes'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-600 dark:text-gray-400">No movement history available</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Access Logs Tab -->
                <div id="access-tab" class="tab-content p-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Access Logs</h3>
                    <?php if (!empty($file['access_logs'])): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($file['access_logs'] as $log): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                <?php echo date('M j, Y H:i', strtotime($log['access_time'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                <?php echo htmlspecialchars($log['user_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                <?php echo ucfirst(str_replace('_', ' ', $log['action'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                <?php echo htmlspecialchars($log['ip_address']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 dark:text-gray-400">No access logs available</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="share_file.php" method="POST">
                <input type="hidden" name="file_id" value="<?php echo $file['file_id']; ?>">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Share With Department</label>
                        <select name="department_id" id="department" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <?php foreach ($departments as $dept): ?>
                                <?php if ($dept['department_id'] != $file['source_department_id']): ?>
                                    <option value="<?php echo $dept['department_id']; ?>">
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message (Optional)</label>
                        <textarea name="message" id="message" rows="3" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Share File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab functionality
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons and hide all tabs
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'text-primary-600', 'dark:text-primary-400', 'border-primary-500', 'dark:border-primary-400');
            btn.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
        });
        
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        // Add active class to clicked button and show corresponding tab
        button.classList.add('active', 'text-primary-600', 'dark:text-primary-400', 'border-primary-500', 'dark:border-primary-400');
        button.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
        
        const tabId = button.getAttribute('data-tab') + '-tab';
        document.getElementById(tabId).classList.remove('hidden');
    });
});
</script>

<?php require_once 'userincludes/footer.php'; ?>