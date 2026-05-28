ALTER TABLE `sprint_products`
  ADD COLUMN `quota_total` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `stock_sold`,
  ADD COLUMN `quota_used` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `quota_total`;