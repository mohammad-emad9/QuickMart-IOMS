-- DB-02: enforce Order Management integrity and preserve historical rows.
-- This migration is DDL-only; it does not backfill or alter existing data.
-- Apply after 001_products_inventory_foundation.sql and after validating that
-- existing Orders.Order_Type values are Sell/Purchase and detail quantities and
-- prices are positive.

-- UP
ALTER TABLE Orders
    DROP FOREIGN KEY orders_ibfk_1;

ALTER TABLE Orders
    ADD CONSTRAINT orders_ibfk_1
        FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE RESTRICT;

ALTER TABLE Orders
    ADD CONSTRAINT chk_orders_order_type
        CHECK (Order_Type IN ('Sell', 'Purchase'));

ALTER TABLE Order_Details
    DROP FOREIGN KEY order_details_ibfk_1;

ALTER TABLE Order_Details
    ADD CONSTRAINT order_details_ibfk_1
        FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE RESTRICT;

ALTER TABLE Order_Details
    ADD CONSTRAINT chk_order_details_ordered_qty_positive
        CHECK (Ordered_Qty > 0),
    ADD CONSTRAINT chk_order_details_sold_price_positive
        CHECK (Sold_Price > 0);

-- DOWN PLAN (execute manually, in this order, only if rollback is required)
-- ALTER TABLE Order_Details
--     DROP CONSTRAINT chk_order_details_ordered_qty_positive,
--     DROP CONSTRAINT chk_order_details_sold_price_positive;
-- ALTER TABLE Order_Details
--     DROP FOREIGN KEY order_details_ibfk_1;
-- ALTER TABLE Order_Details
--     ADD CONSTRAINT order_details_ibfk_1
--         FOREIGN KEY (Order_ID) REFERENCES Orders(Order_ID) ON DELETE CASCADE;
--
-- ALTER TABLE Orders
--     DROP CONSTRAINT chk_orders_order_type;
-- ALTER TABLE Orders
--     DROP FOREIGN KEY orders_ibfk_1;
-- ALTER TABLE Orders
--     ADD CONSTRAINT orders_ibfk_1
--         FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE CASCADE;
--
-- The DOWN plan restores the pre-DB-02 semantics and therefore re-enables
-- destructive cascades and permits invalid order/detail values. It must not be
-- used when preserving order history is required.
