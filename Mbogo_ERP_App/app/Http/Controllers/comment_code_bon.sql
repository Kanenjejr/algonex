START TRANSACTION;

SET @db_name = DATABASE();

SET @exchange_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'proformas'
      AND COLUMN_NAME = 'exchange_rate'
);

SET @sql = IF(
    @exchange_column_exists = 0,
    'ALTER TABLE proformas ADD COLUMN exchange_rate DECIMAL(20,6) NULL AFTER currency',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

COMMIT;
