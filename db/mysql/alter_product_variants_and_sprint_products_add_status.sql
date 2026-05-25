ALTER TABLE `product_variants`
  ADD COLUMN `status` ENUM('ACTIVE','DELETED') NOT NULL DEFAULT 'ACTIVE' AFTER `attributes`,
  ADD INDEX `idx_product_variants_status` (`status`);

ALTER TABLE `sprint_products`
  ADD COLUMN `status` ENUM('ACTIVE','INACTIVE','DELETED') NOT NULL DEFAULT 'ACTIVE' AFTER `variant`,
  ADD INDEX `idx_sprint_products_status` (`status`);
