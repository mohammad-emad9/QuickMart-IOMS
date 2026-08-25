-- =============================================
-- QuickMart IOMS Database Creation Script
-- =============================================
-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS quickmart_db;
USE quickmart_db;
-- Drop existing tables (in reverse order of dependencies)
DROP TABLE IF EXISTS Order_Details;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Products;
DROP TABLE IF EXISTS Login_Rate_Limits;
DROP TABLE IF EXISTS Password_Reset_Tokens;
DROP TABLE IF EXISTS Staff;
-- 2. Staff Table
CREATE TABLE Staff (
    Staff_ID VARCHAR(20) PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Phone_Number VARCHAR(20),
    Role VARCHAR(20) NOT NULL DEFAULT 'Staff',
    Auth_Revision INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT chk_staff_full_name_nonempty CHECK (Full_Name REGEXP '[^[:space:]]'),
    CONSTRAINT chk_staff_email_nonempty CHECK (Email REGEXP '[^[:space:]]'),
    CONSTRAINT chk_staff_role CHECK (BINARY Role IN ('Admin', 'Manager', 'Staff')),
    CONSTRAINT chk_staff_auth_revision CHECK (Auth_Revision >= 1)
);
-- 3. Persistent login-attempt buckets. Only one-way rate keys are stored;
-- raw identifiers, client addresses, and passwords are never persisted.
CREATE TABLE Login_Rate_Limits (
    Rate_Key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    Window_Started_At DATETIME NOT NULL,
    Attempt_Count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    Last_Attempt_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Rate_Key),
    CONSTRAINT chk_login_rate_limits_attempt_count CHECK (Attempt_Count >= 0),
    KEY idx_login_rate_limits_window (Window_Started_At)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- 4. Password Reset Tokens Table
-- Store only a case-sensitive SHA-256 hexadecimal token hash. Raw reset tokens
-- must remain in the future application flow and must never be persisted here.
CREATE TABLE Password_Reset_Tokens (
    Reset_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Staff_ID VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    Token_Hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    Expires_At DATETIME NOT NULL,
    Used_At DATETIME NULL DEFAULT NULL,
    Revoked_At DATETIME NULL DEFAULT NULL,
    Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Reset_ID),
    UNIQUE KEY uq_password_reset_tokens_token_hash (Token_Hash),
    KEY idx_password_reset_tokens_staff_created (Staff_ID, Created_At),
    KEY idx_password_reset_tokens_expires_at (Expires_At),
    CONSTRAINT chk_password_reset_tokens_hash_format
        CHECK (BINARY Token_Hash REGEXP '^[0-9A-Fa-f]{64}$'),
    CONSTRAINT fk_password_reset_tokens_staff
        FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- 4. Products Table
CREATE TABLE Products (
    Product_ID VARCHAR(20) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Category VARCHAR(50) NOT NULL,
    Quantity INT NOT NULL DEFAULT 0,
    Price DECIMAL(10, 2) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Normal',
    Threshold INT NOT NULL DEFAULT 20,
    CONSTRAINT chk_products_quantity_nonnegative CHECK (Quantity >= 0),
    CONSTRAINT chk_products_price_positive CHECK (Price > 0),
    CONSTRAINT chk_products_status CHECK (Status IN ('Normal', 'Low Stock', 'Out of Stock')),
    CONSTRAINT chk_products_threshold_nonnegative CHECK (Threshold >= 0)
);
-- 5. Orders Table
CREATE TABLE Orders (
    Order_ID VARCHAR(20) PRIMARY KEY,
    Staff_ID VARCHAR(20) NOT NULL,
    Order_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    Order_Type VARCHAR(20) NOT NULL,
    Party_Name VARCHAR(100),
    CONSTRAINT chk_orders_order_type CHECK (Order_Type IN ('Sell', 'Purchase')),
    FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE RESTRICT
);
-- 6. Order Details Table
CREATE TABLE Order_Details (
    Detail_ID INT AUTO_INCREMENT PRIMARY KEY,
    Order_ID VARCHAR(20) NOT NULL,
    Product_ID VARCHAR(20) NOT NULL,
    Ordered_Qty INT NOT NULL,
    Sold_Price DECIMAL(10, 2) NOT NULL,
    CONSTRAINT chk_order_details_ordered_qty_positive CHECK (Ordered_Qty > 0),
    CONSTRAINT chk_order_details_sold_price_positive CHECK (Sold_Price > 0),
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE RESTRICT,
    CONSTRAINT fk_order_details_product
        FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE RESTRICT
);
-- Create indexes for better performance
CREATE INDEX idx_products_category ON Products(Category);
CREATE INDEX idx_products_status ON Products(Status);
CREATE INDEX idx_orders_date ON Orders(Order_Date);
CREATE INDEX idx_orders_type ON Orders(Order_Type);
