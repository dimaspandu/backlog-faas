ALTER TABLE `product_variants`
  MODIFY COLUMN `sku` VARCHAR(128) NOT NULL;

-- Note: This change makes SKU required for all product variants.
-- Existing rows with NULL sku (if any) must be updated before running this migration.
