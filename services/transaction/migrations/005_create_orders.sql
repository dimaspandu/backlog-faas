CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(50),
  sprint_id VARCHAR(50),
  customer_id INT,
  total INT,
  notes TEXT,
  status VARCHAR(20),
  payment_status VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (sprint_id) REFERENCES sprints(id),
  FOREIGN KEY (customer_id) REFERENCES customers(id)
);