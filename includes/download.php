<?php
require_once 'auth.php';
require_once 'authmenus.php';

$auth = new Auth();
$authMenus = new AuthMenus();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$fileId = $_GET['id'] ?? 0;
if (!$fileId) {
    header('Location: index.php');
    exit;
}

try {
    $conn = (new Database())->connect();
    
    // Get file info
    $stmt = $conn->prepare("
        SELECT f.*, d.department_name, CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
        FROM files f
        JOIN departments d ON f.source_department_id = d.department_id
        JOIN users u ON f.uploaded_by = u.user_id
        WHERE f.file_id = :file_id
    ");
    $stmt->bindParam(':file_id', $fileId);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$file) {
        throw new Exception('File not found');
    }
    
    // Check permissions
    $userId = $auth->getCurrentUser()['user_id'];
    $departmentId = $auth->getCurrentUser()['department_id'];
    
    // Admin can access all files
    if ($auth->getCurrentUser()['role_id'] != 1) {
        // Check if user uploaded the file
        if ($file['uploaded_by'] != $userId) {
            // Check if file is shared with user's department
            $stmt = $conn->prepare("
                SELECT 1 FROM file_shares 
                WHERE file_id = :file_id AND shared_to_dept = :dept_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':dept_id', $departmentId);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                // Check if file is from same department
                if ($file['source_department_id'] != $departmentId) {
                    throw new Exception('You do not have permission to access this file');
                }
            }
        }
    }
    
    // Check if file exists
    if (!file_exists($file['file_path'])) {
        throw new Exception('File not found on server');
    }
    
    // Log file access
    $stmt = $conn->prepare("
        INSERT INTO file_access_logs (
            file_id, user_id, action, ip_address, user_agent
        ) VALUES (
            :file_id, :user_id, 'download', :ip_address, :user_agent
        )
    ");
    $stmt->bindParam(':file_id', $fileId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
    $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $stmt->execute();
    
    // Send file to browser
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . filesize($file['file_path']));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    readfile($file['file_path']);
    exit;
    
} catch (Exception $e) {
    // Log error
    error_log("Download error: " . $e->getMessage());
    
    // Show error page
    $pageTitle = "Download Error";
    require_once 'header.php';
    ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="alert alert-danger">
                <h4>Download Failed</h4>
                <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
                <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
}
?>