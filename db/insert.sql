-- First, remove the foreign key constraint on receiver
ALTER TABLE `files` DROP FOREIGN KEY `files_ibfk_4`;

-- Remove the index on receiver 
ALTER TABLE `files` DROP INDEX `idx_files_receiver`;

-- Change the receiver column from int to varchar to store names
ALTER TABLE `files` MODIFY COLUMN `receiver` VARCHAR(255) DEFAULT NULL;

-- Optional: Add back an index for performance (not a foreign key)
ALTER TABLE `files` ADD INDEX `idx_files_receiver` (`receiver`);

-- If you want to update existing records with receiver = 0 to NULL
UPDATE `files` SET `receiver` = NULL WHERE `receiver` = '0' OR `receiver` = '';