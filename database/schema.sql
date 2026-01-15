-- BlockPayOption Database Schema
-- Created: 2026-01-15

-- Create database
CREATE DATABASE IF NOT EXISTS blockpayoption CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blockpayoption;

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payment_methods
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(20) NOT NULL,
    logo_path VARCHAR(255) DEFAULT NULL,
    wallet_address VARCHAR(255) NOT NULL,
    qr_code_path VARCHAR(255) DEFAULT NULL,
    networks TEXT,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tutorials
CREATE TABLE IF NOT EXISTS tutorials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    category VARCHAR(50) DEFAULT 'general',
    image_path VARCHAR(255) DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_is_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: platforms
CREATE TABLE IF NOT EXISTS platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_path VARCHAR(255) DEFAULT NULL,
    description TEXT,
    website_url VARCHAR(255) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    pros TEXT,
    cons TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_rating (rating),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payment_links
CREATE TABLE IF NOT EXISTS payment_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_id VARCHAR(50) NOT NULL UNIQUE,
    payment_method_id INT NOT NULL,
    amount DECIMAL(18,8) NOT NULL,
    currency VARCHAR(20) NOT NULL,
    recipient_email VARCHAR(100) DEFAULT NULL,
    status ENUM('pending', 'completed', 'expired') DEFAULT 'pending',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE CASCADE,
    INDEX idx_unique_id (unique_id),
    INDEX idx_status (status),
    INDEX idx_payment_method_id (payment_method_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: admin123 (hashed using password_hash with PASSWORD_DEFAULT)
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@blockpayoption.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample payment methods
INSERT INTO payment_methods (name, symbol, wallet_address, networks, description, display_order, is_active) VALUES
('Bitcoin', 'BTC', 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh', 'Bitcoin Network, Lightning Network', 'Bitcoin is the first and most widely recognized cryptocurrency. It operates on a decentralized network and is often referred to as digital gold.', 1, 1),
('Ethereum', 'ETH', '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb', 'Ethereum Mainnet, Polygon', 'Ethereum is a decentralized platform that runs smart contracts. It is the second-largest cryptocurrency by market capitalization.', 2, 1),
('USDT', 'USDT', 'TQrZ5FbCvGvhWxYXvJBYhkCVKwXcZnCGqN', 'Ethereum (ERC-20), Tron (TRC-20), BSC (BEP-20)', 'Tether (USDT) is a stablecoin pegged to the US Dollar. It provides price stability in the volatile cryptocurrency market.', 3, 1);

-- Insert sample tutorials
INSERT INTO tutorials (title, slug, content, category, display_order, is_published) VALUES
('What is Cryptocurrency?', 'what-is-cryptocurrency', '<h2>Introduction to Cryptocurrency</h2><p>Cryptocurrency is a digital or virtual currency that uses cryptography for security. Unlike traditional currencies issued by governments (fiat currency), cryptocurrencies operate on decentralized networks based on blockchain technology.</p><h3>Key Features:</h3><ul><li><strong>Decentralization:</strong> No central authority controls the currency</li><li><strong>Security:</strong> Uses advanced cryptographic techniques</li><li><strong>Transparency:</strong> All transactions are recorded on a public ledger</li><li><strong>Anonymity:</strong> Users can remain pseudonymous</li></ul><h3>How It Works:</h3><p>Cryptocurrencies use blockchain technology - a distributed ledger that records all transactions across a network of computers. This makes them secure, transparent, and resistant to fraud.</p><h3>Popular Cryptocurrencies:</h3><ul><li>Bitcoin (BTC) - The first cryptocurrency</li><li>Ethereum (ETH) - Platform for smart contracts</li><li>USDT - Stablecoin pegged to USD</li></ul>', 'beginner', 1, 1),

('How to Create a Crypto Wallet', 'how-to-create-crypto-wallet', '<h2>Step-by-Step Guide to Creating Your First Crypto Wallet</h2><p>A cryptocurrency wallet is a digital tool that allows you to store, send, and receive cryptocurrencies. Here\'s how to set one up:</p><h3>Step 1: Choose a Wallet Type</h3><ul><li><strong>Software Wallets:</strong> Apps on your phone or computer (e.g., Trust Wallet, MetaMask)</li><li><strong>Hardware Wallets:</strong> Physical devices for maximum security (e.g., Ledger, Trezor)</li><li><strong>Web Wallets:</strong> Online wallets accessible from browsers</li></ul><h3>Step 2: Download and Install</h3><p>For beginners, we recommend starting with Trust Wallet or MetaMask:</p><ol><li>Visit the official website or app store</li><li>Download the official application</li><li>Install on your device</li></ol><h3>Step 3: Create Your Wallet</h3><ol><li>Open the app and select "Create New Wallet"</li><li>Write down your recovery phrase (12-24 words) - KEEP THIS SAFE!</li><li>Set a strong password</li><li>Confirm your recovery phrase</li></ol><h3>Step 4: Secure Your Wallet</h3><ul><li>Never share your recovery phrase with anyone</li><li>Store it in a safe, offline location</li><li>Enable two-factor authentication if available</li><li>Make backup copies of your recovery phrase</li></ul><h3>Step 5: Receive Your First Crypto</h3><p>Your wallet will have a unique address (like a bank account number). Share this address to receive cryptocurrency.</p>', 'beginner', 2, 1),

('Making Your First Crypto Payment', 'making-first-crypto-payment', '<h2>How to Make Your First Cryptocurrency Payment</h2><p>Sending cryptocurrency is simple once you understand the basics. Follow this guide to make your first payment safely.</p><h3>What You\'ll Need:</h3><ul><li>A cryptocurrency wallet with funds</li><li>The recipient\'s wallet address</li><li>Enough crypto to cover the amount plus transaction fees</li></ul><h3>Step-by-Step Process:</h3><h4>1. Open Your Wallet</h4><p>Launch your cryptocurrency wallet application and unlock it using your password or biometric authentication.</p><h4>2. Select "Send" or "Transfer"</h4><p>Look for the send/transfer button in your wallet interface.</p><h4>3. Enter Recipient Details</h4><ul><li><strong>Wallet Address:</strong> Copy and paste the recipient\'s address (or scan their QR code)</li><li><strong>Amount:</strong> Enter the amount you wish to send</li><li><strong>Network:</strong> Ensure you select the correct network (e.g., Ethereum Mainnet, BSC, Polygon)</li></ul><h4>4. Review Transaction Details</h4><p>Carefully check:</p><ul><li>Recipient address is correct (double-check!)</li><li>Amount is accurate</li><li>Network/blockchain is correct</li><li>Transaction fee is acceptable</li></ul><h4>5. Confirm and Send</h4><p>Once you\'ve verified everything, confirm the transaction. The crypto will be sent to the blockchain network.</p><h4>6. Wait for Confirmation</h4><p>Transactions typically take a few minutes to several hours depending on network congestion and the blockchain used.</p><h3>Important Safety Tips:</h3><ul><li>Always double-check the recipient address - transactions cannot be reversed</li><li>Start with a small test transaction if sending a large amount</li><li>Ensure you\'re using the correct network</li><li>Keep transaction records for your reference</li><li>Never share your private keys or recovery phrase</li></ul><h3>Transaction Fees:</h3><p>Every crypto transaction requires a small fee paid to network validators. During high network activity, fees may increase. You can usually choose fee levels: slow (cheaper), standard, or fast (more expensive).</p>', 'intermediate', 3, 1);

-- Insert sample platforms
INSERT INTO platforms (name, description, website_url, rating, pros, cons, display_order, is_active) VALUES
('Binance', 'Binance is the world\'s largest cryptocurrency exchange by trading volume. It offers a wide range of cryptocurrencies, low fees, and advanced trading features.', 'https://www.binance.com', 4.50, 'Largest selection of cryptocurrencies|Low trading fees|Advanced trading tools|High liquidity|Mobile app available|Staking and earning options', 'Complex interface for beginners|Regulatory scrutiny in some countries|Customer support can be slow', 1, 1),

('Coinbase', 'Coinbase is one of the most user-friendly cryptocurrency exchanges, perfect for beginners. It offers a simple interface and is available in many countries.', 'https://www.coinbase.com', 4.20, 'Very user-friendly interface|Regulated and compliant|Excellent security|Insurance on deposits|Educational resources|Easy fiat on/off ramps', 'Higher fees compared to competitors|Limited advanced trading features on basic platform|Fewer cryptocurrencies than Binance', 2, 1),

('Kraken', 'Kraken is a veteran cryptocurrency exchange known for its security and wide range of supported currencies. It offers both basic and advanced trading options.', 'https://www.kraken.com', 4.30, 'Strong security track record|Wide range of cryptocurrencies|Advanced trading features|Competitive fees|Futures and margin trading|Good customer support', 'Interface can be overwhelming for beginners|Bank transfer delays in some regions|Limited payment methods in certain countries', 3, 1),

('Crypto.com', 'Crypto.com offers a comprehensive ecosystem including exchange, wallet, visa card, and DeFi services. Known for its rewards and cashback programs.', 'https://www.crypto.com', 4.10, 'Crypto visa debit card|Cashback rewards program|Wide range of services|User-friendly mobile app|Competitive staking rewards|No trading fees on some pairs', 'Card benefits require staking CRO|Spread can be high on instant buys|Withdrawal fees on some currencies|Geographic restrictions apply', 4, 1);

-- Sample payment link (for demonstration)
INSERT INTO payment_links (unique_id, payment_method_id, amount, currency, recipient_email, status, expires_at) VALUES
('demo-' || UNIX_TIMESTAMP(), 1, 0.0015, 'BTC', 'customer@example.com', 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY));
