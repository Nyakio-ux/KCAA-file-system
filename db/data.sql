-- 1. Users table - stores all system users
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Roles table - defines system roles
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Departments table - stores department information
CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) UNIQUE NOT NULL,
    department_code VARCHAR(10) UNIQUE NOT NULL,
    description TEXT,
    head_user_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (head_user_id) REFERENCES users(user_id)
);

-- 4. User roles assignment table
CREATE TABLE user_roles (
    user_role_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    department_id INT,
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (assigned_by) REFERENCES users(user_id),
    UNIQUE KEY unique_user_role_dept (user_id, role_id, department_id)
);

-- 5. File categories table
CREATE TABLE file_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Files table - stores file metadata
CREATE TABLE files (
    file_id INT PRIMARY KEY AUTO_INCREMENT,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    mime_type VARCHAR(100),
    category_id INT,
    uploaded_by INT NOT NULL,
    source_department_id INT NOT NULL,
    description TEXT,
    is_confidential BOOLEAN DEFAULT FALSE,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES file_categories(category_id),
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id),
    FOREIGN KEY (source_department_id) REFERENCES departments(department_id)
);

-- 7. File sharing table - tracks file sharing between departments
CREATE TABLE file_shares (
    share_id INT PRIMARY KEY AUTO_INCREMENT,
    file_id INT NOT NULL,
    shared_by INT NOT NULL,
    shared_from_dept INT NOT NULL,
    shared_to_dept INT NOT NULL,
    share_message TEXT,
    share_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (file_id) REFERENCES files(file_id),
    FOREIGN KEY (shared_by) REFERENCES users(user_id),
    FOREIGN KEY (shared_from_dept) REFERENCES departments(department_id),
    FOREIGN KEY (shared_to_dept) REFERENCES departments(department_id)
);

-- 8. File workflow status table
CREATE TABLE workflow_statuses (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    status_order INT NOT NULL,
    is_final BOOLEAN DEFAULT FALSE
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    token_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Remember me tokens table
CREATE TABLE remember_me_tokens (
    token_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Login logs table
CREATE TABLE login_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Failed login attempts table
CREATE TABLE failed_login_attempts (
    attempt_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    attempt_time DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 9. File approvals table - tracks approval workflow
CREATE TABLE file_approvals (
    approval_id INT PRIMARY KEY AUTO_INCREMENT,
    file_id INT NOT NULL,
    share_id INT,
    department_id INT NOT NULL,
    reviewer_id INT,
    approver_id INT,
    status_id INT NOT NULL,
    review_comments TEXT,
    approval_comments TEXT,
    reviewed_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(file_id),
    FOREIGN KEY (share_id) REFERENCES file_shares(share_id),
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (approver_id) REFERENCES users(user_id),
    FOREIGN KEY (status_id) REFERENCES workflow_statuses(status_id)
);

-- 10. File access logs table - tracks all file access
CREATE TABLE file_access_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    file_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL, -- 'view', 'download', 'share', 'approve', 'reject'
    ip_address VARCHAR(45),
    user_agent TEXT,
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(file_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 11. Department file permissions table
CREATE TABLE department_permissions (
    permission_id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    target_department_id INT NOT NULL,
    permission_type VARCHAR(50) NOT NULL, -- 'read', 'write', 'share', 'approve'
    granted_by INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (target_department_id) REFERENCES departments(department_id),
    FOREIGN KEY (granted_by) REFERENCES users(user_id)
);

-- 12. System notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_id INT NOT NULL,
    sender_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(50) NOT NULL, -- 'file_shared', 'approval_needed', 'approved', 'rejected'
    related_file_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id),
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (related_file_id) REFERENCES files(file_id)
);

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('Admin', 'System administrator with full access and category management'),
('Head of Department', 'Head of department with file sharing, approval, and user management permissions'),
('User', 'Regular user with basic file access permissions');

-- Insert default workflow statuses
INSERT INTO workflow_statuses (status_name, description, status_order, is_final) VALUES
('Pending Review', 'File is waiting for review', 1, FALSE),
('Under Review', 'File is currently being reviewed', 2, FALSE),
('Pending Approval', 'File is waiting for approval', 3, FALSE),
('Approved', 'File has been approved', 4, TRUE),
('Rejected', 'File has been rejected', 5, TRUE),
('Revision Required', 'File needs revision', 6, FALSE),
('Withdrawn', 'File has been withdrawn', 7, TRUE);

-- 15. Department user invitations table - Track invitations sent by department heads
CREATE TABLE department_user_invitations (
    invitation_id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    invited_by INT NOT NULL,
    email VARCHAR(100) NOT NULL,
    user_category_id INT NOT NULL,
    invitation_token VARCHAR(255) UNIQUE NOT NULL,
    invitation_message TEXT,
    status ENUM('pending', 'accepted', 'expired') DEFAULT 'pending',
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL, -- Changed to allow NULL
    created_user_id INT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (invited_by) REFERENCES users(user_id),
    FOREIGN KEY (user_category_id) REFERENCES user_categories(category_id),
    FOREIGN KEY (created_user_id) REFERENCES users(user_id)
);
-- 16. Department head permissions table - Define what department heads can do
CREATE TABLE department_head_permissions (
    permission_id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    head_user_id INT NOT NULL,
    can_add_users BOOLEAN DEFAULT TRUE,
    can_remove_users BOOLEAN DEFAULT TRUE,
    can_assign_categories BOOLEAN DEFAULT TRUE,
    can_share_files BOOLEAN DEFAULT TRUE,
    can_approve_files BOOLEAN DEFAULT TRUE,
    can_view_department_analytics BOOLEAN DEFAULT TRUE,
    max_users_allowed INT DEFAULT 100, -- Limit on department size
    granted_by INT NOT NULL, -- Admin who granted these permissions
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (department_id) REFERENCES departments(department_id),
    FOREIGN KEY (head_user_id) REFERENCES users(user_id),
    FOREIGN KEY (granted_by) REFERENCES users(user_id),
    UNIQUE KEY unique_dept_head (department_id, head_user_id)
);
INSERT INTO user_categories (category_name, description, can_upload_files, can_share_files, can_approve_files, can_review_files, created_by) VALUES
('Standard User', 'Regular users with basic file access', TRUE, FALSE, FALSE, TRUE, 1),
('Senior User', 'Users with additional file sharing capabilities', TRUE, TRUE, FALSE, TRUE, 1),
('Finance Team', 'Users with access to financial documents', TRUE, FALSE, FALSE, TRUE, 1),
('HR Team', 'Human resources team members', TRUE, FALSE, FALSE, TRUE, 1),
('IcT Team', 'Information technology team members', TRUE, TRUE, FALSE, TRUE, 1);

-- 13. User categories table - Admin can add custom user categories
CREATE TABLE user_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON, -- Store specific permissions for this category
    can_upload_files BOOLEAN DEFAULT TRUE,
    can_share_files BOOLEAN DEFAULT FALSE,
    can_approve_files BOOLEAN DEFAULT FALSE,
    can_review_files BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Add user_category_id to users table
ALTER TABLE users ADD COLUMN user_category_id INT AFTER user_id;
ALTER TABLE users ADD FOREIGN KEY (user_category_id) REFERENCES user_categories(category_id);

-- 14. File categories management - Admin manages all categories
ALTER TABLE file_categories ADD COLUMN created_by INT NOT NULL DEFAULT 1;
ALTER TABLE file_categories ADD FOREIGN KEY (created_by) REFERENCES users(user_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_user_roles_user ON user_roles(user_id);
CREATE INDEX idx_user_roles_dept ON user_roles(department_id);
CREATE INDEX idx_files_uploaded_by ON files(uploaded_by);
CREATE INDEX idx_files_source_dept ON files(source_department_id);
CREATE INDEX idx_files_upload_date ON files(upload_date);
CREATE INDEX idx_file_shares_file ON file_shares(file_id);
CREATE INDEX idx_file_shares_dept ON file_shares(shared_to_dept);
CREATE INDEX idx_file_approvals_file ON file_approvals(file_id);
CREATE INDEX idx_file_approvals_dept ON file_approvals(department_id);
CREATE INDEX idx_file_approvals_status ON file_approvals(status_id);
CREATE INDEX idx_file_access_logs_file ON file_access_logs(file_id);
CREATE INDEX idx_file_access_logs_user ON file_access_logs(user_id);
CREATE INDEX idx_notifications_recipient ON notifications(recipient_id);
CREATE INDEX idx_user_categories_active ON user_categories(is_active);
CREATE INDEX idx_file_categories_created_by ON file_categories(created_by);
CREATE INDEX idx_dept_invitations_email ON department_user_invitations(email);
CREATE INDEX idx_dept_invitations_status ON department_user_invitations(status);
CREATE INDEX idx_dept_invitations_token ON department_user_invitations(invitation_token);
CREATE INDEX idx_dept_head_permissions ON department_head_permissions(department_id, head_user_id);

-- Create views for common queries

-- View for file workflow tracking
CREATE VIEW v_file_workflow AS
SELECT 
    f.file_id,
    f.file_name,
    f.original_name,
    d_source.department_name as source_department,
    u_uploader.first_name as uploader_first_name,
    u_uploader.last_name as uploader_last_name,
    fa.approval_id,
    d_review.department_name as reviewing_department,
    ws.status_name as current_status,
    u_reviewer.first_name as reviewer_first_name,
    u_reviewer.last_name as reviewer_last_name,
    u_approver.first_name as approver_first_name,
    u_approver.last_name as approver_last_name,
    fa.reviewed_at,
    fa.approved_at,
    fa.review_comments,
    fa.approval_comments
FROM files f
JOIN departments d_source ON f.source_department_id = d_source.department_id
JOIN users u_uploader ON f.uploaded_by = u_uploader.user_id
LEFT JOIN file_approvals fa ON f.file_id = fa.file_id
LEFT JOIN departments d_review ON fa.department_id = d_review.department_id
LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
LEFT JOIN users u_reviewer ON fa.reviewer_id = u_reviewer.user_id
LEFT JOIN users u_approver ON fa.approver_id = u_approver.user_id;

-- View for admin dashboard
CREATE VIEW v_admin_dashboard AS
SELECT 
    f.file_id,
    f.file_name,
    f.original_name,
    fc.category_name,
    d.department_name as source_department,
    CONCAT(u.first_name, ' ', u.last_name) as uploaded_by,
    f.upload_date,
    f.file_size,
    COUNT(DISTINCT fa.approval_id) as approval_count,
    COUNT(DISTINCT fs.share_id) as share_count,
    GROUP_CONCAT(DISTINCT ws.status_name ORDER BY fa.created_at DESC SEPARATOR ', ') as workflow_statuses
FROM files f
JOIN departments d ON f.source_department_id = d.department_id
JOIN users u ON f.uploaded_by = u.user_id
LEFT JOIN file_categories fc ON f.category_id = fc.category_id
LEFT JOIN file_approvals fa ON f.file_id = fa.file_id
LEFT JOIN file_shares fs ON f.file_id = fs.file_id
LEFT JOIN workflow_statuses ws ON fa.status_id = ws.status_id
GROUP BY f.file_id, f.file_name, f.original_name, fc.category_name, d.department_name, u.first_name, u.last_name, f.upload_date, f.file_size;

-- View for admin user management
CREATE VIEW v_admin_user_management AS
SELECT 
    u.user_id,
    u.username,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    uc.category_name as user_category,
    r.role_name,
    d.department_name,
    u.is_active as user_active,
    u.created_at as user_created,
    COUNT(DISTINCT f.file_id) as files_uploaded,
    COUNT(DISTINCT fa.approval_id) as approvals_made
FROM users u
LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
LEFT JOIN roles r ON ur.role_id = r.role_id
LEFT JOIN departments d ON ur.department_id = d.department_id
LEFT JOIN files f ON u.user_id = f.uploaded_by
LEFT JOIN file_approvals fa ON u.user_id = fa.approver_id
GROUP BY u.user_id, u.username, u.email, u.first_name, u.last_name, uc.category_name, r.role_name, d.department_name, u.is_active, u.created_at;

-- View for admin category management
CREATE VIEW v_admin_category_management AS
SELECT 
    'File Category' as category_type,
    fc.category_id as id,
    fc.category_name as name,
    fc.description,
    fc.is_active,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
    fc.created_at,
    COUNT(DISTINCT f.file_id) as usage_count
FROM file_categories fc
JOIN users u ON fc.created_by = u.user_id
LEFT JOIN files f ON fc.category_id = f.category_id
GROUP BY fc.category_id, fc.category_name, fc.description, fc.is_active, u.first_name, u.last_name, fc.created_at

UNION ALL

SELECT 
    'User Category' as category_type,
    uc.category_id as id,
    uc.category_name as name,
    uc.description,
    uc.is_active,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
    uc.created_at,
    COUNT(DISTINCT usr.user_id) as usage_count
FROM user_categories uc
JOIN users u ON uc.created_by = u.user_id
LEFT JOIN users usr ON uc.category_id = usr.user_category_id
GROUP BY uc.category_id, uc.category_name, uc.description, uc.is_active, u.first_name, u.last_name, uc.created_at;

-- View for department head user management
CREATE VIEW v_department_head_user_management AS
SELECT 
    d.department_id,
    d.department_name,
    u.user_id,
    u.username,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    uc.category_name as user_category,
    uc.can_upload_files,
    uc.can_share_files,
    uc.can_approve_files,
    uc.can_review_files,
    ur.assigned_at as joined_department,
    u.is_active,
    COUNT(DISTINCT f.file_id) as files_uploaded,
    COUNT(DISTINCT fa.approval_id) as approvals_made
FROM departments d
JOIN user_roles ur ON d.department_id = ur.department_id AND ur.is_active = TRUE
JOIN users u ON ur.user_id = u.user_id
LEFT JOIN user_categories uc ON u.user_category_id = uc.category_id
LEFT JOIN files f ON u.user_id = f.uploaded_by
LEFT JOIN file_approvals fa ON u.user_id = fa.approver_id
GROUP BY d.department_id, d.department_name, u.user_id, u.username, u.email, u.first_name, u.last_name, 
         uc.category_name, uc.can_upload_files, uc.can_share_files, uc.can_approve_files, uc.can_review_files, 
         ur.assigned_at, u.is_active;

-- View for department head invitations management
CREATE VIEW v_department_invitations AS
SELECT 
    i.invitation_id,
    i.email,
    d.department_name,
    uc.category_name as invited_as_category,
    CONCAT(u.first_name, ' ', u.last_name) as invited_by_name,
    i.invitation_message,
    i.status,
    i.invited_at,
    i.expires_at,
    i.responded_at,
    CASE 
        WHEN i.expires_at < NOW() AND i.status = 'pending' THEN 'expired'
        ELSE i.status 
    END as current_status
FROM department_user_invitations i
JOIN departments d ON i.department_id = d.department_id
JOIN users u ON i.invited_by = u.user_id
JOIN user_categories uc ON i.user_category_id = uc.category_id;

-- View for department head permissions check
CREATE VIEW v_department_head_permissions AS
SELECT 
    d.department_id,
    d.department_name,
    u.user_id as head_user_id,
    CONCAT(u.first_name, ' ', u.last_name) as head_name,
    dhp.can_add_users,
    dhp.can_remove_users,
    dhp.can_assign_categories,
    dhp.can_share_files,
    dhp.can_approve_files,
    dhp.can_view_department_analytics,
    dhp.max_users_allowed,
    COUNT(DISTINCT ur.user_id) as current_user_count,
    (dhp.max_users_allowed - COUNT(DISTINCT ur.user_id)) as remaining_slots,
    dhp.granted_at,
    dhp.is_active
FROM departments d
JOIN users u ON d.head_user_id = u.user_id
LEFT JOIN department_head_permissions dhp ON d.department_id = dhp.department_id AND d.head_user_id = dhp.head_user_id
LEFT JOIN user_roles ur ON d.department_id = ur.department_id AND ur.is_active = TRUE
GROUP BY d.department_id, d.department_name, u.user_id, u.first_name, u.last_name, 
         dhp.can_add_users, dhp.can_remove_users, dhp.can_assign_categories, dhp.can_share_files, 
         dhp.can_approve_files, dhp.can_view_department_analytics, dhp.max_users_allowed, dhp.granted_at, dhp.is_active;