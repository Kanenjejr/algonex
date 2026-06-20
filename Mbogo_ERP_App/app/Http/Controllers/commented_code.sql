-- Update sales/proforma numeric columns to allow 6 decimal places.
-- Run in MySQL/MariaDB after taking a database backup.

START TRANSACTION;

ALTER TABLE proformas
    MODIFY subtotal DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY vat DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY total DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY paid_amount DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE services
    MODIFY price DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE proforma_items
    MODIFY qty DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY price DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY total DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE invoices
    MODIFY exchange_rate DECIMAL(20,6) NOT NULL DEFAULT 1.000000,
    MODIFY total_tzs DECIMAL(20,6) NULL,
    MODIFY sub_total DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY vat_rate DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY tax DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY discount DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY total DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY paid_amount DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY balance DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE invoice_items
    MODIFY qty DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY price DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY total DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE deliveries
    MODIFY approved_qty DECIMAL(20,6) NULL,
    MODIFY delivery_income_amount DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY delivery_income_exchange_rate DECIMAL(20,6) NOT NULL DEFAULT 1.000000;

ALTER TABLE delivery_items
    MODIFY quantity DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY unit_price DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY total DECIMAL(20,6) NOT NULL DEFAULT 0.000000,
    MODIFY issued_qty DECIMAL(20,6) NOT NULL DEFAULT 0.000000;

COMMIT;

-- Backup your database before running this.
-- This keeps existing routes and code intact; it only increases decimal precision.

ALTER TABLE purchase_orders
    MODIFY exchange_rate DECIMAL(18,6) NOT NULL DEFAULT 1.000000,
    MODIFY vat_rate DECIMAL(18,6) NOT NULL DEFAULT 18.000000,
    MODIFY sub_total DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY vat_amount DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY discount DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY total_amount DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY total_tzs DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY amount_paid DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY balance DECIMAL(18,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE purchase_order_items
    MODIFY qty DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY received_qty DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY balance_qty DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY unit_price DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY sub_total DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY vat_amount DECIMAL(18,6) NOT NULL DEFAULT 0.000000,
    MODIFY total_price DECIMAL(18,6) NOT NULL DEFAULT 0.000000;



-- 16/06/2026
SET @db_name = DATABASE();
SET @currency_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'proformas'
      AND COLUMN_NAME = 'currency'
);

SET @sql = IF(
    @currency_column_exists = 0,
    'ALTER TABLE proformas ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT ''TZS'' AFTER invoice_type',
    'SELECT ''currency column already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE proformas
SET currency = CASE
    WHEN invoice_type = 'export' THEN 'USD'
    ELSE 'TZS'
END
WHERE currency IS NULL OR currency = '';


-- 17/06/2026 codes.


ALTER TABLE deliveries
    ADD COLUMN IF NOT EXISTS customs_manifest_no VARCHAR(255) NULL AFTER delivery_note_no,
    ADD COLUMN IF NOT EXISTS export_reference_no VARCHAR(255) NULL AFTER customs_manifest_no,
    ADD COLUMN IF NOT EXISTS transporter_name VARCHAR(255) NULL AFTER transport_mode,
    ADD COLUMN IF NOT EXISTS truck2_registration_no VARCHAR(255) NULL AFTER vehicle_no,
    ADD COLUMN IF NOT EXISTS trailer_registration_no VARCHAR(255) NULL AFTER truck2_registration_no,
    ADD COLUMN IF NOT EXISTS container2_no VARCHAR(255) NULL AFTER container_no,
    ADD COLUMN IF NOT EXISTS container3_no VARCHAR(255) NULL AFTER container2_no,
    ADD COLUMN IF NOT EXISTS total_gross_weight VARCHAR(255) NULL AFTER approved_qty,
    ADD COLUMN IF NOT EXISTS clearing_agent VARCHAR(255) NULL AFTER authority,
    ADD COLUMN IF NOT EXISTS bill_of_entry_no VARCHAR(255) NULL AFTER clearing_agent,
    ADD COLUMN IF NOT EXISTS exit_entry_no VARCHAR(255) NULL AFTER bill_of_entry_no;

ALTER TABLE delivery_items
    ADD COLUMN IF NOT EXISTS packages_no_type VARCHAR(255) NULL AFTER quantity,
    ADD COLUMN IF NOT EXISTS gross_weight VARCHAR(255) NULL AFTER packages_no_type;

-- Optional index for fast lookup by manifest number.
CREATE INDEX IF NOT EXISTS deliveries_customs_manifest_no_index ON deliveries (customs_manifest_no);
