-- Vinpearl Resort Database Schema

-- Drop database if exists (for development only)
DROP DATABASE IF EXISTS vinpearl_resort;

-- Create database
CREATE DATABASE vinpearl_resort;
USE vinpearl_resort;

-- Users table for both customers and administrators
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  is_admin BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL
);

-- Rooms/Accommodations table
CREATE TABLE rooms (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name_en VARCHAR(255) NOT NULL,
  name_vi VARCHAR(255) NOT NULL,
  description_en TEXT NOT NULL,
  description_vi TEXT NOT NULL,
  price_per_night DECIMAL(10,2) NOT NULL,
  capacity INT NOT NULL,
  room_size VARCHAR(50) NOT NULL, -- e.g., "40 m²"
  bed_type VARCHAR(100) NOT NULL, -- e.g., "King" or "Twin"
  amenities TEXT NOT NULL, -- JSON string of amenities
  image_url VARCHAR(255) NOT NULL,
  is_available BOOLEAN DEFAULT true
);

-- Room images (multiple images per room)
CREATE TABLE room_images (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Packages table (vacation packages)
CREATE TABLE packages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name_en VARCHAR(255) NOT NULL,
  name_vi VARCHAR(255) NOT NULL,
  description_en TEXT NOT NULL,
  description_vi TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration INT NOT NULL, -- in days
  includes_text_en TEXT NOT NULL,
  includes_text_vi TEXT NOT NULL,
  image_url VARCHAR(255) NOT NULL
);

-- Tours table
CREATE TABLE tours (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name_en VARCHAR(255) NOT NULL,
  name_vi VARCHAR(255) NOT NULL,
  description_en TEXT NOT NULL,
  description_vi TEXT NOT NULL,
  price_per_person DECIMAL(10,2) NOT NULL,
  duration VARCHAR(50) NOT NULL, -- e.g., "4 hours"
  departure_time VARCHAR(50) NOT NULL,
  meeting_point_en VARCHAR(255) NOT NULL,
  meeting_point_vi VARCHAR(255) NOT NULL,
  includes_text_en TEXT NOT NULL,
  includes_text_vi TEXT NOT NULL,
  image_url VARCHAR(255) NOT NULL
);

-- Bookings table (for rooms)
CREATE TABLE room_bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  guests INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Bookings table (for packages)
CREATE TABLE package_bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  package_id INT NOT NULL,
  start_date DATE NOT NULL,
  guests INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Bookings table (for tours)
CREATE TABLE tour_bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  tour_id INT NOT NULL,
  tour_date DATE NOT NULL,
  guests INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  title_en VARCHAR(255) NOT NULL,
  title_vi VARCHAR(255) NOT NULL,
  content_en TEXT NOT NULL,
  content_vi TEXT NOT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  type ENUM('room', 'package', 'tour') NOT NULL,
  item_id INT NOT NULL, -- references room_id, package_id, or tour_id based on type
  is_approved BOOLEAN DEFAULT false,
  is_hidden BOOLEAN DEFAULT false,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Nearby places table
CREATE TABLE nearby_places (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name_en VARCHAR(255) NOT NULL,
  name_vi VARCHAR(255) NOT NULL,
  description_en TEXT NOT NULL,
  description_vi TEXT NOT NULL,
  category ENUM('restaurant', 'attraction', 'shopping', 'cafe') NOT NULL,
  address VARCHAR(255) NOT NULL,
  distance_km DECIMAL(5,2) NOT NULL,
  contact_phone VARCHAR(20),
  website_url VARCHAR(255),
  booking_url VARCHAR(255),
  opening_hours VARCHAR(255),
  price_level ENUM('$', '$$', '$$$', '$$$$') NOT NULL,
  image_url VARCHAR(255) NOT NULL
);

-- Content table (for multi-language website content)
CREATE TABLE content (
  id INT PRIMARY KEY AUTO_INCREMENT,
  page VARCHAR(50) NOT NULL,
  section VARCHAR(50) NOT NULL,
  key_name VARCHAR(50) NOT NULL,
  content_en TEXT NOT NULL,
  content_vi TEXT NOT NULL,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (page, section, key_name)
);

-- Promotional banners
CREATE TABLE promotional_banners (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title_en VARCHAR(255) NOT NULL,
  title_vi VARCHAR(255) NOT NULL,
  description_en TEXT,
  description_vi TEXT,
  image_url VARCHAR(255) NOT NULL,
  link_url VARCHAR(255),
  is_active BOOLEAN DEFAULT true,
  position INT NOT NULL, -- Order of display
  start_date DATE,
  end_date DATE
); 