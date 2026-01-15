-- BlockPayOption Database Migration
-- Add Bank Payment Methods Support
-- Created: 2026-01-15

USE blockpayoption;

-- Create bank_payment_methods table
CREATE TABLE IF NOT EXISTS bank_payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(255) NOT NULL,
    account_holder_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    routing_number VARCHAR(50),
    swift_bic_code VARCHAR(50),
    bank_address TEXT,
    account_type ENUM('checking', 'savings', 'business') DEFAULT 'checking',
    currency VARCHAR(10) DEFAULT 'USD',
    country VARCHAR(100),
    instructions TEXT,
    logo_path VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order),
    INDEX idx_currency (currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modify payment_links table to support both crypto and bank payments
ALTER TABLE payment_links 
    ADD COLUMN payment_type ENUM('crypto', 'bank') DEFAULT 'crypto' AFTER payment_method_id,
    ADD COLUMN bank_payment_method_id INT NULL AFTER payment_type,
    ADD INDEX idx_payment_type (payment_type),
    ADD INDEX idx_bank_payment_method_id (bank_payment_method_id),
    ADD CONSTRAINT fk_bank_payment_method 
        FOREIGN KEY (bank_payment_method_id) 
        REFERENCES bank_payment_methods(id) 
        ON DELETE CASCADE;

-- Make payment_method_id nullable (it's NULL for bank payments)
ALTER TABLE payment_links 
    MODIFY COLUMN payment_method_id INT NULL;

-- Drop old foreign key constraint (if exists)
-- Note: The constraint name may vary. This attempts to drop the common auto-generated name.
-- If it fails, manually drop the constraint with: SHOW CREATE TABLE payment_links; to find the name
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'blockpayoption' 
    AND TABLE_NAME = 'payment_links' 
    AND COLUMN_NAME = 'payment_method_id' 
    AND REFERENCED_TABLE_NAME = 'payment_methods'
    LIMIT 1
);

SET @sql_drop = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE payment_links DROP FOREIGN KEY ', @constraint_name), 
    'SELECT "No foreign key constraint found to drop"');

PREPARE stmt FROM @sql_drop;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-add foreign key with proper naming
ALTER TABLE payment_links
    ADD CONSTRAINT fk_crypto_payment_method
        FOREIGN KEY (payment_method_id)
        REFERENCES payment_methods(id)
        ON DELETE CASCADE;

-- Insert sample bank payment methods

-- Chase Bank (USA)
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, account_type, currency, country, instructions, display_order, is_active) 
VALUES
('Chase Bank', 'BlockPayOption LLC', '1234567890', '021000021', 'business', 'USD', 'United States', 
'Please use your unique payment ID as the reference when making the transfer. Transfers typically take 1-3 business days to process.', 
1, 1);

-- Bank of America (USA)
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, account_type, currency, country, instructions, display_order, is_active) 
VALUES
('Bank of America', 'BlockPayOption Inc', '9876543210', '026009593', 'checking', 'USD', 'United States', 
'Domestic wire transfers: Use routing number 026009593. International wire transfers: Use SWIFT code BOFAUS3N. Include payment reference in transfer notes.', 
2, 1);

-- HSBC (International)
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, swift_bic_code, bank_address, account_type, currency, country, instructions, display_order, is_active) 
VALUES
('HSBC Bank', 'BlockPayOption Global Ltd', 'GB29NWBK60161331926819', NULL, 'HSBCGB2L', '8 Canada Square, London E14 5HQ, United Kingdom', 'business', 'GBP', 'United Kingdom', 
'For international transfers, use SWIFT code HSBCGB2L. IBAN: GB29NWBK60161331926819. Please include your payment reference number in the transfer details.', 
3, 1);

-- Wells Fargo (USA)
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, account_type, currency, country, instructions, display_order, is_active) 
VALUES
('Wells Fargo Bank', 'BlockPayOption Services', '5544332211', '121000248', 'business', 'USD', 'United States', 
'ACH transfers are accepted. For wire transfers, contact support for additional details. Processing time: 1-3 business days.', 
4, 1);

-- Citibank (International)
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, swift_bic_code, account_type, currency, country, instructions, display_order, is_active) 
VALUES
('Citibank', 'BlockPayOption International', '7788990011', '021000089', 'CITIUS33', 'business', 'EUR', 'Germany', 
'SWIFT transfers accepted. Use SWIFT code CITIUS33 for international transfers. Include payment ID in reference field.', 
5, 1);
