-- DB-01: enforce the existing Products and Inventory data rules.
-- Apply after the current schema has been checked for compatible data.

ALTER TABLE Products
    MODIFY COLUMN Status VARCHAR(20) NOT NULL DEFAULT 'Normal',
    MODIFY COLUMN Threshold INT NOT NULL DEFAULT 20,
    ADD CONSTRAINT chk_products_quantity_nonnegative CHECK (Quantity >= 0),
    ADD CONSTRAINT chk_products_price_positive CHECK (Price > 0),
    ADD CONSTRAINT chk_products_status CHECK (Status IN ('Normal', 'Low Stock', 'Out of Stock')),
    ADD CONSTRAINT chk_products_threshold_nonnegative CHECK (Threshold >= 0);

ALTER TABLE Order_Details
    DROP FOREIGN KEY order_details_ibfk_2,
    ADD CONSTRAINT fk_order_details_product
        FOREIGN KEY (Product_ID) REFERENCES Products(Product_ID) ON DELETE RESTRICT;
