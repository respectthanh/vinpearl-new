-- Tour Bookings Table
CREATE TABLE IF NOT EXISTS tour_bookings (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  tour_id int(11) NOT NULL,
  tour_date date NOT NULL,
  num_people int(11) NOT NULL,
  special_requests text,
  total_price decimal(10,2) NOT NULL,
  status enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY tour_id (tour_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
