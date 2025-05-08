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
        <div class="footer-columns">
            <div class="footer-column">
                <h3><?php echo $language === 'vi' ? 'Về chúng tôi' : 'About Us'; ?></h3>
                <p><?php echo $language === 'vi' ? 'Vinpearl Resort Nha Trang là khu nghỉ dưỡng sang trọng với tầm nhìn tuyệt đẹp ra biển và dịch vụ đẳng cấp 5 sao.' : 'Vinpearl Resort Nha Trang is a luxury resort with stunning ocean views and 5-star service.'; ?></p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
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
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Vinpearl Resort Nha Trang. <?php echo $language === 'vi' ? 'Đã đăng ký bản quyền.' : 'All rights reserved.'; ?></p>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<a href="#" class="back-to-top">
    <i class="fas fa-chevron-up"></i>
</a>

<script src="assets/js/script.js"></script>