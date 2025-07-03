
<?php
require_once 'Database.php';
require_once 'mail.php';
require_once 'notifications.php';

class FileActions {
    private $db;
    private $mail;
    private $notification;

    public function __construct() {
        $this->db = new Database();
        $this->notification = new Notification();
    }


    /**
     * Get user by ID
     */
    private function getUserById($userId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT user_id, email, first_name, last_name 
                FROM users 
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }

    /**
 * Upload a new file with metadata
 */
public function uploadFile($fileData, $uploadedBy) {
    $maxRetries = 3;
    $retryCount = 0;
    $result = null;
    
    do {
        try {
            $conn = $this->db->connect();
            
            // Set a lower lock timeout for this session
            $conn->exec("SET SESSION innodb_lock_wait_timeout = 30");
            
            // Validate required fields
            $required = ['file_name', 'original_name', 'source_department_id', 'reference_number'];
            foreach ($required as $field) {
                if (empty($fileData[$field])) {
                    return ['success' => false, 'message' => "Required field '$field' is missing"];
                }
            }
            
            // For digital files, validate file path and size
            if (empty($fileData['is_physical'])) {
                if (empty($fileData['file_path']) || empty($fileData['file_size'])) {
                    return ['success' => false, 'message' => "Digital files require file path and size"];
                }
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            try {
                // Insert file metadata (only essential fields)
                $stmt = $conn->prepare("
                    INSERT INTO files (
                        file_name, original_name, file_path, file_size, file_type, 
                        mime_type, uploaded_by, source_department_id, 
                        is_confidential, is_physical, reference_number
                    ) VALUES (
                        :file_name, :original_name, :file_path, :file_size, :file_type, 
                        :mime_type, :uploaded_by, :source_department_id, 
                        :is_confidential, :is_physical, :reference_number
                    )
                ");
                
                // ... [binding parameters remains the same] ...
                
                $stmt->execute();
                $fileId = $conn->lastInsertId();
                
                // Update remaining fields in a separate statement
                if (!empty($fileData['description']) || !empty($fileData['category_id']) || 
                    !empty($fileData['destination_department_id']) || !empty($fileData['comments'])) {
                    
                    $updateStmt = $conn->prepare("
                        UPDATE files SET
                            category_id = :category_id,
                            description = :description,
                            destination_department_id = :destination_department_id,
                            destination_contact = :destination_contact,
                            physical_location = :physical_location,
                            originator = :originator,
                            receiver = :receiver,
                            date_of_origination = :date_of_origination,
                            comments = :comments
                        WHERE file_id = :file_id
                    ");
                    
                    // ... [binding parameters remains the same] ...
                    
                    $updateStmt->execute();
                }
                
                // For physical files, set received_at timestamp - FIXED THIS SECTION
                if (!empty($fileData['is_physical'])) {
                    $updateReceived = $conn->prepare("UPDATE files SET received_at = NOW() WHERE file_id = :file_id");
                    $updateReceived->bindParam(':file_id', $fileId);
                    $updateReceived->execute();
                    
                    // Record the physical movement (can be done after commit)
                    $this->recordPhysicalMovement(
                        $fileId,
                        null,
                        $fileData['source_department_id'],
                        $uploadedBy,
                        'Initial receipt of physical file'
                    );
                }
                
                // Commit transaction early before non-critical operations
                $conn->commit();
                
                // Create initial workflow status (outside transaction)
                $statusId = $this->getInitialWorkflowStatusId();
                if ($statusId) {
                    $this->createFileApproval($fileId, $fileData['source_department_id'], $statusId);
                }
                
                // Log file access (outside transaction)
                $this->logFileAccess($fileId, $uploadedBy, $fileData['is_physical'] ? 'physical_receive' : 'upload');
                
                // Notify appropriate parties (outside transaction)
                $this->notifyFileUpload($fileId, $uploadedBy, $fileData);
                
                return ['success' => true, 'file_id' => $fileId];
                
            } catch (PDOException $e) {
                $conn->rollBack();
                throw $e; // Re-throw for retry logic
            }
            
        } catch (PDOException $e) {
            error_log("File upload attempt $retryCount failed: " . $e->getMessage());
            
            // Check if it's a lock timeout error
            if (strpos($e->getMessage(), 'Lock wait timeout exceeded') === false) {
                return ['success' => false, 'message' => 'Failed to upload file: ' . $e->getMessage()];
            }
            
            $retryCount++;
            if ($retryCount < $maxRetries) {
                usleep(500000 * $retryCount); // Wait 0.5s, then 1s, then 1.5s
                continue;
            }
            
            return ['success' => false, 'message' => 'Failed to upload file after ' . $maxRetries . ' attempts: ' . $e->getMessage()];
        }
    } while ($retryCount < $maxRetries);
}

    /**
     * Record physical file movement between departments
     */
    public function movePhysicalFile($fileId, $fromDeptId, $toDeptId, $movedBy, $notes = '') {
        try {
            $conn = $this->db->connect();
            
            // Verify file exists and is physical
            $file = $this->getFileById($fileId);
            if (!$file) {
                return ['success' => false, 'message' => 'File not found'];
            }
            if (!$file['is_physical']) {
                return ['success' => false, 'message' => 'Only physical files can be moved'];
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Update file's current location
            $stmt = $conn->prepare("
                UPDATE files 
                SET destination_department_id = :to_dept,
                    physical_location = :location,
                    updated_at = NOW()
                WHERE file_id = :file_id
            ");
            $stmt->bindParam(':to_dept', $toDeptId);
            $stmt->bindValue(':location', "Department: " . $this->getDepartmentName($toDeptId));
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            // Record the movement
            $this->recordPhysicalMovement($fileId, $fromDeptId, $toDeptId, $movedBy, $notes);
            
            // Log file access
            $this->logFileAccess($fileId, $movedBy, 'physical_move');
            
            // Commit transaction
            $conn->commit();
            
            // Notify receiving department
            $this->notifyPhysicalFileMovement($fileId, $fromDeptId, $toDeptId, $movedBy);
            
            return ['success' => true, 'message' => 'Physical file movement recorded'];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Physical file move error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to record physical file movement'];
        }
    }


    /**
     * Get file by ID with all metadata (detailed version)
     */
    public function getFileByIdDetailed($fileId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT 
                    f.*, 
                    fc.category_name,
                    d_source.department_name as source_department,
                    d_dest.department_name as destination_department,
                    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                    CONCAT(ur.first_name, ' ', ur.last_name) as received_by_name,
                    (SELECT COUNT(*) FROM physical_file_movements WHERE file_id = f.file_id) as movement_count
                FROM files f
                LEFT JOIN file_categories fc ON f.category_id = fc.category_id
                LEFT JOIN departments d_source ON f.source_department_id = d_source.department_id
                LEFT JOIN departments d_dest ON f.destination_department_id = d_dest.department_id
                LEFT JOIN users u ON f.uploaded_by = u.user_id
                LEFT JOIN users ur ON f.received_by = ur.user_id
                WHERE f.file_id = :file_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($file) {
                $file['movement_history'] = $this->getPhysicalFileHistory($fileId);
            }
            
            return $file;
            
        } catch (PDOException $e) {
            error_log("Get file by ID error: " . $e->getMessage());
            return null;
        }
    }
    

    /**
     * Notify about file upload/registration
     */
    private function notifyFileUpload($fileId, $uploadedBy, $fileData) {
        $file = $this->getFileById($fileId);
        $uploader = $this->getUserById($uploadedBy);
        $uploaderName = $uploader ? $uploader['first_name'] . ' ' . $uploader['last_name'] : 'Unknown';
        
        // Notify department head about new file
        $departmentHead = $this->getDepartmentHead($fileData['source_department_id']);
        if ($departmentHead) {
            $notificationData = [
                'recipient_id' => $departmentHead['user_id'],
                'sender_id' => $uploadedBy,
                'title' => $fileData['is_physical'] ? 'Physical File Received' : 'New File Uploaded',
                'message' => ($fileData['is_physical'] ? 
                    "A physical file '{$fileData['original_name']}' was received by $uploaderName" :
                    "A new file '{$fileData['original_name']}' was uploaded by $uploaderName"),
                'notification_type' => $fileData['is_physical'] ? 'physical_file_received' : 'file_uploaded',
                'related_file_id' => $fileId
            ];
            $this->notification->create($notificationData);
            
            if ($uploader) {
                $emailData = [
                    'to' => $departmentHead['email'],
                    'subject' => ($fileData['is_physical'] ? 'Physical File Received: ' : 'New File Uploaded: ') . $fileData['original_name'],
                    'template' => $fileData['is_physical'] ? 'physical_file_received' : 'file_upload_notification',
                    'data' => [
                        'head_name' => $departmentHead['first_name'] . ' ' . $departmentHead['last_name'],
                        'uploader_name' => $uploaderName,
                        'file_name' => $fileData['original_name'],
                        'reference_number' => $fileData['reference_number'],
                        'file_type' => $fileData['is_physical'] ? 'Physical Document' : $fileData['file_type'],
                        'file_size' => $fileData['is_physical'] ? 'N/A' : $this->formatFileSize($fileData['file_size']),
                        'upload_date' => date('Y-m-d H:i'),
                        'department' => $this->getDepartmentName($fileData['source_department_id']),
                        'file_link' => $_ENV['APP_URL'] . '/files/view/' . $fileId
                    ]
                ];
                
                $this->mail->send($emailData);
            }
        }
        
        // Send confirmation to uploader if different from department head
        if ($uploader && (!$departmentHead || $departmentHead['user_id'] != $uploadedBy)) {
            $uploaderEmailData = [
                'to' => $uploader['email'],
                'subject' => $fileData['is_physical'] ? 
                    'Physical File Registration Confirmation' : 
                    'Your File Was Successfully Uploaded',
                'template' => $fileData['is_physical'] ? 
                    'physical_file_registration_confirmation' : 
                    'file_upload_confirmation',
                'data' => [
                    'user_name' => $uploaderName,
                    'file_name' => $fileData['original_name'],
                    'reference_number' => $fileData['reference_number'],
                    'date' => date('Y-m-d H:i'),
                    'file_link' => $_ENV['APP_URL'] . '/files/view/' . $fileId,
                    'department' => $this->getDepartmentName($fileData['source_department_id'])
                ]
            ];
            $this->mail->send($uploaderEmailData);
        }
    }
    
    /**
     * Notify about physical file movement
     */
    private function notifyPhysicalFileMovement($fileId, $fromDeptId, $toDeptId, $movedBy) {
        $file = $this->getFileById($fileId);
        $mover = $this->getUserById($movedBy);
        $moverName = $mover ? $mover['first_name'] . ' ' . $mover['last_name'] : 'Unknown';
        
        $toDept = $this->getDepartmentById($toDeptId);
        $fromDept = $fromDeptId ? $this->getDepartmentById($fromDeptId) : null;
        
        // Notify receiving department head
        $toDeptHead = $this->getDepartmentHead($toDeptId);
        if ($toDeptHead) {
            $notificationData = [
                'recipient_id' => $toDeptHead['user_id'],
                'sender_id' => $movedBy,
                'title' => 'Physical File Received',
                'message' => "Physical file '{$file['original_name']}' has been received from " . 
                             ($fromDept ? $fromDept['department_name'] : 'external source') . 
                             " by $moverName",
                'notification_type' => 'physical_file_received',
                'related_file_id' => $fileId
            ];
            $this->notification->create($notificationData);
            
            $emailData = [
                'to' => $toDeptHead['email'],
                'subject' => 'Physical File Received: ' . $file['original_name'],
                'template' => 'physical_file_received',
                'data' => [
                    'head_name' => $toDeptHead['first_name'] . ' ' . $toDeptHead['last_name'],
                    'file_name' => $file['original_name'],
                    'reference_number' => $file['reference_number'],
                    'from_source' => $fromDept ? $fromDept['department_name'] : 'External',
                    'received_by' => $moverName,
                    'receive_date' => date('Y-m-d H:i'),
                    'file_link' => $_ENV['APP_URL'] . '/files/view/' . $fileId
                ]
            ];
            $this->mail->send($emailData);
        }
        
        // Notify sending department head if applicable
        if ($fromDeptId && $fromDept) {
            $fromDeptHead = $this->getDepartmentHead($fromDeptId);
            if ($fromDeptHead) {
                $notificationData = [
                    'recipient_id' => $fromDeptHead['user_id'],
                    'sender_id' => $movedBy,
                    'title' => 'Physical File Delivered',
                    'message' => "Physical file '{$file['original_name']}' has been delivered to " . 
                                 $toDept['department_name'] . " by $moverName",
                    'notification_type' => 'physical_file_delivered',
                    'related_file_id' => $fileId
                ];
                $this->notification->create($notificationData);
            }
        }
    }
    

     /**
     * Get department by ID
     */
    private function getDepartmentById($departmentId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT * FROM departments 
                WHERE department_id = :department_id
            ");
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get department by ID error: " . $e->getMessage());
            return null;
        }
    }
    

    /**
     * Share a file with another department
     */
    public function shareFile($fileId, $toDepartmentId, $sharedBy, $message = '') {
        try {
            $conn = $this->db->connect();
            
            // Verify file exists and belongs to source department
            $file = $this->getFileById($fileId);
            if (!$file) {
                return ['success' => false, 'message' => 'File not found'];
            }
            
            // Verify target department exists and get department info
            $stmt = $conn->prepare("
                SELECT department_id, department_name 
                FROM departments 
                WHERE department_id = :dept_id
            ");
            $stmt->bindParam(':dept_id', $toDepartmentId);
            $stmt->execute();
            $targetDept = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$targetDept) {
                return ['success' => false, 'message' => 'Target department not found'];
            }
            
            // Check if file is already shared with this department
            $stmt = $conn->prepare("
                SELECT share_id FROM file_shares 
                WHERE file_id = :file_id AND shared_to_dept = :to_dept AND is_active = TRUE
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':to_dept', $toDepartmentId);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'File is already shared with this department'];
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Create share record
            $stmt = $conn->prepare("
                INSERT INTO file_shares (
                    file_id, shared_by, shared_from_dept, shared_to_dept, 
                    share_message, share_date
                ) VALUES (
                    :file_id, :shared_by, :from_dept, :to_dept, 
                    :message, NOW()
                )
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':shared_by', $sharedBy);
            $stmt->bindParam(':from_dept', $file['source_department_id']);
            $stmt->bindParam(':to_dept', $toDepartmentId);
            $stmt->bindParam(':message', $message);
            $stmt->execute();
            $shareId = $conn->lastInsertId();
            
            // Create approval record for the receiving department
            $statusId = $this->getInitialWorkflowStatusId(); // Pending Review
            $this->createFileApproval($fileId, $toDepartmentId, $statusId, $shareId);
            
            // Log file access
            $this->logFileAccess($fileId, $sharedBy, 'share');
            
            // Commit transaction
            $conn->commit();
            
            // Get department head and source department info
            $departmentHead = $this->getDepartmentHead($toDepartmentId);
            $sourceDept = $this->getDepartmentName($file['source_department_id']);
            $sharer = $this->getUserById($sharedBy);
            $sharerName = $sharer ? $sharer['first_name'] . ' ' . $sharer['last_name'] : 'Unknown';
            
            if ($departmentHead) {
                // 1. Create in-app notification
                $notificationData = [
                    'recipient_id' => $departmentHead['user_id'],
                    'sender_id' => $sharedBy,
                    'title' => 'File Shared With Your Department',
                    'message' => "File '{$file['original_name']}' was shared from $sourceDept by $sharerName",
                    'notification_type' => 'file_shared',
                    'related_file_id' => $fileId
                ];
                $this->notification->create($notificationData);
                
                // 2. Send email to target department head
                $emailData = [
                    'to' => $departmentHead['email'],
                    'subject' => 'File Shared: ' . $file['original_name'],
                    'template' => 'file_share_notification',
                    'data' => [
                        'recipient_name' => $departmentHead['first_name'] . ' ' . $departmentHead['last_name'],
                        'file_name' => $file['original_name'],
                        'sharer_name' => $sharerName,
                        'source_department' => $sourceDept,
                        'target_department' => $targetDept['department_name'],
                        'share_date' => date('Y-m-d H:i'),
                        'share_message' => $message ?: 'No additional message provided',
                        'file_link' => $_ENV['APP_URL'] . '/files/view/' . $fileId,
                        'action_link' => $_ENV['APP_URL'] . '/files/review/' . $fileId
                    ]
                ];
                $this->mail->send($emailData);
            }
            
            $sourceDeptHead = $this->getDepartmentHead($file['source_department_id']);
            if ($sourceDeptHead && $sourceDeptHead['user_id'] != $sharedBy) {
                $notificationData = [
                    'recipient_id' => $sourceDeptHead['user_id'],
                    'sender_id' => $sharedBy,
                    'title' => 'File Shared to Another Department',
                    'message' => "You shared '{$file['original_name']}' to {$targetDept['department_name']}",
                    'notification_type' => 'file_shared_outbound',
                    'related_file_id' => $fileId
                ];
                $this->notification->create($notificationData);
            }
            
            return [
                'success' => true, 
                'share_id' => $shareId,
                'message' => 'File shared successfully'
            ];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("File share error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to share file'];
        }
    }
    
    /**
     * Update file workflow status (review, approval, etc.) 
     */
    public function updateFileStatus($fileId, $departmentId, $statusName, $userId, $comments = '') {
        try {
            $conn = $this->db->connect();
            
            // Get status ID and validate
            $statusId = $this->getStatusIdByName($statusName);
            if (!$statusId) {
                return ['success' => false, 'message' => 'Invalid status'];
            }
            
            // Get current approval record
            $approval = $this->getFileApproval($fileId, $departmentId);
            if (!$approval) {
                return ['success' => false, 'message' => 'File approval record not found'];
            }
            
            // Determine action type based on status
            $actionConfig = $this->getStatusActionConfig($statusName);
            if (!$actionConfig) {
                return ['success' => false, 'message' => 'Invalid status action'];
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Update approval record
            $this->updateApprovalRecord(
                $approval['approval_id'],
                $statusId,
                $userId,
                $actionConfig,
                $comments
            );
            
            // Log file access
            $this->logFileAccess($fileId, $userId, $actionConfig['action']);
            
            // Get complete file details with department info
            $file = $this->getFileWithDepartmentInfo($fileId);
            
            // Commit transaction
            $conn->commit();
            
            // Notify relevant users via both in-app and email
            $this->notifyStatusChange(
                $file, 
                $departmentId, 
                $statusName, 
                $userId, 
                $actionConfig['notificationType'], 
                $comments
            );
            
            return [
                'success' => true, 
                'message' => 'File status updated to ' . $statusName,
                'file_id' => $fileId,
                'new_status' => $statusName
            ];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("File status update error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update file status'];
        }
    }

    /**
     * Helper method to get status action configuration
     */
    private function getStatusActionConfig($statusName) {
        $config = [
            'Under Review' => [
                'action' => 'review',
                'notificationType' => 'file_reviewed',
                'updateField' => 'reviewer_id',
                'timestampField' => 'reviewed_at',
                'commentsField' => 'review_comments',
                'emailTemplate' => 'file_under_review'
            ],
            'Revision Required' => [
                'action' => 'revision_request',
                'notificationType' => 'file_revision',
                'updateField' => 'reviewer_id',
                'timestampField' => 'reviewed_at',
                'commentsField' => 'review_comments',
                'emailTemplate' => 'file_revision_required'
            ],
            'Approved' => [
                'action' => 'approval',
                'notificationType' => 'file_approved',
                'updateField' => 'approver_id',
                'timestampField' => 'approved_at',
                'commentsField' => 'approval_comments',
                'emailTemplate' => 'file_approved'
            ],
            'Rejected' => [
                'action' => 'rejection',
                'notificationType' => 'file_rejected',
                'updateField' => 'approver_id',
                'timestampField' => 'approved_at',
                'commentsField' => 'approval_comments',
                'emailTemplate' => 'file_rejected'
            ]
        ];
        
        return $config[$statusName] ?? [
            'action' => 'status_update',
            'notificationType' => 'file_status_updated',
            'emailTemplate' => 'file_status_update'
        ];
    }

    /**
     * Update approval record in database
     */
    private function updateApprovalRecord($approvalId, $statusId, $userId, $actionConfig, $comments) {
        $conn = $this->db->connect();
        
        $query = "UPDATE file_approvals SET status_id = :status_id";
        $params = [':status_id' => $statusId];
        
        if (!empty($actionConfig['updateField'])) {
            $query .= ", {$actionConfig['updateField']} = :user_id";
            $params[':user_id'] = $userId;
        }
        
        if (!empty($actionConfig['timestampField'])) {
            $query .= ", {$actionConfig['timestampField']} = NOW()";
        }
        
        if (!empty($actionConfig['commentsField']) && $comments) {
            $query .= ", {$actionConfig['commentsField']} = :comments";
            $params[':comments'] = $comments;
        }
        
        $query .= " WHERE approval_id = :approval_id";
        $params[':approval_id'] = $approvalId;
        
        $stmt = $conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
    }

    /**
     * Enhanced notification with email support
     */
    private function notifyStatusChange($file, $departmentId, $statusName, $userId, $notificationType, $comments = '') {
        try {
            $conn = $this->db->connect();
            $changer = $this->getUserById($userId);
            $changerName = $changer ? $changer['first_name'] . ' ' . $changer['last_name'] : 'System';
            
            // Get all users in the department who should be notified
            $stmt = $conn->prepare("
                SELECT u.user_id, u.email, u.first_name, u.last_name
                FROM user_roles ur
                JOIN users u ON ur.user_id = u.user_id
                WHERE ur.department_id = :department_id
                AND ur.is_active = TRUE
                AND u.user_id != :current_user_id
            ");
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->bindParam(':current_user_id', $userId);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get status action config for email template
            $actionConfig = $this->getStatusActionConfig($statusName);
            
            // Notify department members
            foreach ($users as $user) {
                // In-app notification
                $notificationData = [
                    'recipient_id' => $user['user_id'],
                    'sender_id' => $userId,
                    'title' => "File Status: $statusName",
                    'message' => "File '{$file['original_name']}' status changed to '$statusName' by $changerName",
                    'notification_type' => $notificationType,
                    'related_file_id' => $file['file_id']
                ];
                $this->notification->create($notificationData);
                
                // Email notification
                $emailData = [
                    'to' => $user['email'],
                    'subject' => "File Status Update: {$file['original_name']} - $statusName",
                    'template' => $actionConfig['emailTemplate'],
                    'data' => [
                        'recipient_name' => $user['first_name'] . ' ' . $user['last_name'],
                        'file_name' => $file['original_name'],
                        'status' => $statusName,
                        'changed_by' => $changerName,
                        'change_date' => date('Y-m-d H:i'),
                        'comments' => $comments ?: 'No additional comments provided',
                        'file_link' => $_ENV['APP_URL'] . '/files/view/' . $file['file_id'],
                        'department' => $file['source_department']
                    ]
                ];
                $this->mail->send($emailData);
            }
            
            // Notify the file uploader if they're in a different department
            if ($file['uploaded_by'] != $userId && $file['source_department_id'] != $departmentId) {
                $uploader = $this->getUserById($file['uploaded_by']);
                if ($uploader) {
                    // In-app notification
                    $notificationData = [
                        'recipient_id' => $file['uploaded_by'],
                        'sender_id' => $userId,
                        'title' => "Your File Status: $statusName",
                        'message' => "Your file '{$file['original_name']}' status changed to '$statusName' by $changerName",
                        'notification_type' => $notificationType,
                        'related_file_id' => $file['file_id']
                    ];
                    $this->notification->create($notificationData);
                    
                    // Email notification
                    $emailData = [
                        'to' => $uploader['email'],
                        'subject' => "Your File Status: {$file['original_name']} - $statusName",
                        'template' => 'file_status_update_owner',
                        'data' => [
                            'owner_name' => $uploader['first_name'] . ' ' . $uploader['last_name'],
                            'file_name' => $file['original_name'],
                            'status' => $statusName,
                            'changed_by' => $changerName,
                            'change_date' => date('Y-m-d H:i'),
                            'comments' => $comments ?: 'No additional comments provided',
                            'file_link' => $_ENV['APP_URL'] . '/files/view/' . $file['file_id'],
                            'department' => $file['source_department']
                        ]
                    ];
                    $this->mail->send($emailData);
                }
            }
            
        } catch (PDOException $e) {
            error_log("Status change notification error: " . $e->getMessage());
        }
    }

    /**
     * Get file with complete department info
     */
    private function getFileWithDepartmentInfo($fileId) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("
            SELECT 
                f.*, 
                d.department_name as source_department,
                d.department_id as source_department_id
            FROM files f
            LEFT JOIN departments d ON f.source_department_id = d.department_id
            WHERE f.file_id = :file_id
        ");
        $stmt->bindParam(':file_id', $fileId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get file by ID with all metadata
     */
    public function getFileById($fileId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT 
                    f.*, 
                    fc.category_name,
                    d.department_name as source_department,
                    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
                FROM files f
                LEFT JOIN file_categories fc ON f.category_id = fc.category_id
                LEFT JOIN departments d ON f.source_department_id = d.department_id
                LEFT JOIN users u ON f.uploaded_by = u.user_id
                WHERE f.file_id = :file_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get file by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all files with filtering and pagination
     */
    
    /**
     * Get all files with filtering and pagination
     */
    public function getAllFiles($page = 1, $perPage = 10, $filters = []) {
        try {
            $conn = $this->db->connect();
            
            // Build base query
            $query = "
                SELECT 
                    f.file_id, f.original_name, f.file_name, f.file_path,
                    f.file_size, f.file_type, f.upload_date, f.received_at,
                    f.is_physical, f.reference_number,
                    fc.category_name,
                    d_source.department_name as source_department,
                    d_dest.department_name as destination_department,
                    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                    COUNT(DISTINCT fs.share_id) as share_count,
                    GROUP_CONCAT(DISTINCT ws.status_name ORDER BY fa.created_at DESC SEPARATOR ', ') as workflow_statuses,
                    (SELECT COUNT(*) FROM physical_file_movements WHERE file_id = f.file_id) as movement_count
                FROM files f
                LEFT JOIN file_categories fc ON f.category_id = fc.category_id
                LEFT JOIN departments d_source ON f.source_department_id = d_source.department_id
                LEFT JOIN departments d_dest ON f.destination_department_id = d_dest.department_id
                LEFT JOIN users u ON f.uploaded_by = u.user_id
                LEFT JOIN file_shares fs ON f.file_id = fs.file_id AND fs.is_active = TRUE
                LEFT JOIN file_approvals fa ON f.file_id = fa.file_id
                LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
            ";
            
            // Add filters
            $where = [];
            $params = [];
            
            if (!empty($filters['search'])) {
                $search = "%{$filters['search']}%";
                $where[] = "(f.original_name LIKE :search OR f.description LIKE :search OR f.reference_number LIKE :search)";
                $params[':search'] = $search;
            }
            
            if (!empty($filters['department_id'])) {
                $where[] = "f.source_department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }
            
            if (!empty($filters['category_id'])) {
                $where[] = "f.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            
            if (!empty($filters['uploaded_by'])) {
                $where[] = "f.uploaded_by = :uploaded_by";
                $params[':uploaded_by'] = $filters['uploaded_by'];
            }
            
            if (!empty($filters['status_id'])) {
                $where[] = "fa.status_id = :status_id";
                $params[':status_id'] = $filters['status_id'];
            }
            
            if (!empty($filters['is_confidential'])) {
                $where[] = "f.is_confidential = :is_confidential";
                $params[':is_confidential'] = $filters['is_confidential'];
            }
            
            if (!empty($filters['is_physical'])) {
                $where[] = "f.is_physical = :is_physical";
                $params[':is_physical'] = $filters['is_physical'];
            }
            
            if (!empty($filters['reference_number'])) {
                $where[] = "f.reference_number = :reference_number";
                $params[':reference_number'] = $filters['reference_number'];
            }
            
            if (!empty($where)) {
                $query .= " WHERE " . implode(" AND ", $where);
            }
            
            $query .= " GROUP BY f.file_id";
            
            // Add sorting
            $sortField = $filters['sort'] ?? 'f.upload_date';
            $sortOrder = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $query .= " ORDER BY $sortField $sortOrder";
            
            // Add pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :offset, :per_page";
            $params[':offset'] = $offset;
            $params[':per_page'] = $perPage;
            
            // Execute query
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "
                SELECT COUNT(DISTINCT f.file_id) as total 
                FROM files f
                LEFT JOIN file_approvals fa ON f.file_id = fa.file_id
            ";
            
            if (!empty($where)) {
                $countQuery .= " WHERE " . implode(" AND ", $where);
            }
            
            $stmt = $conn->prepare($countQuery);
            
            // Remove LIMIT params for count query
            unset($params[':offset']);
            unset($params[':per_page']);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'files' => $files,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
            
        } catch (PDOException $e) {
            error_log("Get all files error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve files'];
        }
    }


     /**
     * Helper method to record physical file movement
     */
    private function recordPhysicalMovement($fileId, $fromDeptId, $toDeptId, $movedBy, $notes = '') {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("
            INSERT INTO physical_file_movements (
                file_id, from_department_id, to_department_id, moved_by, notes
            ) VALUES (
                :file_id, :from_dept, :to_dept, :moved_by, :notes
            )
        ");
        $stmt->bindParam(':file_id', $fileId);
        $stmt->bindValue(':from_dept', $fromDeptId);
        $stmt->bindParam(':to_dept', $toDeptId);
        $stmt->bindParam(':moved_by', $movedBy);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();
    }
    


    /**
     * Get physical file movement history
     */
    public function getPhysicalFileHistory($fileId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT 
                    m.movement_id, m.movement_date, m.notes,
                    from_dept.department_name as from_department,
                    to_dept.department_name as to_department,
                    CONCAT(u.first_name, ' ', u.last_name) as moved_by_name
                FROM physical_file_movements m
                JOIN departments to_dept ON m.to_department_id = to_dept.department_id
                LEFT JOIN departments from_dept ON m.from_department_id = from_dept.department_id
                JOIN users u ON m.moved_by = u.user_id
                WHERE m.file_id = :file_id
                ORDER BY m.movement_date DESC
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get physical file history error: " . $e->getMessage());
            return [];
        }
    }


    
    /**
     * Get files shared with a department
     */
    public function getSharedFiles($departmentId, $page = 1, $perPage = 10, $filters = []) {
        try {
            $conn = $this->db->connect();
            
            // Build base query
            $query = "
                SELECT 
                    f.file_id, f.original_name, f.file_name, f.file_path,
                    f.file_size, f.file_type, f.upload_date,
                    fc.category_name,
                    d.department_name as source_department,
                    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name,
                    fs.share_date, fs.share_message,
                    CONCAT(us.first_name, ' ', us.last_name) as shared_by_name,
                    ws.status_name as current_status
                FROM file_shares fs
                JOIN files f ON fs.file_id = f.file_id
                LEFT JOIN file_categories fc ON f.category_id = fc.category_id
                LEFT JOIN departments d ON f.source_department_id = d.department_id
                LEFT JOIN users u ON f.uploaded_by = u.user_id
                LEFT JOIN users us ON fs.shared_by = us.user_id
                LEFT JOIN file_approvals fa ON f.file_id = fa.file_id AND fa.department_id = :department_id
                LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
                WHERE fs.shared_to_dept = :department_id
                AND fs.is_active = TRUE
            ";
            
            $params = [
                ':department_id' => $departmentId
            ];
            
            // Add filters
            if (!empty($filters['status_id'])) {
                $query .= " AND fa.status_id = :status_id";
                $params[':status_id'] = $filters['status_id'];
            }
            
            if (!empty($filters['search'])) {
                $search = "%{$filters['search']}%";
                $query .= " AND (f.original_name LIKE :search OR f.description LIKE :search)";
                $params[':search'] = $search;
            }
            
            // Add sorting
            $sortField = $filters['sort'] ?? 'fs.share_date';
            $sortOrder = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $query .= " ORDER BY $sortField $sortOrder";
            
            // Add pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :offset, :per_page";
            $params[':offset'] = $offset;
            $params[':per_page'] = $perPage;
            
            // Execute query
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            
            $stmt->execute();
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM file_shares fs
                JOIN files f ON fs.file_id = f.file_id
                WHERE fs.shared_to_dept = :department_id
                AND fs.is_active = TRUE
            ";
            
            $stmt = $conn->prepare($countQuery);
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'files' => $files,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
            
        } catch (PDOException $e) {
            error_log("Get shared files error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to retrieve shared files'];
        }
    }
    
    /**
     * Delete a file (mark as inactive in database - actual file deletion should be handled separately)
     */
    public function deleteFile($fileId, $deletedBy) {
        try {
            $conn = $this->db->connect();
            
            // Get file info first for notification
            $file = $this->getFileById($fileId);
            if (!$file) {
                return ['success' => false, 'message' => 'File not found'];
            }
            
            // Start transaction
            $conn->beginTransaction();
            
            // Mark file shares as inactive
            $stmt = $conn->prepare("
                UPDATE file_shares SET is_active = FALSE 
                WHERE file_id = :file_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            // Mark file approvals as inactive (or delete them)
            $stmt = $conn->prepare("
                DELETE FROM file_approvals 
                WHERE file_id = :file_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            // Delete the file record
            $stmt = $conn->prepare("
                DELETE FROM files 
                WHERE file_id = :file_id
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->execute();
            
            // Log file access
            $this->logFileAccess($fileId, $deletedBy, 'delete');
            
            // Commit transaction
            $conn->commit();
            
            // Notify department members
            $this->notifyFileDeletion($file, $deletedBy);
            
            return ['success' => true, 'message' => 'File deleted successfully'];
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Delete file error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to delete file'];
        }
    }
    
    // ===== PRIVATE HELPER METHODS ===== //
    
    private function getInitialWorkflowStatusId() {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT status_id FROM workflow_statuses 
                WHERE status_name = 'Pending Review'
            ");
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['status_id'] : null;
            
        } catch (PDOException $e) {
            error_log("Get initial workflow status error: " . $e->getMessage());
            return null;
        }
    }
    
    private function getStatusIdByName($statusName) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT status_id FROM workflow_statuses 
                WHERE status_name = :status_name
            ");
            $stmt->bindParam(':status_name', $statusName);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['status_id'] : null;
            
        } catch (PDOException $e) {
            error_log("Get status ID by name error: " . $e->getMessage());
            return null;
        }
    }
    
    private function createFileApproval($fileId, $departmentId, $statusId, $shareId = null) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                INSERT INTO file_approvals (
                    file_id, share_id, department_id, status_id
                ) VALUES (
                    :file_id, :share_id, :department_id, :status_id
                )
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':share_id', $shareId);
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->bindParam(':status_id', $statusId);
            $stmt->execute();
            
            return $conn->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Create file approval error: " . $e->getMessage());
            return false;
        }
    }
    
    private function getFileApproval($fileId, $departmentId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT * FROM file_approvals 
                WHERE file_id = :file_id AND department_id = :department_id
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get file approval error: " . $e->getMessage());
            return null;
        }
    }
    
    private function logFileAccess($fileId, $userId, $action) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                INSERT INTO file_access_logs (
                    file_id, user_id, action, ip_address, user_agent
                ) VALUES (
                    :file_id, :user_id, :action, :ip_address, :user_agent
                )
            ");
            $stmt->bindParam(':file_id', $fileId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
            $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("File access log error: " . $e->getMessage());
        }
    }
    
    private function getDepartmentHead($departmentId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT u.user_id, u.email, u.first_name, u.last_name
                FROM departments d
                JOIN users u ON d.head_user_id = u.user_id
                WHERE d.department_id = :department_id
            ");
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get department head error: " . $e->getMessage());
            return null;
        }
    }
    
    private function getDepartmentName($departmentId) {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT department_name FROM departments 
                WHERE department_id = :department_id
            ");
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['department_name'] : 'Unknown Department';
            
        } catch (PDOException $e) {
            error_log("Get department name error: " . $e->getMessage());
            return 'Unknown Department';
        }
    }
    
    private function notifyFileDeletion($file, $deletedBy) {
        try {
            $conn = $this->db->connect();
            
            // Get all users who interacted with the file (uploader, sharers, approvers)
            $stmt = $conn->prepare("
                SELECT DISTINCT u.user_id
                FROM (
                    SELECT uploaded_by as user_id FROM files WHERE file_id = :file_id
                    UNION
                    SELECT shared_by as user_id FROM file_shares WHERE file_id = :file_id
                    UNION
                    SELECT reviewer_id as user_id FROM file_approvals WHERE file_id = :file_id AND reviewer_id IS NOT NULL
                    UNION
                    SELECT approver_id as user_id FROM file_approvals WHERE file_id = :file_id AND approver_id IS NOT NULL
                ) as file_users
                JOIN users u ON file_users.user_id = u.user_id
                WHERE u.user_id != :deleted_by
            ");
            $stmt->bindParam(':file_id', $file['file_id']);
            $stmt->bindParam(':deleted_by', $deletedBy);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get user who deleted the file
            $deleter = $this->getUserById($deletedBy);
            $deleterName = $deleter ? $deleter['first_name'] . ' ' . $deleter['last_name'] : 'Unknown';
            
            // Send notifications
            foreach ($users as $user) {
                $notificationData = [
                    'recipient_id' => $user['user_id'],
                    'sender_id' => $deletedBy,
                    'title' => 'File Deleted',
                    'message' => "File '{$file['original_name']}' was deleted by $deleterName",
                    'notification_type' => 'file_deleted'
                ];
                
                $this->notification->create($notificationData);
            }
            
        } catch (PDOException $e) {
            error_log("File deletion notification error: " . $e->getMessage());
        }
    }

    /**
     * Helper to format file sizes
     */
    private function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }


    /**
     * Get all workflow statuses
     */
    public function getWorkflowStatuses() {
        try {
            $conn = $this->db->connect();
            
            $stmt = $conn->prepare("
                SELECT * FROM workflow_statuses 
                ORDER BY status_order ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get workflow statuses error: " . $e->getMessage());
            return [];
        }
    }


    
}