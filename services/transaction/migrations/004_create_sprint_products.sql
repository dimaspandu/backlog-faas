CREATE TABLE sprint_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sprint_id VARCHAR(50),
  product_id INT,
  price INT,
  discount DECIMAL(5,2) DEFAULT 0,
  is_available BOOLEAN DEFAULT TRUE,

  FOREIGN KEY (sprint_id) REFERENCES sprints(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);