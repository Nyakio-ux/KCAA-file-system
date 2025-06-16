-- 1. Insert the users
INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_category_id)
VALUES 
    ('admin1', 'livingstoneapeli@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Livingstone', 'Apeli', '0703416091', 
     (SELECT category_id FROM user_categories WHERE category_name = 'Standard User')),
     
    ('depthead1', 'tonnytrevix@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Tonny', 'Apeli', '0754497441', 
     (SELECT category_id FROM user_categories WHERE category_name = 'Senior User')),
     
    ('user1', 'livingstoneapeli@stepakash.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Ivy', 'Williams', '0703416099', 
     (SELECT category_id FROM user_categories WHERE category_name = 'Standard User'));

-- 2. Insert a department if none exists
INSERT IGNORE INTO departments (department_name, department_code, description)
VALUES ('IT Department', 'IT', 'Information Technology Department');

-- 3. Assign roles to users
-- Admin role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, NULL, u.user_id
FROM users u, roles r
WHERE u.username = 'admin1' AND r.role_name = 'Admin';

-- Department head role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, d.department_id, 
       (SELECT user_id FROM users WHERE username = 'admin1')
FROM users u, roles r, departments d
WHERE u.username = 'depthead1' AND r.role_name = 'Head of Department' 
AND d.department_code = 'IT';

-- Update department head
UPDATE departments d
JOIN users u ON u.username = 'depthead1'
SET d.head_user_id = u.user_id
WHERE d.department_code = 'IT';

-- Regular user role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, d.department_id, 
       (SELECT user_id FROM users WHERE username = 'depthead1')
FROM users u, roles r, departments d
WHERE u.username = 'user1' AND r.role_name = 'User' 
AND d.department_code = 'IT';

-- 4. Set up department head permissions 
INSERT INTO department_head_permissions (
    department_id, head_user_id, can_add_users, can_remove_users, 
    can_assign_categories, can_share_files, can_approve_files, 
    can_view_department_analytics, max_users_allowed, granted_by
)
SELECT 
    d.department_id, 
    d.head_user_id,
    TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 100,
    (SELECT user_id FROM users WHERE username = 'admin1')
FROM departments d
WHERE d.department_code = 'IT';