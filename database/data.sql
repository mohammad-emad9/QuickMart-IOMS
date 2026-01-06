-- =============================================
-- QuickMart IOMS Seed Data Script
-- Run this AFTER schema.sql
-- =============================================
USE quickmart_db;
-- =============================================
-- 1. Staff Sample Data
-- Password for all: 'admin123' (hashed with PHP password_hash)
-- =============================================
INSERT INTO Staff (
        Staff_ID,
        Full_Name,
        Email,
        Password,
        Phone_Number,
        Role
    )
VALUES (
        'STF001',
        'Ahmad Admin',
        'admin@quickmart.com',
        '$2y$10$DbKG.YXHd4fMNJ9FI2KFQOLWnkQB7MqPcF9svREqwfFOF9Lo78wLq',
        '0501234567',
        'Admin'
    ),
    (
        'STF002',
        'Sarah Manager',
        'manager@quickmart.com',
        '$2y$10$DbKG.YXHd4fMNJ9FI2KFQOLWnkQB7MqPcF9svREqwfFOF9Lo78wLq',
        '0507654321',
        'Manager'
    ),
    (
        'STF003',
        'Mohammed Staff',
        'staff@quickmart.com',
        '$2y$10$DbKG.YXHd4fMNJ9FI2KFQOLWnkQB7MqPcF9svREqwfFOF9Lo78wLq',
        '0509876543',
        'Staff'
    );
-- =============================================
-- 2. Products Sample Data
-- =============================================
INSERT INTO Products (
        Product_ID,
        Name,
        Category,
        Quantity,
        Price,
        Status,
        Threshold
    )
VALUES (
        'PRD001',
        'iPhone 15 Pro',
        'Electronics',
        25,
        4999.00,
        'Normal',
        5
    ),
    (
        'PRD002',
        'Samsung Galaxy S24',
        'Electronics',
        30,
        3999.00,
        'Normal',
        5
    ),
    (
        'PRD003',
        'MacBook Pro 14"',
        'Computers',
        10,
        8999.00,
        'Normal',
        3
    ),
    (
        'PRD004',
        'Dell XPS 15',
        'Computers',
        8,
        6499.00,
        'Normal',
        3
    ),
    (
        'PRD005',
        'AirPods Pro 2',
        'Accessories',
        50,
        999.00,
        'Normal',
        10
    ),
    (
        'PRD006',
        'Sony WH-1000XM5',
        'Accessories',
        20,
        1499.00,
        'Normal',
        5
    ),
    (
        'PRD007',
        'iPad Pro 12.9"',
        'Tablets',
        15,
        5499.00,
        'Normal',
        5
    ),
    (
        'PRD008',
        'Samsung Tab S9',
        'Tablets',
        12,
        3499.00,
        'Normal',
        5
    ),
    (
        'PRD009',
        'Apple Watch Ultra 2',
        'Wearables',
        4,
        3699.00,
        'Low Stock',
        5
    ),
    (
        'PRD010',
        'USB-C Charger 65W',
        'Accessories',
        2,
        149.00,
        'Low Stock',
        10
    );
-- =============================================
-- 3. Orders Sample Data
-- =============================================
INSERT INTO Orders (
        Order_ID,
        Staff_ID,
        Order_Date,
        Order_Type,
        Party_Name
    )
VALUES (
        'ORD001',
        'STF001',
        '2024-12-25 10:30:00',
        'Sell',
        'Khalid Al-Rashid'
    ),
    (
        'ORD002',
        'STF002',
        '2024-12-26 14:15:00',
        'Sell',
        'Fatima Hassan'
    ),
    (
        'ORD003',
        'STF001',
        '2024-12-27 09:00:00',
        'Purchase',
        'Apple Supplier Inc.'
    ),
    (
        'ORD004',
        'STF003',
        '2024-12-28 16:45:00',
        'Sell',
        'Omar Abdullah'
    ),
    (
        'ORD005',
        'STF002',
        '2024-12-29 11:20:00',
        'Sell',
        'Noura Al-Salem'
    );
-- =============================================
-- 4. Order Details Sample Data
-- =============================================
INSERT INTO Order_Details (Order_ID, Product_ID, Ordered_Qty, Sold_Price)
VALUES -- Order 1: Khalid bought iPhone and AirPods
    ('ORD001', 'PRD001', 1, 4999.00),
    ('ORD001', 'PRD005', 2, 999.00),
    -- Order 2: Fatima bought MacBook
    ('ORD002', 'PRD003', 1, 8999.00),
    -- Order 3: Purchase from supplier
    ('ORD003', 'PRD001', 20, 4500.00),
    ('ORD003', 'PRD005', 30, 850.00),
    -- Order 4: Omar bought Samsung phone and headphones
    ('ORD004', 'PRD002', 1, 3999.00),
    ('ORD004', 'PRD006', 1, 1499.00),
    -- Order 5: Noura bought iPad
    ('ORD005', 'PRD007', 1, 5499.00);