<?php
/**
 * Footer Template - Vinpearl Resort Nha Trang
 */

// Get language parameter if not already set
$language = isset($language) ? $language : (isset($_GET['lang']) && $_GET['lang'] === 'vi' ? 'vi' : 'en');
$lang = $language; // For compatibility with existing code
?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="assets/images/logo.png" alt="Vinpearl Resort Nha Trang Logo">
                </div>
                <p><?php echo $language === 'vi' ? 
                    'Trải nghiệm sự sang trọng và đẳng cấp tại một trong những khu nghỉ dưỡng hàng đầu của Việt Nam.' : 
                    'Experience luxury and elegance at one of Vietnam\'s premier beachfront resorts.'; ?></p>
            </div>
            
            <div class="footer-column">
                <h3><?php echo $language === 'vi' ? 'Liên kết nhanh' : 'Quick Links'; ?></h3>
                <ul>
                    <li><a href="rooms.php"><?php echo $language === 'vi' ? 'Phòng' : 'Rooms'; ?></a></li>
                    <li><a href="packages.php"><?php echo $language === 'vi' ? 'Gói dịch vụ' : 'Packages'; ?></a></li>
                    <li><a href="tours.php"><?php echo $language === 'vi' ? 'Tours' : 'Tours'; ?></a></li>
                    <li><a href="nearby.php"><?php echo $language === 'vi' ? 'Điểm tham quan' : 'Nearby'; ?></a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><?php echo $language === 'vi' ? 'Kết nối' : 'Connect'; ?></h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3><?php echo $language === 'vi' ? 'Liên hệ' : 'Contact'; ?></h3>
                <p><i class="fas fa-map-marker-alt"></i> Vinpearl Resort Nha Trang, Đảo Hòn Tre, Nha Trang, Việt Nam</p>
                <p><i class="fas fa-phone"></i> +84 258 598 9999</p>
                <p><i class="fas fa-envelope"></i> info@vinpearl.com</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
        </div>
    </div>
</footer>