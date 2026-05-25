ALTER TABLE `products`
  ADD COLUMN `status` ENUM('ACTIVE','DELETED') NOT NULL DEFAULT 'ACTIVE' AFTER `images`,
  ADD INDEX `idx_products_status` (`status`);
