-- Mock data for Vinpearl Resort database

-- Use the vinpearl_resort database
USE vinpearl_resort;

INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('admin@vinpearl.com', '0192023a7bbd73250516f069df18b500', 'Admin User', 
                 '+84 123 456 789', 1);
INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('john.doe@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'John Doe', 
                 '+1 555-123-4567', 0);
INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('jane.smith@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'Jane Smith', 
                 '+1 555-765-4321', 0);
INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('nguyen.van@example.vn', '482c811da5d5b4bc6d497ffa98491e38', 'Nguyen Van A', 
                 '+84 912 345 678', 0);
INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('tran.thi@example.vn', '482c811da5d5b4bc6d497ffa98491e38', 'Tran Thi B', 
                 '+84 987 654 321', 0);
INSERT INTO rooms (name_en, name_vi, description_en, description_vi, price_per_night, capacity, 
                room_size, bed_type, amenities, image_url) 
                VALUES ('Deluxe Ocean View', 'Phòng Deluxe Hướng Biển', 'Spacious room with breathtaking ocean views, modern amenities, and a private balcony.', 
                'Phòng rộng rãi với tầm nhìn tuyệt đẹp ra biển, tiện nghi hiện đại và ban công riêng.', 150.0, 2, 
                '40 m²', 'King', '["Air conditioning", "Flat-screen TV", "Free WiFi", "Minibar", "Safe"]', 'assets/images/rooms/deluxe-ocean.jpg');
INSERT INTO rooms (name_en, name_vi, description_en, description_vi, price_per_night, capacity, 
                room_size, bed_type, amenities, image_url) 
                VALUES ('Premium Garden Suite', 'Phòng Suite Hướng Vườn', 'Elegant suite with garden views, separate living area, and exclusive amenities.', 
                'Phòng suite sang trọng với tầm nhìn ra vườn, khu vực sinh hoạt riêng biệt và tiện nghi độc quyền.', 250.0, 2, 
                '60 m²', 'King', '["Air conditioning", "Flat-screen TV", "Free WiFi", "Minibar", "Safe", "Bathtub", "Coffee machine"]', 'assets/images/rooms/premium-garden.jpg');
INSERT INTO rooms (name_en, name_vi, description_en, description_vi, price_per_night, capacity, 
                room_size, bed_type, amenities, image_url) 
                VALUES ('Family Beach Villa', 'Biệt Thự Bãi Biển Gia Đình', 'Spacious villa perfect for families, with direct beach access and a private pool.', 
                'Biệt thự rộng rãi hoàn hảo cho gia đình, với lối đi trực tiếp ra bãi biển và hồ bơi riêng.', 450.0, 4, 
                '120 m²', '2 Kings', '["Air conditioning", "Flat-screen TV", "Free WiFi", "Minibar", "Safe", "Private pool", "Kitchen", "Dining area", "Beach access"]', 'assets/images/rooms/family-villa.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (1, 'assets/images/rooms/deluxe-ocean-1.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (1, 'assets/images/rooms/deluxe-ocean-2.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (1, 'assets/images/rooms/deluxe-ocean-3.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (2, 'assets/images/rooms/premium-garden-1.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (2, 'assets/images/rooms/premium-garden-2.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (2, 'assets/images/rooms/premium-garden-3.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (3, 'assets/images/rooms/family-villa-1.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (3, 'assets/images/rooms/family-villa-2.jpg');
INSERT INTO room_images (room_id, image_url) VALUES (3, 'assets/images/rooms/family-villa-3.jpg');
INSERT INTO packages (name_en, name_vi, description_en, description_vi, price, duration, 
                 includes_text_en, includes_text_vi, image_url) 
                 VALUES ('Romantic Getaway', 'Kỳ Nghỉ Lãng Mạn', 'Perfect package for couples looking for a romantic escape.', 
                 'Gói hoàn hảo cho các cặp đôi đang tìm kiếm một kỳ nghỉ lãng mạn.', 500.0, 3, 
                 'Deluxe Ocean View Room, Daily breakfast, Couple\'s massage, Romantic dinner on beach', 'Phòng Deluxe Hướng Biển, Bữa sáng hàng ngày, Massage cho cặp đôi, Bữa tối lãng mạn trên bãi biển', 'assets/images/packages/romantic.jpg');
INSERT INTO packages (name_en, name_vi, description_en, description_vi, price, duration, 
                 includes_text_en, includes_text_vi, image_url) 
                 VALUES ('Family Adventure', 'Phiêu Lưu Gia Đình', 'Fun-filled package for the whole family with activities for all ages.', 
                 'Gói đầy niềm vui cho cả gia đình với các hoạt động cho mọi lứa tuổi.', 800.0, 5, 
                 'Family Villa, Daily breakfast, Water park access, Dolphin show, Island tour', 'Biệt Thự Gia Đình, Bữa sáng hàng ngày, Vào công viên nước, Xem show cá heo, Tour tham quan đảo', 'assets/images/packages/family.jpg');
INSERT INTO tours (name_en, name_vi, description_en, description_vi, price_per_person, duration, 
                departure_time, meeting_point_en, meeting_point_vi, includes_text_en, includes_text_vi, image_url) 
                VALUES ('Island Hopping Tour', 'Tour Khám Phá Đảo', 'Discover the beautiful islands surrounding Nha Trang.', 
                'Khám phá những hòn đảo xinh đẹp xung quanh Nha Trang.', 45.0, '6 hours', 
                '09:00 AM', 'Vinpearl Resort Lobby', 'Sảnh Vinpearl Resort', 
                'Boat transportation, Lunch, Snorkeling gear, Tour guide', 'Phương tiện đi lại bằng thuyền, Bữa trưa, Dụng cụ lặn, Hướng dẫn viên', 'assets/images/tours/island-hopping.jpg');
INSERT INTO tours (name_en, name_vi, description_en, description_vi, price_per_person, duration, 
                departure_time, meeting_point_en, meeting_point_vi, includes_text_en, includes_text_vi, image_url) 
                VALUES ('City Cultural Tour', 'Tour Văn Hóa Thành Phố', 'Explore the rich cultural heritage of Nha Trang city.', 
                'Khám phá di sản văn hóa phong phú của thành phố Nha Trang.', 35.0, '4 hours', 
                '02:00 PM', 'Vinpearl Resort Lobby', 'Sảnh Vinpearl Resort', 
                'Transportation, Temple entrance fees, Tour guide, Afternoon tea', 'Phương tiện đi lại, Phí vào cổng đền, Hướng dẫn viên, Trà chiều', 'assets/images/tours/city-cultural.jpg');
INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                 VALUES (2, 1, '2023-06-10', '2023-06-15', 
                 2, 750.0, 'completed');
INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                 VALUES (3, 2, '2023-07-20', '2023-07-25', 
                 2, 1250.0, 'completed');
INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                 VALUES (4, 3, '2025-05-08', '2025-05-15', 
                 4, 3150.0, 'confirmed');
INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                 VALUES (5, 1, '2025-06-07', '2025-06-11', 
                 2, 600.0, 'pending');
INSERT INTO package_bookings (user_id, package_id, start_date, guests, total_price, status) 
                 VALUES (2, 1, '2023-08-15', 
                 2, 500.0, 'completed');
INSERT INTO package_bookings (user_id, package_id, start_date, guests, total_price, status) 
                 VALUES (4, 2, '2025-06-22', 
                 4, 800.0, 'pending');
INSERT INTO tour_bookings (user_id, tour_id, tour_date, guests, total_price, status) 
                 VALUES (3, 1, '2023-07-22', 
                 2, 90.0, 'completed');
INSERT INTO tour_bookings (user_id, tour_id, tour_date, guests, total_price, status) 
                 VALUES (5, 2, '2025-05-11', 
                 1, 35.0, 'confirmed');
INSERT INTO reviews (user_id, title_en, title_vi, content_en, content_vi, rating, type, item_id, is_approved, is_hidden) 
                 VALUES (2, 'Amazing ocean view!', 'Tầm nhìn ra biển tuyệt vời!', 'The Deluxe Ocean View room exceeded our expectations. The view was breathtaking and the service was excellent.', 
                 'Phòng Deluxe Hướng Biển vượt quá mong đợi của chúng tôi. Tầm nhìn thật ngoạn mục và dịch vụ rất xuất sắc.', 5, 'room', 1, 
                 1, 0);
INSERT INTO reviews (user_id, title_en, title_vi, content_en, content_vi, rating, type, item_id, is_approved, is_hidden) 
                 VALUES (3, 'Lovely garden suite', 'Phòng suite vườn đáng yêu', 'We loved our stay in the Premium Garden Suite. The room was spacious and comfortable.', 
                 'Chúng tôi rất thích kỳ nghỉ của mình trong Phòng Suite Hướng Vườn. Phòng rộng rãi và thoải mái.', 4, 'room', 2, 
                 1, 0);
INSERT INTO reviews (user_id, title_en, title_vi, content_en, content_vi, rating, type, item_id, is_approved, is_hidden) 
                 VALUES (4, 'Perfect for families', 'Hoàn hảo cho gia đình', 'The Family Package was perfect for our needs. The kids loved the water park!', 
                 'Gói Gia Đình thật hoàn hảo cho nhu cầu của chúng tôi. Bọn trẻ rất thích công viên nước!', 5, 'package', 2, 
                 0, 0);
INSERT INTO reviews (user_id, title_en, title_vi, content_en, content_vi, rating, type, item_id, is_approved, is_hidden) 
                 VALUES (5, 'Beautiful islands', 'Những hòn đảo xinh đẹp', 'The Island Hopping Tour was the highlight of our trip. The guide was knowledgeable and friendly.', 
                 'Tour Khám Phá Đảo là điểm nhấn của chuyến đi. Hướng dẫn viên có kiến thức và thân thiện.', 4, 'tour', 1, 
                 1, 0);
INSERT INTO nearby_places (name_en, name_vi, description_en, description_vi, category, address, 
                 distance_km, contact_phone, website_url, booking_url, opening_hours, price_level, image_url) 
                 VALUES ('Long Son Pagoda', 'Chùa Long Sơn', 'Historic Buddhist temple with a large white Buddha statue overlooking the city.', 
                 'Ngôi chùa Phật giáo lịch sử với tượng Phật trắng lớn nhìn xuống thành phố.', 'attraction', '22 October 23 Street, Nha Trang, Vietnam', 4.5, 
                 '+84 258 3522 525', 'https://example.com/longson', '', 
                 '7:00 AM - 6:00 PM', '$', 'assets/images/nearby/long-son-pagoda.jpg');
INSERT INTO nearby_places (name_en, name_vi, description_en, description_vi, category, address, 
                 distance_km, contact_phone, website_url, booking_url, opening_hours, price_level, image_url) 
                 VALUES ('Sailing Club Restaurant', 'Nhà hàng Sailing Club', 'Beachfront restaurant serving international cuisine with stunning ocean views.', 
                 'Nhà hàng bên bãi biển phục vụ ẩm thực quốc tế với tầm nhìn ra biển tuyệt đẹp.', 'restaurant', '72-74 Tran Phu Street, Nha Trang, Vietnam', 2.8, 
                 '+84 258 3524 628', 'https://example.com/sailingclub', 'https://example.com/sailingclub/reservations', 
                 '11:00 AM - 12:00 AM', '$$$', 'assets/images/nearby/sailing-club.jpg');
INSERT INTO nearby_places (name_en, name_vi, description_en, description_vi, category, address, 
                 distance_km, contact_phone, website_url, booking_url, opening_hours, price_level, image_url) 
                 VALUES ('Nha Trang Night Market', 'Chợ Đêm Nha Trang', 'Vibrant night market selling local goods, crafts, and street food.', 
                 'Chợ đêm sôi động bán hàng hóa địa phương, đồ thủ công và đồ ăn đường phố.', 'shopping', 'Tran Phu Street, Nha Trang, Vietnam', 3.2, 
                 '', '', '', 
                 '6:00 PM - 12:00 AM', '$', 'assets/images/nearby/night-market.jpg');
INSERT INTO nearby_places (name_en, name_vi, description_en, description_vi, category, address, 
                 distance_km, contact_phone, website_url, booking_url, opening_hours, price_level, image_url) 
                 VALUES ('Rainforest Cafe', 'Quán Cà Phê Rừng Mưa', 'Cozy cafe with a tropical rainforest theme and excellent Vietnamese coffee.', 
                 'Quán cà phê ấm cúng với chủ đề rừng mưa nhiệt đới và cà phê Việt Nam tuyệt vời.', 'cafe', '56 Nguyen Thien Thuat Street, Nha Trang, Vietnam', 3.8, 
                 '+84 258 3526 789', 'https://example.com/rainforestcafe', '', 
                 '7:00 AM - 10:00 PM', '$$', 'assets/images/nearby/rainforest-cafe.jpg');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('home', 'hero', 'title', 
                 'Welcome to Vinpearl Resort Nha Trang', 'Chào mừng đến với Vinpearl Resort Nha Trang');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('home', 'hero', 'subtitle', 
                 'Experience luxury by the beautiful Nha Trang beach', 'Trải nghiệm sang trọng bên bãi biển Nha Trang xinh đẹp');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('home', 'about', 'title', 
                 'About Our Resort', 'Về Khu Nghỉ Dưỡng Của Chúng Tôi');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('home', 'about', 'content', 
                 'Vinpearl Resort Nha Trang offers the perfect blend of luxury and natural beauty. Located on a private beach, our resort features spacious rooms with stunning views, world-class dining options, and a range of activities for all ages.', 'Vinpearl Resort Nha Trang mang đến sự kết hợp hoàn hảo giữa sang trọng và vẻ đẹp tự nhiên. Tọa lạc trên một bãi biển riêng, khu nghỉ dưỡng của chúng tôi có những phòng rộng rãi với tầm nhìn tuyệt đẹp, các lựa chọn ẩm thực đẳng cấp thế giới và nhiều hoạt động cho mọi lứa tuổi.');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('rooms', 'header', 'title', 
                 'Our Accommodations', 'Phòng Nghỉ Của Chúng Tôi');
INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('rooms', 'header', 'subtitle', 
                 'Discover our range of luxurious rooms and suites', 'Khám phá các loại phòng và suite sang trọng của chúng tôi');
INSERT INTO promotional_banners (title_en, title_vi, description_en, description_vi, image_url, 
                 link_url, is_active, position, start_date, end_date) 
                 VALUES ('Summer Special', 'Ưu Đãi Mùa Hè', 'Enjoy 20% off on all room bookings during summer months', 
                 'Giảm 20% cho tất cả các đặt phòng trong những tháng hè', 'assets/images/banners/summer-special.jpg', 'promotions/summer-special', 
                 1, 1, '2023-06-01', '2023-08-31');
INSERT INTO promotional_banners (title_en, title_vi, description_en, description_vi, image_url, 
                 link_url, is_active, position, start_date, end_date) 
                 VALUES ('Honeymoon Package', 'Gói Trăng Mật', 'Special honeymoon package with romantic dinner and spa treatments', 
                 'Gói trăng mật đặc biệt với bữa tối lãng mạn và các liệu pháp spa', 'assets/images/banners/honeymoon.jpg', 'packages/honeymoon', 
                 1, 2, '2023-01-01', '2023-12-31');
