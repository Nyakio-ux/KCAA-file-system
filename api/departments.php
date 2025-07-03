<?php
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/auth.php';

// Create database connection
$db = new Database();

// Authenticate request
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Get all active departments
    $conn = $db->connect();
    $stmt = $conn->prepare("
        SELECT 
            department_id, 
            department_name, 
            department_code,
            head_user_id,
            is_active
        FROM departments
        WHERE is_active = TRUE
        ORDER BY department_name ASC
    ");
    $stmt->execute();
    
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get department heads information
    $departmentIds = array_column($departments, 'department_id');
    if (!empty($departmentIds)) {
        $placeholders = implode(',', array_fill(0, count($departmentIds), '?'));
        $stmt = $conn->prepare("
            SELECT 
                u.user_id,
                CONCAT(u.first_name, ' ', u.last_name) as full_name,
                u.email,
                d.department_id
            FROM users u
            JOIN departments d ON u.user_id = d.head_user_id
            WHERE d.department_id IN ($placeholders)
        ");
        $stmt->execute($departmentIds);
        $heads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map heads to departments
        $headsMap = [];
        foreach ($heads as $head) {
            $headsMap[$head['department_id']] = [
                'head_name' => $head['full_name'],
                'head_email' => $head['email']
            ];
        }
        
        // Merge head info with departments
        foreach ($departments as &$department) {
            if (isset($headsMap[$department['department_id']])) {
                $department = array_merge($department, $headsMap[$department['department_id']]);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'departments' => $departments
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}