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
DROP TABLE IF EXISTS Staff;
-- 2. Staff Table
CREATE TABLE Staff (
    Staff_ID VARCHAR(20) PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Phone_Number VARCHAR(20),
    Role VARCHAR(20) DEFAULT 'Staff'
);
-- 3. Products Table
CREATE TABLE Products (
    Product_ID VARCHAR(20) PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Category VARCHAR(50) NOT NULL,
    Quantity INT NOT NULL DEFAULT 0,
    Price DECIMAL(10, 2) NOT NULL,
    Status VARCHAR(20) DEFAULT 'Normal',
    Threshold INT DEFAULT 20
);
-- 4. Orders Table
CREATE TABLE Orders (
    Order_ID VARCHAR(20) PRIMARY KEY,
    Staff_ID VARCHAR(20) NOT NULL,
    Order_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    Order_Type VARCHAR(20) NOT NULL,
    Party_Name VARCHAR(100),
    FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE CASCADE
);
-- 5. Order Details Table
CREATE TABLE Order_Details (
    Detail_ID INT AUTO_INCREMENT PRIMARY KEY,
    Order_ID VARCHAR(20) NOT NULL,
    Product_ID VARCHAR(20) NOT NULL,
    Ordered_Qty INT NOT NULL,
    Sold_Price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE,
    FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE CASCADE
);
-- Create indexes for better performance
CREATE INDEX idx_staff_email ON Staff(Email);
CREATE INDEX idx_products_category ON Products(Category);
CREATE INDEX idx_products_status ON Products(Status);
CREATE INDEX idx_orders_date ON Orders(Order_Date);
CREATE INDEX idx_orders_type ON Orders(Order_Type);