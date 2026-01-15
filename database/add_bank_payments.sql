-- Migration: Add Bank Payment Methods Feature
-- Created: 2026-01-15
-- Description: Adds support for traditional bank payment methods alongside cryptocurrency

USE blockpayoption;

-- Create bank_payment_methods table
CREATE TABLE IF NOT EXISTS bank_payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(255) NOT NULL,
    account_holder_name VARCHAR(255) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    routing_number VARCHAR(50),
    swift_code VARCHAR(20),
    iban VARCHAR(50),
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

-- Alter payment_links table to support both crypto and bank payments
ALTER TABLE payment_links 
ADD COLUMN payment_type ENUM('crypto', 'bank') DEFAULT 'crypto' AFTER payment_method_id,
ADD COLUMN bank_payment_method_id INT NULL AFTER payment_type,
ADD INDEX idx_payment_type (payment_type),
ADD INDEX idx_bank_payment_method_id (bank_payment_method_id);

-- Add foreign key constraint for bank payment methods
ALTER TABLE payment_links 
ADD CONSTRAINT fk_bank_payment_method 
FOREIGN KEY (bank_payment_method_id) REFERENCES bank_payment_methods(id) ON DELETE SET NULL;

-- Make payment_method_id nullable since bank payments won't use it
ALTER TABLE payment_links 
MODIFY COLUMN payment_method_id INT NULL;

-- Insert sample bank payment methods
INSERT INTO bank_payment_methods 
(bank_name, account_holder_name, account_number, routing_number, account_type, currency, country, instructions, display_order, is_active) 
VALUES
(
    'Chase Bank',
    'BlockPayOption LLC',
    '123456789012',
    '021000021',
    'business',
    'USD',
    'United States',
    'Please include your invoice number in the transfer reference. Transfers typically take 1-3 business days to process.',
    1,
    1
),
(
    'HSBC International',
    'BlockPayOption Ltd',
    'GB29NWBK60161331926819',
    NULL,
    'business',
    'EUR',
    'United Kingdom',
    'For international transfers, please use SWIFT code HBUKGB4B. Include your payment reference in the transfer details. Processing time: 2-5 business days.',
    2,
    1
);

-- Update HSBC with SWIFT and IBAN
UPDATE bank_payment_methods 
SET swift_code = 'HBUKGB4B', 
    iban = 'GB29NWBK60161331926819'
WHERE bank_name = 'HSBC International';

-- Sample bank payment link (for demonstration)
INSERT INTO payment_links (unique_id, payment_type, bank_payment_method_id, amount, currency, recipient_email, status, expires_at) 
VALUES
(CONCAT('bank-demo-', UNIX_TIMESTAMP()), 'bank', 1, 500.00, 'USD', 'customer@example.com', 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY));
