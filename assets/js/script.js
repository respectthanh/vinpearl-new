/**
 * Vinpearl Resort Nha Trang - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initSliders();
    initDatePickers();
    initMobileMenu();
    initRoomBooking();
    initFormValidation();
    
    // Animate elements when they come into view
    function animateOnScroll() {
        const elements = document.querySelectorAll('.room-card, .service-card, .stat-item');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight;
            
            if (elementPosition < screenPosition - 100) {
                element.classList.add('animate');
            }
        });
    }
    
    // Run on page load
    animateOnScroll();
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
    
    // Initialize date inputs with minimum today's date
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        if (input.getAttribute('min') === '<?php echo date("Y-m-d"); ?>' || !input.getAttribute('min')) {
            input.setAttribute('min', today);
        }
        
        // If it's a check-out date and no value is set, default to tomorrow
        if (input.id === 'check_out_date' && !input.value) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            input.setAttribute('min', tomorrowStr);
        }
    });
    
    // Handle check-in/check-out date relationship
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            // Set checkout minimum date to be at least check-in date + 1
            const checkInDate = new Date(this.value);
            checkInDate.setDate(checkInDate.getDate() + 1);
            const newMinDate = checkInDate.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', newMinDate);
            
            // If current checkout date is now invalid, update it
            if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
                checkOutInput.value = newMinDate;
            }
        });
    }

    // Simple testimonial slider
    let currentTestimonial = 0;
    const testimonials = document.querySelectorAll('.testimonial-slide');
    
    if (testimonials.length > 1) {
        // Hide all slides except the first one
        for (let i = 1; i < testimonials.length; i++) {
            testimonials[i].style.display = 'none';
        }
        
        // Change testimonial every 5 seconds
        setInterval(() => {
            testimonials[currentTestimonial].style.display = 'none';
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            testimonials[currentTestimonial].style.display = 'block';
        }, 5000);
    }

    // Enhanced testimonial slider for homepage
    const enhancedTestimonials = document.querySelectorAll('.testimonial-slide');
    
    if (enhancedTestimonials.length > 1) {
        // Hide all except first testimonial
        for (let i = 1; i < enhancedTestimonials.length; i++) {
            enhancedTestimonials[i].style.display = 'none';
        }
        
        let currentEnhancedTestimonial = 0;
        
        // Transition function with fade effect
        function showNextTestimonial() {
            // Fade out current testimonial
            enhancedTestimonials[currentEnhancedTestimonial].style.opacity = '0';
            
            setTimeout(function() {
                // Hide current testimonial
                enhancedTestimonials[currentEnhancedTestimonial].style.display = 'none';
                
                // Move to next testimonial
                currentEnhancedTestimonial = (currentEnhancedTestimonial + 1) % enhancedTestimonials.length;
                
                // Show next testimonial
                enhancedTestimonials[currentEnhancedTestimonial].style.display = 'block';
                
                // Fade in next testimonial
                setTimeout(function() {
                    enhancedTestimonials[currentEnhancedTestimonial].style.opacity = '1';
                }, 50);
                
            }, 500);
        }
        
        // Set initial opacity for transition
        enhancedTestimonials.forEach(testimonial => {
            testimonial.style.transition = 'opacity 0.5s ease';
            testimonial.style.opacity = '1';
        });
        
        // Cycle through testimonials
        setInterval(showNextTestimonial, 5000);
    }

    // Animate room cards immediately on page load
    const roomCards = document.querySelectorAll('.room-card');
    roomCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate');
        }, 200 * index); // Stagger the animations
    });
    
    // Animate elements when they come into view
    function animateOnScroll() {
        const elements = document.querySelectorAll('.service-card, .stat-item');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight;
            
            if (elementPosition < screenPosition - 100) {
                element.classList.add('animate');
            }
        });
    }
    
    // Run on page load
    animateOnScroll();
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
});

/**
 * Initialize sliders/carousels
 */
function initSliders() {
    // Check if banner slider exists
    const bannerSlider = document.querySelector('.banner-slider');
    if (bannerSlider) {
        // Simple slider implementation
        // In a real project, you might want to use a library like Swiper or Slick
        let currentSlide = 0;
        const slides = bannerSlider.querySelectorAll('.banner-slide');
        const totalSlides = slides.length;
        
        // If there's only one slide, don't set up slider
        if (totalSlides <= 1) return;
        
        // Create navigation dots
        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'slider-dots';
        
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';
            dot.setAttribute('data-slide', i);
            dot.addEventListener('click', function() {
                goToSlide(parseInt(this.getAttribute('data-slide')));
            });
            dotsContainer.appendChild(dot);
        }
        
        bannerSlider.appendChild(dotsContainer);
        
        // Create next/prev buttons
        const prevButton = document.createElement('button');
        prevButton.className = 'slider-nav slider-prev';
        prevButton.innerHTML = '&larr;';
        prevButton.addEventListener('click', function() {
            goToSlide((currentSlide - 1 + totalSlides) % totalSlides);
        });
        
        const nextButton = document.createElement('button');
        nextButton.className = 'slider-nav slider-next';
        nextButton.innerHTML = '&rarr;';
        nextButton.addEventListener('click', function() {
            goToSlide((currentSlide + 1) % totalSlides);
        });
        
        bannerSlider.appendChild(prevButton);
        bannerSlider.appendChild(nextButton);
        
        // Function to go to a specific slide
        function goToSlide(slideIndex) {
            slides.forEach((slide, index) => {
                slide.style.display = index === slideIndex ? 'flex' : 'none';
            });
            
            // Update dots
            dotsContainer.querySelectorAll('.slider-dot').forEach((dot, index) => {
                if (index === slideIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            currentSlide = slideIndex;
        }
        
        // Initialize first slide
        goToSlide(0);
        
        // Optional: Auto-slide
        setInterval(function() {
            goToSlide((currentSlide + 1) % totalSlides);
        }, 5000);
    }
    
    // Room image sliders (for room details page)
    const roomSliders = document.querySelectorAll('.room-slider');
    roomSliders.forEach(initRoomSlider);
    
    function initRoomSlider(sliderElement) {
        // Similar slider implementation for room images
        let currentImg = 0;
        const images = sliderElement.querySelectorAll('.room-slide');
        const totalImages = images.length;
        
        if (totalImages <= 1) return;
        
        // Create navigation dots
        const dotsContainer = document.createElement('div');
        dotsContainer.className = 'slider-dots';
        
        for (let i = 0; i < totalImages; i++) {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';
            dot.setAttribute('data-slide', i);
            dot.addEventListener('click', function() {
                goToImage(parseInt(this.getAttribute('data-slide')));
            });
            dotsContainer.appendChild(dot);
        }
        
        sliderElement.appendChild(dotsContainer);
        
        // Create next/prev buttons
        const prevButton = document.createElement('button');
        prevButton.className = 'slider-nav slider-prev';
        prevButton.innerHTML = '&larr;';
        prevButton.addEventListener('click', function() {
            goToImage((currentImg - 1 + totalImages) % totalImages);
        });
        
        const nextButton = document.createElement('button');
        nextButton.className = 'slider-nav slider-next';
        nextButton.innerHTML = '&rarr;';
        nextButton.addEventListener('click', function() {
            goToImage((currentImg + 1) % totalImages);
        });
        
        sliderElement.appendChild(prevButton);
        sliderElement.appendChild(nextButton);
        
        // Function to go to a specific image
        function goToImage(imageIndex) {
            images.forEach((img, index) => {
                img.style.display = index === imageIndex ? 'block' : 'none';
            });
            
            // Update dots
            dotsContainer.querySelectorAll('.slider-dot').forEach((dot, index) => {
                if (index === imageIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            currentImg = imageIndex;
        }
        
        // Initialize first image
        goToImage(0);
    }
}

/**
 * Initialize date pickers
 */
function initDatePickers() {
    // In a real project, you'd use a library like flatpickr or datepicker.js
    // For this example, we'll just add basic functionality using HTML5 date inputs
    
    // Get all date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set min date to today
        if (input.classList.contains('future-date')) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
        
        // If there are paired check-in/check-out inputs, handle their relationship
        if (input.id === 'check_in_date') {
            input.addEventListener('change', function() {
                const checkOutInput = document.getElementById('check_out_date');
                if (checkOutInput) {
                    // Set minimum check-out date to the day after check-in
                    const checkInDate = new Date(this.value);
                    checkInDate.setDate(checkInDate.getDate() + 1);
                    const minCheckOutDate = checkInDate.toISOString().split('T')[0];
                    checkOutInput.setAttribute('min', minCheckOutDate);
                    
                    // If check-out date is before new check-in date, update it
                    if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
                        checkOutInput.value = minCheckOutDate;
                    }
                }
            });
        }
    });
}

/**
 * Initialize mobile menu functionality
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNavigation = document.querySelector('.main-navigation');
    
    if (mobileMenuBtn && mainNavigation) {
        mobileMenuBtn.addEventListener('click', function() {
            mainNavigation.classList.toggle('active');
            this.classList.toggle('active');
            
            // Accessibility
            const expanded = mainNavigation.classList.contains('active');
            this.setAttribute('aria-expanded', expanded.toString());
        });
    }
}

/**
 * Initialize room booking functionality
 */
function initRoomBooking() {
    // Get booking form
    const bookingForm = document.getElementById('booking-form');
    
    if (bookingForm) {
        // Update price calculation when inputs change
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');
        const guestsInput = document.getElementById('guests');
        const roomPriceElement = document.getElementById('room-price');
        const totalPriceElement = document.getElementById('total-price');
        
        if (checkInInput && checkOutInput && roomPriceElement && totalPriceElement) {
            // Function to update price
            function updateTotalPrice() {
                if (checkInInput.value && checkOutInput.value) {
                    // Calculate number of nights
                    const checkIn = new Date(checkInInput.value);
                    const checkOut = new Date(checkOutInput.value);
                    const nights = Math.floor((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                    
                    if (nights > 0) {
                        // Get room price from data attribute
                        const roomPrice = parseFloat(roomPriceElement.getAttribute('data-price'));
                        const totalPrice = roomPrice * nights;
                        
                        // Update displayed total price
                        totalPriceElement.textContent = totalPrice.toFixed(2);
                        
                        // Update hidden total price input
                        const totalPriceInput = document.getElementById('total_price');
                        if (totalPriceInput) {
                            totalPriceInput.value = totalPrice.toFixed(2);
                        }
                    }
                }
            }
            
            // Add event listeners
            checkInInput.addEventListener('change', updateTotalPrice);
            checkOutInput.addEventListener('change', updateTotalPrice);
        }
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    // Get all forms with validation
    const forms = document.querySelectorAll('form.validate');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    // Add error class
                    field.classList.add('input-error');
                    
                    // Create error message if it doesn't exist
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    // Remove error class and message
                    field.classList.remove('input-error');
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.remove();
                    }
                }
            });
            
            // Check email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value.trim() && !isValidEmail(field.value)) {
                    isValid = false;
                    // Add error class
                    field.classList.add('input-error');
                    
                    // Create error message if it doesn't exist
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Please enter a valid email address';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    } else {
                        errorMsg.textContent = 'Please enter a valid email address';
                    }
                }
            });
            
            // Check password confirmation
            const passwordField = form.querySelector('input[name="password"]');
            const passwordConfirmField = form.querySelector('input[name="password_confirm"]');
            
            if (passwordField && passwordConfirmField && 
                passwordField.value && passwordConfirmField.value && 
                passwordField.value !== passwordConfirmField.value) {
                
                isValid = false;
                // Add error class
                passwordConfirmField.classList.add('input-error');
                
                // Create error message if it doesn't exist
                let errorMsg = passwordConfirmField.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                    errorMsg = document.createElement('span');
                    errorMsg.className = 'error-message';
                    errorMsg.textContent = 'Passwords do not match';
                    passwordConfirmField.parentNode.insertBefore(errorMsg, passwordConfirmField.nextSibling);
                } else {
                    errorMsg.textContent = 'Passwords do not match';
                }
            }
            
            // Prevent form submission if validation fails
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Validate email format
 * 
 * @param {string} email Email address to validate
 * @return {boolean} True if email is valid
 */
function isValidEmail(email) {
    // Simple email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Format currency
 * 
 * @param {number} amount Amount to format
 * @param {string} currencyCode Currency code
 * @return {string} Formatted currency string
 */
function formatCurrency(amount, currencyCode = 'USD') {
    // Get currency symbol based on code
    const currencySymbols = {
        'USD': '$',
        'VND': '₫'
    };
    
    const symbol = currencySymbols[currencyCode] || '';
    
    // Format based on currency
    if (currencyCode === 'VND') {
        // VND has no decimal places
        return symbol + amount.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    } else {
        // Standard 2 decimal places for most currencies
        return symbol + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
}