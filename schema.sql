CREATE DATABASE IF NOT EXISTS return_warranty_tracker;
USE return_warranty_tracker;

CREATE TABLE IF NOT EXISTS returns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serial_number VARCHAR(100) NOT NULL,
    invoice_number VARCHAR(100) NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    return_reason VARCHAR(500) NOT NULL,
    return_date DATE NOT NULL,
    warranty_expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_serial_number (serial_number),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_warranty_expiry_date (warranty_expiry_date)
);
