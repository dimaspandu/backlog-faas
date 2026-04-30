CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  name_snapshot VARCHAR(100),
  price_snapshot INT,
  qty INT,

  bundle_id VARCHAR(50),
  bundle_name VARCHAR(50),

  FOREIGN KEY (order_id) REFERENCES orders(id)
);