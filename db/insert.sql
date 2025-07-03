
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