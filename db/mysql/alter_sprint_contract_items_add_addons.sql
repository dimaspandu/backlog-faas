ALTER TABLE `sprint_contract_items`
  ADD COLUMN `addons` JSON DEFAULT NULL AFTER `variant`;