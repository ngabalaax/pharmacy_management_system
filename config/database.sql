-- Create database with proper character set
CREATE DATABASE IF NOT EXISTS hanan_pharmacy 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hanan_pharmacy;

-- Users table with enhanced security
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('manager', 'pharmacist', 'store_coordinator', 'cashier') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Drugs table with inventory management features
CREATE TABLE drugs (
    drug_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    generic_name VARCHAR(100),
    batch_number VARCHAR(50) NOT NULL,
    barcode VARCHAR(50) UNIQUE,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    expiry_date DATE NOT NULL,
    manufacturer VARCHAR(100),
    supplier VARCHAR(100),
    storage_conditions VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_expiry (expiry_date),
    INDEX idx_supplier (supplier),
    INDEX idx_active (is_active),
    CHECK (quantity >= 0),
    CHECK (reorder_level >= 0),
    CHECK (unit_price >= 0),
    CHECK (selling_price >= unit_price)
) ENGINE=InnoDB;

-- Suppliers table
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    tax_id VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB;

-- Sales table with payment information
CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(20),
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile_money', 'insurance') NOT NULL,
    payment_status ENUM('pending', 'paid', 'partially_paid') DEFAULT 'paid',
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_invoice (invoice_number),
    INDEX idx_date (sale_date),
    INDEX idx_customer (customer_name),
    INDEX idx_payment_status (payment_status),
    CHECK (subtotal >= 0),
    CHECK (discount >= 0),
    CHECK (tax_amount >= 0),
    CHECK (total_amount >= 0)
) ENGINE=InnoDB;

-- Sale items table
CREATE TABLE sale_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    drug_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (drug_id) REFERENCES drugs(drug_id),
    INDEX idx_sale (sale_id),
    INDEX idx_drug (drug_id),
    CHECK (quantity > 0),
    CHECK (unit_price >= 0),
    CHECK (total_price >= 0)
) ENGINE=InnoDB;

-- Inventory adjustments (stock movements)
CREATE TABLE inventory_adjustments (
    adjustment_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_id INT NOT NULL,
    user_id INT NOT NULL,
    adjustment_type ENUM('purchase', 'sale', 'return', 'damage', 'expiry', 'adjustment') NOT NULL,
    quantity_change INT NOT NULL,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    reference_id INT COMMENT 'ID of related sale/purchase',
    notes TEXT,
    adjustment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drug_id) REFERENCES drugs(drug_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_drug (drug_id),
    INDEX idx_date (adjustment_date),
    INDEX idx_type (adjustment_type)
) ENGINE=InnoDB;

-- Expiry alerts table with enhanced tracking
CREATE TABLE expiry_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_id INT NOT NULL,
    user_id INT COMMENT 'User who addressed the alert',
    alert_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL,
    status ENUM('pending', 'resolved', 'discarded') DEFAULT 'pending',
    action_taken TEXT,
    FOREIGN KEY (drug_id) REFERENCES drugs(drug_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_status (status),
    INDEX idx_drug (drug_id)
) ENGINE=InnoDB;

-- Audit log for tracking changes
CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- Create initial admin user
INSERT INTO users (username, password, first_name, last_name, email, role)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'System',
    'Admin',
    'admin@hananpharmacy.com',
    'manager'
);

-- Create trigger for inventory updates
DELIMITER //
CREATE TRIGGER after_drug_update
AFTER UPDATE ON drugs
FOR EACH ROW
BEGIN
    IF OLD.quantity != NEW.quantity THEN
        INSERT INTO inventory_adjustments (
            drug_id, user_id, adjustment_type, quantity_change,
            previous_quantity, new_quantity, notes
        ) VALUES (
            NEW.drug_id, NULL, 'adjustment', 
            NEW.quantity - OLD.quantity,
            OLD.quantity, NEW.quantity,
            'System automatic quantity adjustment'
        );
    END IF;
END//
DELIMITER ;

-- Create view for low stock alerts
CREATE VIEW low_stock_alert AS
SELECT 
    d.drug_id, d.name, d.batch_number, d.quantity, 
    d.reorder_level, d.category, d.supplier,
    CASE 
        WHEN d.quantity = 0 THEN 'Out of Stock'
        WHEN d.quantity <= d.reorder_level THEN 'Low Stock'
        ELSE 'Adequate Stock'
    END AS stock_status
FROM drugs d
WHERE d.quantity <= d.reorder_level AND d.is_active = TRUE;

-- Create view for expiry alerts
CREATE VIEW expiry_alert AS
SELECT 
    d.drug_id, d.name, d.batch_number, 
    d.quantity, d.expiry_date, d.category,
    DATEDIFF(d.expiry_date, CURDATE()) AS days_remaining,
    CASE 
        WHEN d.expiry_date <= CURDATE() THEN 'Expired'
        WHEN DATEDIFF(d.expiry_date, CURDATE()) <= 30 THEN 'Expiring Soon'
        ELSE 'Valid'
    END AS expiry_status
FROM drugs d
WHERE (d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AND d.is_active = TRUE;