-- Create the database
CREATE DATABASE IF NOT EXISTS cadetportal;
USE cadetportal;

-- Create the applications table
CREATE TABLE applications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    address TEXT DEFAULT NULL,
    state VARCHAR(100) DEFAULT NULL,
    lga VARCHAR(100) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(50) NOT NULL,
    transaction_reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
