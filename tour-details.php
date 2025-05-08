<?php
/**
 * Vinpearl Resort Nha Trang - Tour Details Page
 * With booking functionality
 */

require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Determine language
$language = isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en';

// Get tour ID from URL
$tourId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// For consistency with tours.php, we'll use the same hardcoded tour data
// This ensures we use the same image URLs for all tours
$selectedTour = null;

// If tour not found in database, fall back to hardcoded tours
if (!$selectedTour) {
    // For this enhancement demo, we'll use hardcoded tours
    $tours = [
    [
        'id' => 1,
        'name_en' => 'Island Hopping Adventure',
        'name_vi' => 'Phiêu Lưu Khám Phá Đảo',
        'description_en' => 'Explore the stunning islands around Nha Trang with our professional guides. Swim, snorkel, and enjoy fresh seafood lunch.',
        'description_vi' => 'Khám phá những hòn đảo tuyệt đẹp quanh Nha Trang với đội ngũ hướng dẫn viên chuyên nghiệp. Bơi, lặn và thưởng thức bữa trưa hải sản tươi ngon.',
        'category_en' => 'Adventure',
        'category_vi' => 'Phiêu Lưu',
        'duration' => '8 hours',
        'max_people' => 12,
        'price_per_person' => 89,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Boat ride, Snorkeling gear, Lunch, Drinks',
        'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80',
        'full_description_en' => 'Embark on an unforgettable island-hopping adventure around the stunning Nha Trang Bay. Our experienced guides will take you to multiple islands where you can swim in crystal-clear waters, snorkel among vibrant coral reefs, and discover the diverse marine life. Enjoy a delicious seafood lunch prepared fresh on one of the islands. This tour is perfect for nature lovers and adventure seekers who want to experience the natural beauty of Vietnam\'s coastline. All necessary equipment is provided, and no prior snorkeling experience is required.',
        'full_description_vi' => 'Bắt đầu chuyến phiêu lưu khám phá đảo không thể quên quanh Vịnh Nha Trang tuyệt đẹp. Các hướng dẫn viên giàu kinh nghiệm của chúng tôi sẽ đưa bạn đến nhiều hòn đảo, nơi bạn có thể bơi trong làn nước trong vắt, lặn ngắm san hô rực rỡ và khám phá đa dạng sinh vật biển. Thưởng thức bữa trưa hải sản ngon tuyệt được chế biến tươi ngon trên một trong những hòn đảo. Tour này hoàn hảo cho những người yêu thiên nhiên và những người tìm kiếm phiêu lưu muốn trải nghiệm vẻ đẹp tự nhiên của bờ biển Việt Nam. Tất cả các thiết bị cần thiết đều được cung cấp và không cần kinh nghiệm lặn trước đó.'
    ],
    [
        'id' => 2,
        'name_en' => 'City Cultural Tour',
        'name_vi' => 'Tour Văn Hóa Thành Phố',
        'description_en' => 'Discover Nha Trang\'s cultural heritage with visits to ancient temples, historic sites, and local craft villages.',
        'description_vi' => 'Khám phá di sản văn hóa của Nha Trang với các chuyến thăm đến đền chùa cổ, các di tích lịch sử và làng nghề thủ công.',
        'category_en' => 'Cultural',
        'category_vi' => 'Văn Hóa',
        'duration' => '6 hours',
        'max_people' => 15,
        'price_per_person' => 65,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Transportation, Entrance fees, Local guide, Lunch',
        'image_url' => 'https://images.pexels.com/photos/6143369/pexels-photo-6143369.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
        'full_description_en' => 'Immerse yourself in the rich cultural heritage of Nha Trang with our comprehensive city tour. Visit ancient Cham temples that date back centuries, explore historical landmarks that tell the story of Vietnam\'s past, and experience the authentic lifestyle in local craft villages. Our knowledgeable guides will share fascinating insights about the region\'s history, traditions, and customs. You\'ll also have the opportunity to interact with local artisans and learn about traditional crafts. The tour includes a delicious authentic Vietnamese lunch at a local restaurant, giving you a taste of the region\'s culinary delights.',
        'full_description_vi' => 'Đắm mình trong di sản văn hóa phong phú của Nha Trang với tour thành phố toàn diện của chúng tôi. Thăm các đền thờ Chăm cổ có niên đại hàng thế kỷ, khám phá các địa danh lịch sử kể câu chuyện về quá khứ của Việt Nam và trải nghiệm lối sống đích thực tại các làng nghề địa phương. Các hướng dẫn viên am hiểu của chúng tôi sẽ chia sẻ những hiểu biết hấp dẫn về lịch sử, truyền thống và phong tục của vùng. Bạn cũng sẽ có cơ hội tương tác với các nghệ nhân địa phương và tìm hiểu về các nghề thủ công truyền thống. Tour bao gồm bữa trưa đích thực của Việt Nam tại nhà hàng địa phương, mang đến cho bạn hương vị ẩm thực đặc trưng của vùng.'
    ],
    [
        'id' => 3,
        'name_en' => 'Sunset Sailing Cruise',
        'name_vi' => 'Du Thuyền Hoàng Hôn',
        'description_en' => 'Enjoy a breathtaking sunset on our luxury catamaran. Includes cocktails, light dinner, and the opportunity to spot marine life.',
        'description_vi' => 'Tận hưởng cảnh hoàng hôn tuyệt đẹp trên du thuyền sang trọng. Bao gồm cocktail, bữa tối nhẹ và cơ hội ngắm nhìn sinh vật biển.',
        'category_en' => 'Relaxation',
        'category_vi' => 'Thư Giãn',
        'duration' => '3 hours',
        'max_people' => 20,
        'price_per_person' => 120,
        'meeting_point_en' => 'Resort Marina',
        'meeting_point_vi' => 'Bến Du Thuyền Resort',
        'includes' => 'Luxury catamaran, Cocktails, Dinner, Live music',
        'image_url' => 'https://images.pexels.com/photos/163236/luxury-yacht-boat-speed-water-163236.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
        'full_description_en' => 'Set sail on our luxury catamaran for an unforgettable sunset experience on the waters of Nha Trang Bay. As the sun begins to set, the sky transforms into a canvas of vibrant colors, creating a magical backdrop for your evening cruise. Sip on handcrafted cocktails and enjoy a delicious light dinner featuring local and international cuisine. Our attentive crew will point out marine life such as dolphins and flying fish that often appear during this time. The gentle sound of live acoustic music completes this perfect evening on the water. This cruise is ideal for couples seeking romance, photographers capturing the perfect sunset, or anyone looking to end their day in a truly special way.',
        'full_description_vi' => 'Khởi hành trên du thuyền sang trọng của chúng tôi để có trải nghiệm hoàng hôn khó quên trên vùng biển Vịnh Nha Trang. Khi mặt trời bắt đầu lặn, bầu trời biến thành một bức tranh với những màu sắc rực rỡ, tạo nên một khung cảnh kỳ diệu cho chuyến du thuyền buổi tối của bạn. Nhâm nhi những ly cocktail thủ công và thưởng thức bữa tối nhẹ nhàng ngon miệng với các món ăn địa phương và quốc tế. Đội thủy thủ chu đáo của chúng tôi sẽ chỉ cho bạn thấy những sinh vật biển như cá heo và cá chuồn thường xuất hiện trong thời gian này. Âm thanh nhẹ nhàng của nhạc acoustic sống hoàn thiện buổi tối hoàn hảo này trên mặt nước. Chuyến du thuyền này lý tưởng cho các cặp đôi tìm kiếm sự lãng mạn, nhiếp ảnh gia ghi lại hoàng hôn hoàn hảo, hoặc bất kỳ ai muốn kết thúc ngày của họ theo một cách thực sự đặc biệt.'
    ],
    [
        'id' => 4,
        'name_en' => 'Countryside Bike Tour',
        'name_vi' => 'Tour Xe Đạp Vùng Quê',
        'description_en' => 'Cycle through the picturesque countryside of Nha Trang, visit local villages, rice fields, and experience authentic rural life.',
        'description_vi' => 'Đạp xe qua những vùng quê đẹp như tranh của Nha Trang, thăm các làng quê, ruộng lúa và trải nghiệm cuộc sống nông thôn đích thực.',
        'category_en' => 'Adventure',
        'category_vi' => 'Phiêu Lưu',
        'duration' => '5 hours',
        'max_people' => 10,
        'price_per_person' => 55,
        'meeting_point_en' => 'Resort Lobby',
        'meeting_point_vi' => 'Sảnh Resort',
        'includes' => 'Bike rental, Safety gear, Water, Local snacks',
        'image_url' => 'https://images.pexels.com/photos/100582/pexels-photo-100582.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
        'full_description_en' => 'Escape the bustling city and discover the serene beauty of Nha Trang\'s countryside on our guided bicycle tour. Pedal through lush landscapes of rice paddies, vegetable farms, and fruit orchards while enjoying the fresh air and stunning mountain views. Visit authentic local villages where you\'ll have the chance to meet friendly locals and learn about their traditional way of life. Stop at a family home to see how rice paper and rice wine are made using age-old techniques. This leisurely-paced tour is suitable for riders of all levels, with plenty of stops for photos, rest, and refreshments. Experience a side of Vietnam that many tourists never see on this immersive cultural adventure.',
        'full_description_vi' => 'Thoát khỏi thành phố nhộn nhịp và khám phá vẻ đẹp thanh bình của vùng quê Nha Trang trong tour xe đạp có hướng dẫn của chúng tôi. Đạp xe qua những cảnh quan xanh tươi của cánh đồng lúa, trang trại rau và vườn cây ăn quả trong khi tận hưởng không khí trong lành và tầm nhìn núi non tuyệt đẹp. Thăm các làng quê đích thực nơi bạn sẽ có cơ hội gặp gỡ người dân địa phương thân thiện và tìm hiểu về lối sống truyền thống của họ. Dừng chân tại một ngôi nhà gia đình để xem cách làm bánh tráng và rượu gạo bằng kỹ thuật lâu đời. Tour với nhịp độ thong thả này phù hợp với người đi xe đạp ở mọi trình độ, với nhiều điểm dừng để chụp ảnh, nghỉ ngơi và giải khát. Trải nghiệm một khía cạnh của Việt Nam mà nhiều du khách không bao giờ thấy trong cuộc phiêu lưu văn hóa đắm chìm này.'
    ],
    [
        'id' => 5,
        'name_en' => 'Cooking Class & Market Tour',
        'name_vi' => 'Lớp Học Nấu Ăn & Tour Chợ',
        'description_en' => 'Learn the art of Vietnamese cuisine with a local chef. Visit the market to select fresh ingredients and prepare traditional dishes.',
        'description_vi' => 'Học nghệ thuật ẩm thực Việt Nam với đầu bếp địa phương. Thăm chợ để chọn nguyên liệu tươi và chế biến các món ăn truyền thống.',
        'category_en' => 'Food',
        'category_vi' => 'Ẩm Thực',
        'duration' => '4 hours',
        'max_people' => 8,
        'price_per_person' => 75,
        'meeting_point_en' => 'Resort Restaurant',
        'meeting_point_vi' => 'Nhà Hàng Resort',
        'includes' => 'Market visit, Ingredients, Cooking tools, Recipe book',
        'image_url' => 'https://images.unsplash.com/photo-1569420067112-b57b4f024595?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80',
        'full_description_en' => 'Discover the secrets of Vietnamese cuisine in our hands-on cooking class led by an experienced local chef. The experience begins with a guided tour of a vibrant local market, where you\'ll learn to select the freshest ingredients and discover exotic herbs and spices essential to Vietnamese cooking. Back in our purpose-built cooking school, you\'ll prepare a complete menu of traditional dishes, mastering techniques that have been passed down through generations. Learn to balance the five fundamental flavors that make Vietnamese cuisine so distinctive: sweet, sour, bitter, spicy, and salty. At the end of the class, enjoy the delicious meal you\'ve created in a convivial atmosphere. You\'ll also receive a recipe book so you can recreate these authentic dishes at home and impress your friends and family.',
        'full_description_vi' => 'Khám phá bí mật của ẩm thực Việt Nam trong lớp học nấu ăn thực hành của chúng tôi dưới sự hướng dẫn của đầu bếp địa phương giàu kinh nghiệm. Trải nghiệm bắt đầu với chuyến tham quan có hướng dẫn đến chợ địa phương sôi động, nơi bạn sẽ học cách chọn nguyên liệu tươi ngon nhất và khám phá các loại thảo mộc và gia vị độc đáo cần thiết cho ẩm thực Việt Nam. Trở lại trường dạy nấu ăn được xây dựng cho mục đích này, bạn sẽ chuẩn bị một thực đơn đầy đủ các món ăn truyền thống, làm chủ các kỹ thuật đã được truyền qua nhiều thế hệ. Học cách cân bằng năm hương vị cơ bản làm nên sự đặc biệt của ẩm thực Việt Nam: ngọt, chua, đắng, cay và mặn. Vào cuối lớp học, thưởng thức bữa ăn ngon bạn đã tạo ra trong không khí thân thiện. Bạn cũng sẽ nhận được một cuốn sách công thức để có thể tái tạo những món ăn đích thực này tại nhà và gây ấn tượng với bạn bè và gia đình.'
    ],
    [
        'id' => 6,
        'name_en' => 'Mud Bath Spa Experience',
        'name_vi' => 'Trải Nghiệm Tắm Bùn Khoáng',
        'description_en' => 'Relax in the famous Nha Trang mineral mud baths. Includes spa treatments, mineral water swimming, and private relaxation areas.',
        'description_vi' => 'Thư giãn trong các bồn tắm bùn khoáng nổi tiếng của Nha Trang. Bao gồm các liệu pháp spa, bơi nước khoáng và khu thư giãn riêng.',
        'category_en' => 'Wellness',
        'category_vi' => 'Sức Khỏe',
        'duration' => '4 hours',
        'max_people' => 10,
        'price_per_person' => 95,
        'meeting_point_en' => 'Resort Spa',
        'meeting_point_vi' => 'Spa Resort',
        'includes' => 'Mud bath, Hot mineral bath, Jacuzzi, Massage',
        'image_url' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1950&q=80',
        'full_description_en' => 'Indulge in a rejuvenating spa experience at Nha Trang\'s renowned mineral mud baths. This therapeutic treatment begins with a warm mineral mud bath, rich in natural elements that nourish your skin and relieve muscle tension. The mineral-rich mud is known for its detoxifying properties and ability to improve circulation. After your mud bath, rinse off in a refreshing mineral waterfall before relaxing in a hot mineral water pool. The experience continues with time in a jacuzzi and a professional 30-minute massage focusing on pressure points to release any remaining tension. Throughout your spa journey, you\'ll have access to private relaxation areas where you can unwind with herbal tea and fresh fruit. This wellness experience will leave you feeling completely refreshed and revitalized.',
        'full_description_vi' => 'Đắm mình trong trải nghiệm spa trẻ hóa tại các bồn tắm bùn khoáng nổi tiếng của Nha Trang. Liệu pháp trị liệu này bắt đầu với bồn tắm bùn khoáng ấm, giàu các nguyên tố tự nhiên nuôi dưỡng làn da và giảm căng thẳng cơ bắp. Bùn giàu khoáng chất được biết đến với đặc tính giải độc và khả năng cải thiện tuần hoàn. Sau khi tắm bùn, rửa sạch dưới thác nước khoáng mát lạnh trước khi thư giãn trong hồ nước khoáng nóng. Trải nghiệm tiếp tục với thời gian trong bồn sục và massage chuyên nghiệp 30 phút tập trung vào các điểm áp lực để giải phóng bất kỳ căng thẳng còn lại. Trong suốt hành trình spa, bạn sẽ có quyền truy cập vào các khu vực thư giãn riêng tư nơi bạn có thể thư giãn với trà thảo mộc và trái cây tươi. Trải nghiệm sức khỏe này sẽ khiến bạn cảm thấy hoàn toàn sảng khoái và tràn đầy sinh lực.'
    ]
];

// Find the selected tour
$selectedTour = null;
foreach ($tours as $tour) {
    if ($tour['id'] == $tourId) {
        $selectedTour = $tour;
        break;
    }
}
}

// If tour not found, redirect to tours page
if (!$selectedTour) {
    header('Location: tours.php');
    exit;
}

// Get current user if logged in
$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();

// Handle booking form submission
$bookingSuccess = false;
$bookingError = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_tour'])) {
    // Validate form data
    $formData = [
        'tour_date' => $_POST['tour_date'] ?? '',
        'num_people' => (int)($_POST['num_people'] ?? 1),
        'special_requests' => $_POST['special_requests'] ?? ''
    ];
    
    // Basic validation
    if (empty($formData['tour_date'])) {
        $bookingError = $language === 'vi' ? 'Vui lòng chọn ngày tour.' : 'Please select a tour date.';
    } elseif ($formData['num_people'] < 1 || $formData['num_people'] > $selectedTour['max_people']) {
        $bookingError = $language === 'vi' ? 
            'Số lượng người không hợp lệ. Tối đa ' . $selectedTour['max_people'] . ' người.' : 
            'Invalid number of people. Maximum ' . $selectedTour['max_people'] . ' people.';
    } elseif (!$isLoggedIn) {
        $bookingError = $language === 'vi' ? 
            'Vui lòng đăng nhập để đặt tour.' : 
            'Please log in to book a tour.';
    } else {
        // Calculate total price
        $totalPrice = $selectedTour['price_per_person'] * $formData['num_people'];
        
        // Save booking to database
        $conn = connectDatabase();
        if ($conn) {
            // Prepare SQL statement using the existing table structure
            // The table has 'guests' instead of 'num_people' and no 'special_requests' column
            $stmt = $conn->prepare("INSERT INTO tour_bookings (user_id, tour_id, tour_date, guests, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt) {
                // Bind parameters
                $stmt->bind_param("iisid", 
                    $currentUser['id'], 
                    $selectedTour['id'], 
                    $formData['tour_date'], 
                    $formData['num_people'], 
                    $totalPrice
                );
                
                // Execute statement
                if ($stmt->execute()) {
                    $bookingSuccess = true;
                    
                    // Clear form data after successful submission
                    $formData = [];
                } else {
                    $bookingError = $language === 'vi' ? 
                        'Có lỗi xảy ra khi đặt tour. Vui lòng thử lại.' : 
                        'An error occurred while booking the tour. Please try again.';
                }
                
                $stmt->close();
            } else {
                $bookingError = $language === 'vi' ? 
                    'Có lỗi xảy ra khi đặt tour. Vui lòng thử lại.' : 
                    'An error occurred while booking the tour. Please try again.';
            }
            
            $conn->close();
        } else {
            $bookingError = $language === 'vi' ? 
                'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.' : 
                'Could not connect to the database. Please try again later.';
        }
    }
}

// Page title
$tourName = $language === 'vi' ? $selectedTour['name_vi'] : $selectedTour['name_en'];
$pageTitle = $tourName;
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo generatePageTitle($pageTitle, $language); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/tours-styles.css">
    <style>
        /* Tour Details Specific Styles */
        .tour-detail-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .tour-detail-image {
            flex: 1;
            min-width: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tour-detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .tour-detail-content {
            flex: 1;
            min-width: 300px;
        }
        
        .tour-detail-header {
            margin-bottom: 1.5rem;
        }
        
        .tour-detail-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .tour-category {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background-color: var(--accent-color);
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .tour-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .tour-highlight-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .tour-highlight-item i {
            color: var(--primary-color);
        }
        
        .tour-description {
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        
        .tour-price-box {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .tour-price-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .tour-price-label {
            font-size: 1rem;
            color: #6c757d;
        }
        
        .booking-form {
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .booking-form h3 {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .booking-total {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .booking-total-label {
            font-weight: 500;
        }
        
        .booking-total-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .btn-book {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn-book:hover {
            background-color: var(--primary-dark);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-prompt {
            text-align: center;
            margin-top: 1rem;
        }
        
        .login-prompt a {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .tour-includes {
            margin-top: 2rem;
        }
        
        .tour-includes h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .includes-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .includes-item {
            background-color: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .includes-item i {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .tour-detail-container {
                flex-direction: column;
            }
            
            .tour-detail-image {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Vinpearl Resort Nha Trang">
                </a>
            </div>
            
            <nav class="main-navigation">
                <ul>
                    <li><a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                    <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php" class="active"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <div class="language-selector">
                    <a href="?id=<?php echo $tourId; ?>&lang=en" <?php echo $language === 'en' ? 'class="active"' : ''; ?>>EN</a> |
                    <a href="?id=<?php echo $tourId; ?>&lang=vi" <?php echo $language === 'vi' ? 'class="active"' : ''; ?>>VI</a>
                </div>
                
                <div class="user-actions">
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span>Hello, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                            <div class="dropdown-menu">
                                <a href="profile.php"><?php echo $language === 'vi' ? 'Hồ sơ' : 'Profile'; ?></a>
                                <a href="bookings.php"><?php echo $language === 'vi' ? 'Đặt chỗ' : 'My Bookings'; ?></a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/index.php"><?php echo $language === 'vi' ? 'Quản trị' : 'Admin Panel'; ?></a>
                                <?php endif; ?>
                                <a href="logout.php"><?php echo $language === 'vi' ? 'Đăng xuất' : 'Logout'; ?></a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm"><?php echo $language === 'vi' ? 'Đăng nhập' : 'Login'; ?></a>
                        <a href="register.php" class="btn btn-sm btn-outline"><?php echo $language === 'vi' ? 'Đăng ký' : 'Register'; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Tour Details Content -->
    <main>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a> &gt;
                <a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a> &gt;
                <span><?php echo $tourName; ?></span>
            </div>
            
            <div class="tour-detail-container">
                <div class="tour-detail-image">
                    <img src="<?php echo htmlspecialchars($selectedTour['image_url']); ?>" alt="<?php echo htmlspecialchars($tourName); ?>">
                </div>
                
                <div class="tour-detail-content">
                    <div class="tour-detail-header">
                        <h1><?php echo $tourName; ?></h1>
                        <div class="tour-category">
                            <i class="fas fa-tag"></i> 
                            <?php echo $language === 'vi' ? $selectedTour['category_vi'] : $selectedTour['category_en']; ?>
                        </div>
                    </div>
                    
                    <div class="tour-highlights">
                        <div class="tour-highlight-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $selectedTour['duration']; ?></span>
                        </div>
                        <div class="tour-highlight-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo $language === 'vi' ? 'Tối đa ' : 'Max '; ?><?php echo $selectedTour['max_people']; ?> <?php echo $language === 'vi' ? 'người' : 'people'; ?></span>
                        </div>
                        <div class="tour-highlight-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $language === 'vi' ? $selectedTour['meeting_point_vi'] : $selectedTour['meeting_point_en']; ?></span>
                        </div>
                    </div>
                    
                    <div class="tour-description">
                        <p><?php echo $language === 'vi' ? $selectedTour['full_description_vi'] : $selectedTour['full_description_en']; ?></p>
                    </div>
                    
                    <div class="tour-includes">
                        <h3><?php echo $language === 'vi' ? 'Bao gồm' : 'Includes'; ?></h3>
                        <div class="includes-list">
                            <?php foreach(explode(', ', $selectedTour['includes']) as $item): ?>
                                <div class="includes-item">
                                    <i class="fas fa-check"></i>
                                    <span><?php echo $item; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tour-price-box">
                        <span class="tour-price-value"><?php echo formatCurrency($selectedTour['price_per_person']); ?></span>
                        <span class="tour-price-label"><?php echo $language === 'vi' ? '/ người' : '/ person'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Booking Form Section -->
            <section class="booking-section">
                <div class="container">
                    <h2><?php echo $language === 'vi' ? 'Đặt tour' : 'Book this Tour'; ?></h2>
                    
                    <?php if ($bookingSuccess): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $language === 'vi' ? 
                                'Đặt tour thành công! Chúng tôi sẽ liên hệ với bạn để xác nhận chi tiết.' : 
                                'Tour booked successfully! We will contact you to confirm the details.'; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($bookingError): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $bookingError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="booking-form">
                        <?php if ($isLoggedIn): ?>
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="tour_date"><?php echo $language === 'vi' ? 'Ngày tour' : 'Tour Date'; ?></label>
                                    <input type="date" id="tour_date" name="tour_date" class="form-control" 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                                           value="<?php echo $formData['tour_date'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="num_people"><?php echo $language === 'vi' ? 'Số người' : 'Number of People'; ?></label>
                                    <input type="number" id="num_people" name="num_people" class="form-control" 
                                           min="1" max="<?php echo $selectedTour['max_people']; ?>" 
                                           value="<?php echo $formData['num_people'] ?? 1; ?>" required>
                                    <small><?php echo $language === 'vi' ? 
                                        'Tối đa ' . $selectedTour['max_people'] . ' người' : 
                                        'Maximum ' . $selectedTour['max_people'] . ' people'; ?></small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="special_requests"><?php echo $language === 'vi' ? 'Yêu cầu đặc biệt' : 'Special Requests'; ?></label>
                                    <textarea id="special_requests" name="special_requests" class="form-control"><?php echo $formData['special_requests'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="booking-total">
                                    <span class="booking-total-label"><?php echo $language === 'vi' ? 'Tổng cộng' : 'Total'; ?>:</span>
                                    <span class="booking-total-value" id="booking-total-value">
                                        <?php echo formatCurrency($selectedTour['price_per_person']); ?>
                                    </span>
                                </div>
                                
                                <button type="submit" name="book_tour" class="btn-book">
                                    <?php echo $language === 'vi' ? 'Xác nhận đặt tour' : 'Confirm Booking'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="login-prompt">
                                <p><?php echo $language === 'vi' ? 
                                    'Vui lòng đăng nhập để đặt tour.' : 
                                    'Please log in to book this tour.'; ?></p>
                                <a href="login.php?redirect=tour-details.php?id=<?php echo $tourId; ?>" class="btn btn-primary">
                                    <?php echo $language === 'vi' ? 'Đăng nhập ngay' : 'Log in now'; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Về chúng tôi' : 'About Us'; ?></h3>
                    <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang là điểm đến sang trọng hàng đầu tại Việt Nam, mang đến trải nghiệm nghỉ dưỡng đẳng cấp thế giới.' : 'Vinpearl Resort Nha Trang is Vietnam\'s premier luxury destination, offering a world-class vacation experience.'; ?></p>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên kết nhanh' : 'Quick Links'; ?></h3>
                    <ul class="footer-links">
                        <li><a href="index.php"><?php echo $language === 'vi' ? 'Trang chủ' : 'Home'; ?></a></li>
                        <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                        <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                        <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                        </ul>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact Us'; ?></h3>
                    <address>
                        <p><i class="fas fa-map-marker-alt"></i> Vinpearl Resort Nha Trang, Việt Nam</p>
                        <p><i class="fas fa-phone"></i> +84 258 359 8888</p>
                        <p><i class="fas fa-envelope"></i> info@vinpearlresort.com</p>
                    </address>
                </div>
                
                <div class="footer-column">
                    <h3><?php echo $language === 'vi' ? 'Theo dõi chúng tôi' : 'Follow Us'; ?></h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký Bản quyền.' : 'All Rights Reserved.'; ?></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for calculating total price -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const numPeopleInput = document.getElementById('num_people');
            const totalValueElement = document.getElementById('booking-total-value');
            const pricePerPerson = <?php echo $selectedTour['price_per_person']; ?>;
            
            function updateTotal() {
                if (numPeopleInput) {
                    const numPeople = parseInt(numPeopleInput.value) || 1;
                    const total = pricePerPerson * numPeople;
                    
                    // Format the total with currency symbol
                    const formattedTotal = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                        minimumFractionDigits: 0
                    }).format(total);
                    
                    if (totalValueElement) {
                        totalValueElement.textContent = formattedTotal;
                    }
                }
            }
            
            // Initial calculation
            updateTotal();
            
            // Update when number of people changes
            if (numPeopleInput) {
                numPeopleInput.addEventListener('input', updateTotal);
            }
        });
    </script>
</body>
</html>