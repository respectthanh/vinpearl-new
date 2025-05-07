#!/usr/bin/env python3
import random
import json
import hashlib
from datetime import datetime, timedelta

# Mock data generation script for Vinpearl Resort database

def generate_mock_data():
    """Generate SQL statements to insert mock data into the database."""
    sql_statements = []
    
    # Add users data
    sql_statements.extend(generate_users())
    
    # Add rooms data
    rooms_data = generate_rooms()
    sql_statements.extend(rooms_data[0])  # Room statements
    sql_statements.extend(rooms_data[1])  # Room images statements
    
    # Add packages data
    sql_statements.extend(generate_packages())
    
    # Add tours data
    sql_statements.extend(generate_tours())
    
    # Add bookings data
    sql_statements.extend(generate_bookings())
    
    # Add reviews data
    sql_statements.extend(generate_reviews())
    
    # Add nearby places data
    sql_statements.extend(generate_nearby_places())
    
    # Add content data
    sql_statements.extend(generate_content())
    
    # Add promotional banners data
    sql_statements.extend(generate_promotional_banners())
    
    return sql_statements

def generate_users():
    """Generate mock users data."""
    users = [
        # Admins
        {
            'email': 'admin@vinpearl.com',
            'password_hash': hashlib.md5('admin123'.encode()).hexdigest(),  # Not for production!
            'full_name': 'Admin User',
            'phone': '+84 123 456 789',
            'is_admin': True
        },
        # Regular users
        {
            'email': 'john.doe@example.com',
            'password_hash': hashlib.md5('password123'.encode()).hexdigest(),
            'full_name': 'John Doe',
            'phone': '+1 555-123-4567',
            'is_admin': False
        },
        {
            'email': 'jane.smith@example.com',
            'password_hash': hashlib.md5('password123'.encode()).hexdigest(),
            'full_name': 'Jane Smith',
            'phone': '+1 555-765-4321',
            'is_admin': False
        },
        {
            'email': 'nguyen.van@example.vn',
            'password_hash': hashlib.md5('password123'.encode()).hexdigest(),
            'full_name': 'Nguyen Van A',
            'phone': '+84 912 345 678',
            'is_admin': False
        },
        {
            'email': 'tran.thi@example.vn',
            'password_hash': hashlib.md5('password123'.encode()).hexdigest(),
            'full_name': 'Tran Thi B',
            'phone': '+84 987 654 321',
            'is_admin': False
        }
    ]
    
    statements = []
    for user in users:
        stmt = f"""INSERT INTO users (email, password_hash, full_name, phone, is_admin) 
                 VALUES ('{user['email']}', '{user['password_hash']}', '{user['full_name']}', 
                 '{user['phone']}', {1 if user['is_admin'] else 0});"""
        statements.append(stmt)
    
    return statements

def generate_rooms():
    """Generate mock rooms data and room images."""
    rooms = [
        {
            'name_en': 'Deluxe Ocean View',
            'name_vi': 'Phòng Deluxe Hướng Biển',
            'description_en': 'Spacious room with breathtaking ocean views, modern amenities, and a private balcony.',
            'description_vi': 'Phòng rộng rãi với tầm nhìn tuyệt đẹp ra biển, tiện nghi hiện đại và ban công riêng.',
            'price_per_night': 150.00,
            'capacity': 2,
            'room_size': '40 m²',
            'bed_type': 'King',
            'amenities': json.dumps(['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe']),
            'image_url': 'assets/images/rooms/deluxe-ocean.jpg',
            'images': [
                'assets/images/rooms/deluxe-ocean-1.jpg',
                'assets/images/rooms/deluxe-ocean-2.jpg',
                'assets/images/rooms/deluxe-ocean-3.jpg'
            ]
        },
        {
            'name_en': 'Premium Garden Suite',
            'name_vi': 'Phòng Suite Hướng Vườn',
            'description_en': 'Elegant suite with garden views, separate living area, and exclusive amenities.',
            'description_vi': 'Phòng suite sang trọng với tầm nhìn ra vườn, khu vực sinh hoạt riêng biệt và tiện nghi độc quyền.',
            'price_per_night': 250.00,
            'capacity': 2,
            'room_size': '60 m²',
            'bed_type': 'King',
            'amenities': json.dumps(['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe', 'Bathtub', 'Coffee machine']),
            'image_url': 'assets/images/rooms/premium-garden.jpg',
            'images': [
                'assets/images/rooms/premium-garden-1.jpg',
                'assets/images/rooms/premium-garden-2.jpg',
                'assets/images/rooms/premium-garden-3.jpg'
            ]
        },
        {
            'name_en': 'Family Beach Villa',
            'name_vi': 'Biệt Thự Bãi Biển Gia Đình',
            'description_en': 'Spacious villa perfect for families, with direct beach access and a private pool.',
            'description_vi': 'Biệt thự rộng rãi hoàn hảo cho gia đình, với lối đi trực tiếp ra bãi biển và hồ bơi riêng.',
            'price_per_night': 450.00,
            'capacity': 4,
            'room_size': '120 m²',
            'bed_type': '2 Kings',
            'amenities': json.dumps(['Air conditioning', 'Flat-screen TV', 'Free WiFi', 'Minibar', 'Safe', 'Private pool', 'Kitchen', 'Dining area', 'Beach access']),
            'image_url': 'assets/images/rooms/family-villa.jpg',
            'images': [
                'assets/images/rooms/family-villa-1.jpg',
                'assets/images/rooms/family-villa-2.jpg',
                'assets/images/rooms/family-villa-3.jpg'
            ]
        }
    ]
    
    room_statements = []
    room_image_statements = []
    
    for i, room in enumerate(rooms, 1):
        stmt = f"""INSERT INTO rooms (name_en, name_vi, description_en, description_vi, price_per_night, capacity, 
                room_size, bed_type, amenities, image_url) 
                VALUES ('{room['name_en']}', '{room['name_vi']}', '{room['description_en']}', 
                '{room['description_vi']}', {room['price_per_night']}, {room['capacity']}, 
                '{room['room_size']}', '{room['bed_type']}', '{room['amenities']}', '{room['image_url']}');"""
        room_statements.append(stmt)
        
        for img in room['images']:
            img_stmt = f"INSERT INTO room_images (room_id, image_url) VALUES ({i}, '{img}');"
            room_image_statements.append(img_stmt)
    
    return [room_statements, room_image_statements]

def generate_packages():
    """Generate mock vacation packages data."""
    packages = [
        {
            'name_en': 'Romantic Getaway',
            'name_vi': 'Kỳ Nghỉ Lãng Mạn',
            'description_en': 'Perfect package for couples looking for a romantic escape.',
            'description_vi': 'Gói hoàn hảo cho các cặp đôi đang tìm kiếm một kỳ nghỉ lãng mạn.',
            'price': 500.00,
            'duration': 3,
            'includes_text_en': 'Deluxe Ocean View Room, Daily breakfast, Couple\'s massage, Romantic dinner on beach',
            'includes_text_vi': 'Phòng Deluxe Hướng Biển, Bữa sáng hàng ngày, Massage cho cặp đôi, Bữa tối lãng mạn trên bãi biển',
            'image_url': 'assets/images/packages/romantic.jpg'
        },
        {
            'name_en': 'Family Adventure',
            'name_vi': 'Phiêu Lưu Gia Đình',
            'description_en': 'Fun-filled package for the whole family with activities for all ages.',
            'description_vi': 'Gói đầy niềm vui cho cả gia đình với các hoạt động cho mọi lứa tuổi.',
            'price': 800.00,
            'duration': 5,
            'includes_text_en': 'Family Villa, Daily breakfast, Water park access, Dolphin show, Island tour',
            'includes_text_vi': 'Biệt Thự Gia Đình, Bữa sáng hàng ngày, Vào công viên nước, Xem show cá heo, Tour tham quan đảo',
            'image_url': 'assets/images/packages/family.jpg'
        }
    ]
    
    statements = []
    for package in packages:
        stmt = f"""INSERT INTO packages (name_en, name_vi, description_en, description_vi, price, duration, 
                 includes_text_en, includes_text_vi, image_url) 
                 VALUES ('{package['name_en']}', '{package['name_vi']}', '{package['description_en']}', 
                 '{package['description_vi']}', {package['price']}, {package['duration']}, 
                 '{package['includes_text_en']}', '{package['includes_text_vi']}', '{package['image_url']}');"""
        statements.append(stmt)
    
    return statements

def generate_tours():
    """Generate mock tours data."""
    tours = [
        {
            'name_en': 'Island Hopping Tour',
            'name_vi': 'Tour Khám Phá Đảo',
            'description_en': 'Discover the beautiful islands surrounding Nha Trang.',
            'description_vi': 'Khám phá những hòn đảo xinh đẹp xung quanh Nha Trang.',
            'price_per_person': 45.00,
            'duration': '6 hours',
            'departure_time': '09:00 AM',
            'meeting_point_en': 'Vinpearl Resort Lobby',
            'meeting_point_vi': 'Sảnh Vinpearl Resort',
            'includes_text_en': 'Boat transportation, Lunch, Snorkeling gear, Tour guide',
            'includes_text_vi': 'Phương tiện đi lại bằng thuyền, Bữa trưa, Dụng cụ lặn, Hướng dẫn viên',
            'image_url': 'assets/images/tours/island-hopping.jpg'
        },
        {
            'name_en': 'City Cultural Tour',
            'name_vi': 'Tour Văn Hóa Thành Phố',
            'description_en': 'Explore the rich cultural heritage of Nha Trang city.',
            'description_vi': 'Khám phá di sản văn hóa phong phú của thành phố Nha Trang.',
            'price_per_person': 35.00,
            'duration': '4 hours',
            'departure_time': '02:00 PM',
            'meeting_point_en': 'Vinpearl Resort Lobby',
            'meeting_point_vi': 'Sảnh Vinpearl Resort',
            'includes_text_en': 'Transportation, Temple entrance fees, Tour guide, Afternoon tea',
            'includes_text_vi': 'Phương tiện đi lại, Phí vào cổng đền, Hướng dẫn viên, Trà chiều',
            'image_url': 'assets/images/tours/city-cultural.jpg'
        }
    ]
    
    statements = []
    for tour in tours:
        stmt = f"""INSERT INTO tours (name_en, name_vi, description_en, description_vi, price_per_person, duration, 
                departure_time, meeting_point_en, meeting_point_vi, includes_text_en, includes_text_vi, image_url) 
                VALUES ('{tour['name_en']}', '{tour['name_vi']}', '{tour['description_en']}', 
                '{tour['description_vi']}', {tour['price_per_person']}, '{tour['duration']}', 
                '{tour['departure_time']}', '{tour['meeting_point_en']}', '{tour['meeting_point_vi']}', 
                '{tour['includes_text_en']}', '{tour['includes_text_vi']}', '{tour['image_url']}');"""
        statements.append(stmt)
    
    return statements

def generate_bookings():
    """Generate mock bookings data for rooms, packages, and tours."""
    statements = []
    
    # Room bookings
    room_bookings = [
        {
            'user_id': 2,  # John Doe
            'room_id': 1,  # Deluxe Ocean View
            'check_in_date': '2023-06-10',
            'check_out_date': '2023-06-15',
            'guests': 2,
            'total_price': 750.00,  # 5 nights * 150
            'status': 'completed'
        },
        {
            'user_id': 3,  # Jane Smith
            'room_id': 2,  # Premium Garden Suite
            'check_in_date': '2023-07-20',
            'check_out_date': '2023-07-25',
            'guests': 2,
            'total_price': 1250.00,  # 5 nights * 250
            'status': 'completed'
        },
        {
            'user_id': 4,  # Nguyen Van A
            'room_id': 3,  # Family Beach Villa
            'check_in_date': datetime.now().strftime('%Y-%m-%d'),
            'check_out_date': (datetime.now() + timedelta(days=7)).strftime('%Y-%m-%d'),
            'guests': 4,
            'total_price': 3150.00,  # 7 nights * 450
            'status': 'confirmed'
        },
        {
            'user_id': 5,  # Tran Thi B
            'room_id': 1,  # Deluxe Ocean View
            'check_in_date': (datetime.now() + timedelta(days=30)).strftime('%Y-%m-%d'),
            'check_out_date': (datetime.now() + timedelta(days=34)).strftime('%Y-%m-%d'),
            'guests': 2,
            'total_price': 600.00,  # 4 nights * 150
            'status': 'pending'
        }
    ]
    
    for booking in room_bookings:
        stmt = f"""INSERT INTO room_bookings (user_id, room_id, check_in_date, check_out_date, guests, total_price, status) 
                 VALUES ({booking['user_id']}, {booking['room_id']}, '{booking['check_in_date']}', '{booking['check_out_date']}', 
                 {booking['guests']}, {booking['total_price']}, '{booking['status']}');"""
        statements.append(stmt)
    
    # Package bookings
    package_bookings = [
        {
            'user_id': 2,  # John Doe
            'package_id': 1,  # Romantic Getaway
            'start_date': '2023-08-15',
            'guests': 2,
            'total_price': 500.00,
            'status': 'completed'
        },
        {
            'user_id': 4,  # Nguyen Van A
            'package_id': 2,  # Family Adventure
            'start_date': (datetime.now() + timedelta(days=45)).strftime('%Y-%m-%d'),
            'guests': 4,
            'total_price': 800.00,
            'status': 'pending'
        }
    ]
    
    for booking in package_bookings:
        stmt = f"""INSERT INTO package_bookings (user_id, package_id, start_date, guests, total_price, status) 
                 VALUES ({booking['user_id']}, {booking['package_id']}, '{booking['start_date']}', 
                 {booking['guests']}, {booking['total_price']}, '{booking['status']}');"""
        statements.append(stmt)
    
    # Tour bookings
    tour_bookings = [
        {
            'user_id': 3,  # Jane Smith
            'tour_id': 1,  # Island Hopping Tour
            'tour_date': '2023-07-22',
            'guests': 2,
            'total_price': 90.00,  # 2 people * 45
            'status': 'completed'
        },
        {
            'user_id': 5,  # Tran Thi B
            'tour_id': 2,  # City Cultural Tour
            'tour_date': (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d'),
            'guests': 1,
            'total_price': 35.00,  # 1 person * 35
            'status': 'confirmed'
        }
    ]
    
    for booking in tour_bookings:
        stmt = f"""INSERT INTO tour_bookings (user_id, tour_id, tour_date, guests, total_price, status) 
                 VALUES ({booking['user_id']}, {booking['tour_id']}, '{booking['tour_date']}', 
                 {booking['guests']}, {booking['total_price']}, '{booking['status']}');"""
        statements.append(stmt)
    
    return statements

def generate_reviews():
    """Generate mock reviews data."""
    reviews = [
        {
            'user_id': 2,  # John Doe
            'title_en': 'Amazing ocean view!',
            'title_vi': 'Tầm nhìn ra biển tuyệt vời!',
            'content_en': 'The Deluxe Ocean View room exceeded our expectations. The view was breathtaking and the service was excellent.',
            'content_vi': 'Phòng Deluxe Hướng Biển vượt quá mong đợi của chúng tôi. Tầm nhìn thật ngoạn mục và dịch vụ rất xuất sắc.',
            'rating': 5,
            'type': 'room',
            'item_id': 1,
            'is_approved': True,
            'is_hidden': False
        },
        {
            'user_id': 3,  # Jane Smith
            'title_en': 'Lovely garden suite',
            'title_vi': 'Phòng suite vườn đáng yêu',
            'content_en': 'We loved our stay in the Premium Garden Suite. The room was spacious and comfortable.',
            'content_vi': 'Chúng tôi rất thích kỳ nghỉ của mình trong Phòng Suite Hướng Vườn. Phòng rộng rãi và thoải mái.',
            'rating': 4,
            'type': 'room',
            'item_id': 2,
            'is_approved': True,
            'is_hidden': False
        },
        {
            'user_id': 4,  # Nguyen Van A
            'title_en': 'Perfect for families',
            'title_vi': 'Hoàn hảo cho gia đình',
            'content_en': 'The Family Package was perfect for our needs. The kids loved the water park!',
            'content_vi': 'Gói Gia Đình thật hoàn hảo cho nhu cầu của chúng tôi. Bọn trẻ rất thích công viên nước!',
            'rating': 5,
            'type': 'package',
            'item_id': 2,
            'is_approved': False,
            'is_hidden': False
        },
        {
            'user_id': 5,  # Tran Thi B
            'title_en': 'Beautiful islands',
            'title_vi': 'Những hòn đảo xinh đẹp',
            'content_en': 'The Island Hopping Tour was the highlight of our trip. The guide was knowledgeable and friendly.',
            'content_vi': 'Tour Khám Phá Đảo là điểm nhấn của chuyến đi. Hướng dẫn viên có kiến thức và thân thiện.',
            'rating': 4,
            'type': 'tour',
            'item_id': 1,
            'is_approved': True,
            'is_hidden': False
        }
    ]
    
    statements = []
    for review in reviews:
        stmt = f"""INSERT INTO reviews (user_id, title_en, title_vi, content_en, content_vi, rating, type, item_id, is_approved, is_hidden) 
                 VALUES ({review['user_id']}, '{review['title_en']}', '{review['title_vi']}', '{review['content_en']}', 
                 '{review['content_vi']}', {review['rating']}, '{review['type']}', {review['item_id']}, 
                 {1 if review['is_approved'] else 0}, {1 if review['is_hidden'] else 0});"""
        statements.append(stmt)
    
    return statements

def generate_nearby_places():
    """Generate mock nearby places data."""
    places = [
        {
            'name_en': 'Long Son Pagoda',
            'name_vi': 'Chùa Long Sơn',
            'description_en': 'Historic Buddhist temple with a large white Buddha statue overlooking the city.',
            'description_vi': 'Ngôi chùa Phật giáo lịch sử với tượng Phật trắng lớn nhìn xuống thành phố.',
            'category': 'attraction',
            'address': '22 October 23 Street, Nha Trang, Vietnam',
            'distance_km': 4.5,
            'contact_phone': '+84 258 3522 525',
            'website_url': 'https://example.com/longson',
            'booking_url': '',
            'opening_hours': '7:00 AM - 6:00 PM',
            'price_level': '$',
            'image_url': 'assets/images/nearby/long-son-pagoda.jpg'
        },
        {
            'name_en': 'Sailing Club Restaurant',
            'name_vi': 'Nhà hàng Sailing Club',
            'description_en': 'Beachfront restaurant serving international cuisine with stunning ocean views.',
            'description_vi': 'Nhà hàng bên bãi biển phục vụ ẩm thực quốc tế với tầm nhìn ra biển tuyệt đẹp.',
            'category': 'restaurant',
            'address': '72-74 Tran Phu Street, Nha Trang, Vietnam',
            'distance_km': 2.8,
            'contact_phone': '+84 258 3524 628',
            'website_url': 'https://example.com/sailingclub',
            'booking_url': 'https://example.com/sailingclub/reservations',
            'opening_hours': '11:00 AM - 12:00 AM',
            'price_level': '$$$',
            'image_url': 'assets/images/nearby/sailing-club.jpg'
        },
        {
            'name_en': 'Nha Trang Night Market',
            'name_vi': 'Chợ Đêm Nha Trang',
            'description_en': 'Vibrant night market selling local goods, crafts, and street food.',
            'description_vi': 'Chợ đêm sôi động bán hàng hóa địa phương, đồ thủ công và đồ ăn đường phố.',
            'category': 'shopping',
            'address': 'Tran Phu Street, Nha Trang, Vietnam',
            'distance_km': 3.2,
            'contact_phone': '',
            'website_url': '',
            'booking_url': '',
            'opening_hours': '6:00 PM - 12:00 AM',
            'price_level': '$',
            'image_url': 'assets/images/nearby/night-market.jpg'
        },
        {
            'name_en': 'Rainforest Cafe',
            'name_vi': 'Quán Cà Phê Rừng Mưa',
            'description_en': 'Cozy cafe with a tropical rainforest theme and excellent Vietnamese coffee.',
            'description_vi': 'Quán cà phê ấm cúng với chủ đề rừng mưa nhiệt đới và cà phê Việt Nam tuyệt vời.',
            'category': 'cafe',
            'address': '56 Nguyen Thien Thuat Street, Nha Trang, Vietnam',
            'distance_km': 3.8,
            'contact_phone': '+84 258 3526 789',
            'website_url': 'https://example.com/rainforestcafe',
            'booking_url': '',
            'opening_hours': '7:00 AM - 10:00 PM',
            'price_level': '$$',
            'image_url': 'assets/images/nearby/rainforest-cafe.jpg'
        }
    ]
    
    statements = []
    for place in places:
        stmt = f"""INSERT INTO nearby_places (name_en, name_vi, description_en, description_vi, category, address, 
                 distance_km, contact_phone, website_url, booking_url, opening_hours, price_level, image_url) 
                 VALUES ('{place['name_en']}', '{place['name_vi']}', '{place['description_en']}', 
                 '{place['description_vi']}', '{place['category']}', '{place['address']}', {place['distance_km']}, 
                 '{place['contact_phone']}', '{place['website_url']}', '{place['booking_url']}', 
                 '{place['opening_hours']}', '{place['price_level']}', '{place['image_url']}');"""
        statements.append(stmt)
    
    return statements

def generate_content():
    """Generate mock content data for multi-language website content."""
    contents = [
        {
            'page': 'home',
            'section': 'hero',
            'key_name': 'title',
            'content_en': 'Welcome to Vinpearl Resort Nha Trang',
            'content_vi': 'Chào mừng đến với Vinpearl Resort Nha Trang'
        },
        {
            'page': 'home',
            'section': 'hero',
            'key_name': 'subtitle',
            'content_en': 'Experience luxury by the beautiful Nha Trang beach',
            'content_vi': 'Trải nghiệm sang trọng bên bãi biển Nha Trang xinh đẹp'
        },
        {
            'page': 'home',
            'section': 'about',
            'key_name': 'title',
            'content_en': 'About Our Resort',
            'content_vi': 'Về Khu Nghỉ Dưỡng Của Chúng Tôi'
        },
        {
            'page': 'home',
            'section': 'about',
            'key_name': 'content',
            'content_en': 'Vinpearl Resort Nha Trang offers the perfect blend of luxury and natural beauty. Located on a private beach, our resort features spacious rooms with stunning views, world-class dining options, and a range of activities for all ages.',
            'content_vi': 'Vinpearl Resort Nha Trang mang đến sự kết hợp hoàn hảo giữa sang trọng và vẻ đẹp tự nhiên. Tọa lạc trên một bãi biển riêng, khu nghỉ dưỡng của chúng tôi có những phòng rộng rãi với tầm nhìn tuyệt đẹp, các lựa chọn ẩm thực đẳng cấp thế giới và nhiều hoạt động cho mọi lứa tuổi.'
        },
        {
            'page': 'rooms',
            'section': 'header',
            'key_name': 'title',
            'content_en': 'Our Accommodations',
            'content_vi': 'Phòng Nghỉ Của Chúng Tôi'
        },
        {
            'page': 'rooms',
            'section': 'header',
            'key_name': 'subtitle',
            'content_en': 'Discover our range of luxurious rooms and suites',
            'content_vi': 'Khám phá các loại phòng và suite sang trọng của chúng tôi'
        }
    ]
    
    statements = []
    for content in contents:
        stmt = f"""INSERT INTO content (page, section, key_name, content_en, content_vi) 
                 VALUES ('{content['page']}', '{content['section']}', '{content['key_name']}', 
                 '{content['content_en']}', '{content['content_vi']}');"""
        statements.append(stmt)
    
    return statements

def generate_promotional_banners():
    """Generate mock promotional banners data."""
    banners = [
        {
            'title_en': 'Summer Special',
            'title_vi': 'Ưu Đãi Mùa Hè',
            'description_en': 'Enjoy 20% off on all room bookings during summer months',
            'description_vi': 'Giảm 20% cho tất cả các đặt phòng trong những tháng hè',
            'image_url': 'assets/images/banners/summer-special.jpg',
            'link_url': 'promotions/summer-special',
            'is_active': True,
            'position': 1,
            'start_date': '2023-06-01',
            'end_date': '2023-08-31'
        },
        {
            'title_en': 'Honeymoon Package',
            'title_vi': 'Gói Trăng Mật',
            'description_en': 'Special honeymoon package with romantic dinner and spa treatments',
            'description_vi': 'Gói trăng mật đặc biệt với bữa tối lãng mạn và các liệu pháp spa',
            'image_url': 'assets/images/banners/honeymoon.jpg',
            'link_url': 'packages/honeymoon',
            'is_active': True,
            'position': 2,
            'start_date': '2023-01-01',
            'end_date': '2023-12-31'
        }
    ]
    
    statements = []
    for banner in banners:
        stmt = f"""INSERT INTO promotional_banners (title_en, title_vi, description_en, description_vi, image_url, 
                 link_url, is_active, position, start_date, end_date) 
                 VALUES ('{banner['title_en']}', '{banner['title_vi']}', '{banner['description_en']}', 
                 '{banner['description_vi']}', '{banner['image_url']}', '{banner['link_url']}', 
                 {1 if banner['is_active'] else 0}, {banner['position']}, '{banner['start_date']}', '{banner['end_date']}');"""
        statements.append(stmt)
    
    return statements

def write_to_sql_file(sql_statements, filename='mock_data.sql'):
    """Write the generated SQL statements to a file."""
    with open(filename, 'w') as file:
        file.write("-- Mock data for Vinpearl Resort database\n\n")
        file.write("-- Use the vinpearl_resort database\n")
        file.write("USE vinpearl_resort;\n\n")
        
        for stmt in sql_statements:
            file.write(stmt + "\n")

if __name__ == "__main__":
    sql_statements = generate_mock_data()
    write_to_sql_file(sql_statements)
    print(f"Generated {len(sql_statements)} SQL statements for mock data.") 