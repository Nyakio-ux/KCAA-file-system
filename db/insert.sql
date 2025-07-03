
ALTER TABLE files 
ADD COLUMN is_physical BOOLEAN DEFAULT FALSE,
ADD COLUMN received_by INT,
ADD COLUMN received_from VARCHAR(100),
ADD COLUMN received_at TIMESTAMP NULL,
ADD COLUMN destination_department_id INT,
ADD COLUMN destination_contact VARCHAR(100),
ADD COLUMN physical_location VARCHAR(255),
ADD COLUMN reference_number VARCHAR(50),
ADD FOREIGN KEY (received_by) REFERENCES users(user_id),
ADD FOREIGN KEY (destination_department_id) REFERENCES departments(department_id);

CREATE TABLE physical_file_movements (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    file_id INT NOT NULL,
    from_department_id INT,
    to_department_id INT NOT NULL,
    moved_by INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (file_id) REFERENCES files(file_id),
    FOREIGN KEY (from_department_id) REFERENCES departments(department_id),
    FOREIGN KEY (to_department_id) REFERENCES departments(department_id),
    FOREIGN KEY (moved_by) REFERENCES users(user_id)
);
=======
-- 1. Insert the users
INSERT INTO users (username, email, password_hash, first_name, last_name, phone, user_category_id)
VALUES 
    ('Winnie', 'nyawinnies@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Winnie ', 'Nyakio', '0703416091', 
     (SELECT category_id FROM user_categories WHERE category_name = 'administrator')),
     
    ('Cynthia', 'chepkemoicynthia05@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Cynthia', 'Memo', '0754497441', 
     (SELECT category_id FROM user_categories WHERE category_name = 'Senior User')),
     
    ('Williams', 'cynthiakoech005@gmail.com', '$2y$10$VahEoKuoUZXlOan90ADVyeSBi1wkOqDWzwljZQuWDlFP.jASEqjCO', 'Cyndy', 'Williams', '0703416099', 
     (SELECT category_id FROM user_categories WHERE category_name = 'Standard User'));






-- 3. Assign roles to users
-- Admin role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, NULL, u.user_id
FROM users u, roles r
WHERE u.username = 'Winnie' AND r.role_name = 'Admin';

-- Department head role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, d.department_id, 
       (SELECT user_id FROM users WHERE username = 'Winnie')
FROM users u, roles r, departments d
WHERE u.username = 'Cynthia' AND r.role_name = 'Head of Department' 
AND d.department_code = 'ICT';

-- Update department head
UPDATE departments d
JOIN users u ON u.username = 'Cynthia'
SET d.head_user_id = u.user_id
WHERE d.department_code = 'ICT';

-- Regular user role
INSERT INTO user_roles (user_id, role_id, department_id, assigned_by)
SELECT u.user_id, r.role_id, d.department_id, 
       (SELECT user_id FROM users WHERE username = 'Cynthia')
FROM users u, roles r, departments d
WHERE u.username = 'Williams' AND r.role_name = 'User' 
AND d.department_code = 'ICT';

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
    (SELECT user_id FROM users WHERE username = 'Winnie')
FROM departments d
WHERE d.department_code = 'ICT';
