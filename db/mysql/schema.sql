CREATE TABLE inventory (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  item_code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  category ENUM(
    'BEAN',
    'POWDER',
    'MILK',
    'SWEETENER',
    'SYRUP',
    'PACKAGING',
    'ICE',
    'OTHER'
  ) NOT NULL,
  stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
  unit ENUM(
    'gr',
    'kg',
    'ml',
    'ltr',
    'pcs'
  ) NOT NULL,
  status ENUM(
    'ACTIVE',
    'DELETED'
  ) NOT NULL DEFAULT 'ACTIVE',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_inventory_code (item_code),
  INDEX idx_inventory_category (category),
  INDEX idx_inventory_status (status)
);

CREATE TABLE products (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(50) NOT NULL UNIQUE,
  packaging_item_code VARCHAR(50) NOT NULL,
  product_slug VARCHAR(100) NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  category ENUM(
    'COFFEE',
    'NON_COFFEE',
    'OTHER'
  ) NOT NULL,
  image_urls VARCHAR(500) NULL,
  selling_price_cents BIGINT UNSIGNED NOT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM(
    'ACTIVE',
    'DELETED'
  ) NOT NULL DEFAULT 'ACTIVE',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_slug (product_slug),
  INDEX idx_products_category (category),
  INDEX idx_products_status (status)
);

CREATE TABLE product_recipes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  inventory_id BIGINT UNSIGNED NOT NULL,
  qty_required DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (inventory_id) REFERENCES inventory(id)
);

CREATE TABLE sprints (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  start_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  end_at DATETIME NULL,
  max_contracts INT UNSIGNED NULL,
  current_contracts INT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) DEFAULT 1,
  is_open TINYINT(1) DEFAULT 0,
  status ENUM('DRAFT','ACTIVE','PAUSED','CLOSED','DELETED') DEFAULT 'DRAFT',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE sprint_product_offerings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sprint_token VARCHAR(32) NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  offer_price_cents BIGINT UNSIGNED NOT NULL,
  stock_limit INT UNSIGNED NOT NULL,
  stock_remaining INT UNSIGNED NOT NULL,
  is_available BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sprint_token) REFERENCES sprints(token),
  FOREIGN KEY (product_id) REFERENCES products(id),
  UNIQUE KEY uk_sprint_product (sprint_token, product_id)
);

CREATE TABLE customers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL,
  phone VARCHAR(50) NULL,
  auth_provider ENUM('GUEST','GOOGLE','EMAIL') DEFAULT 'GUEST',
  external_id VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE sprint_contracts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contract_number VARCHAR(32) NOT NULL UNIQUE,
  sprint_token VARCHAR(32) NOT NULL,
  customer_name VARCHAR(255) NOT NULL,
  customer_contact VARCHAR(255) NOT NULL,
  notes TEXT NULL,
  total_price_cents BIGINT UNSIGNED NOT NULL,
  request_status ENUM(
    'PENDING',
    'APPROVED',
    'REJECTED',
    'FULFILLED',
    'CANCELLED'
  ) NOT NULL DEFAULT 'PENDING',
  payment_status ENUM(
    'UNPAID',
    'PAID',
    'REFUNDED'
  ) NOT NULL DEFAULT 'UNPAID',
  approved_by BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (sprint_token) REFERENCES sprints(token)
);

CREATE TABLE sprint_contract_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sprint_contract_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  sugar_level ENUM('NONE','LESS','MODERATE','NORMAL') DEFAULT 'NORMAL',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (sprint_contract_id) REFERENCES sprint_contracts(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM(
    'administrator',
    'backoffice'
  ) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_sessions (
  token CHAR(64) PRIMARY KEY,
  admin_id BIGINT UNSIGNED NOT NULL,
  username VARCHAR(100) NOT NULL,
  role ENUM(
    'administrator',
    'backoffice'
  ) NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  expires_at DATETIME NOT NULL,
  last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
  INDEX idx_admin_id (admin_id),
  INDEX idx_expires_at (expires_at)
);