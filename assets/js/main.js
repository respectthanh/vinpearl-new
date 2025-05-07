/**
 * Vinpearl Resort Nha Trang - Main JavaScript
 * Handles interactivity and dynamic functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // Room image gallery
    initializeGallery();
    
    // Booking date validation
    validateBookingDates();
    
    // Language switcher
    setupLanguageSwitcher();
    
    // Sticky header on scroll
    handleStickyHeader();
    
    // Initialize any sliders/carousels
    initializeSliders();
    
    // Initialize back to top button
    initBackToTop();
    
    // Set minimum dates for booking form
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');
    
    if (checkInDate && checkOutDate) {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        checkInDate.min = formatDate(today);
        checkOutDate.min = formatDate(tomorrow);
        
        checkInDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const nextDay = new Date(selectedDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            checkOutDate.min = formatDate(nextDay);
            
            // If checkout date is before new minimum, update it
            if (checkOutDate.value && new Date(checkOutDate.value) <= selectedDate) {
                checkOutDate.value = formatDate(nextDay);
            }
        });
    }
});

/**
 * Initialize image galleries for room details
 */
function initializeGallery() {
    const mainImage = document.querySelector('.room-main-image img');
    const thumbnails = document.querySelectorAll('.room-thumbnail');
    
    if (!mainImage || thumbnails.length === 0) return;
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Update main image
            mainImage.src = this.dataset.fullsize;
            
            // Update active state
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Validate booking date inputs
 */
function validateBookingDates() {
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    
    if (!checkInInput || !checkOutInput) return;
    
    // Set minimum date as today
    const today = new Date();
    const todayFormatted = today.toISOString().split('T')[0];
    checkInInput.min = todayFormatted;
    
    // Update checkout min date when checkin changes
    checkInInput.addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        
        // Set checkout min date to day after checkin
        checkInDate.setDate(checkInDate.getDate() + 1);
        const minCheckoutDate = checkInDate.toISOString().split('T')[0];
        checkOutInput.min = minCheckoutDate;
        
        // If current checkout date is before new min, update it
        if (checkOutInput.value && new Date(checkOutInput.value) <= new Date(this.value)) {
            checkOutInput.value = minCheckoutDate;
        }
    });
}

/**
 * Setup language switcher functionality
 */
function setupLanguageSwitcher() {
    const langLinks = document.querySelectorAll('.language-selector a');
    
    langLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.classList.contains('active')) {
                e.preventDefault();
                return;
            }
            
            // Store selected language in session storage
            const lang = this.getAttribute('data-lang');
            sessionStorage.setItem('selectedLanguage', lang);
        });
    });
}

/**
 * Handle sticky header on scroll
 */
function handleStickyHeader() {
    const header = document.querySelector('header');
    const headerHeight = header ? header.offsetHeight : 0;
    
    if (!header) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('sticky');
            document.body.style.paddingTop = headerHeight + 'px';
        } else {
            header.classList.remove('sticky');
            document.body.style.paddingTop = '0';
        }
    });
}

/**
 * Initialize promotional sliders/carousels
 */
function initializeSliders() {
    // If using external libraries, initialize them here
    // This is a placeholder for future integration
}

/**
 * Filter functionality for rooms, packages, etc.
 * @param {string} category - Category to filter by
 * @param {string} targetContainer - Container ID to filter within
 */
function filterItems(category, targetContainer) {
    const container = document.getElementById(targetContainer);
    if (!container) return;
    
    const items = container.querySelectorAll('.filterable-item');
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    // Update active button
    filterBtns.forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Show/hide items based on category
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Display error message in form
 * @param {HTMLElement} input - Input element with error
 * @param {string} message - Error message to display
 */
function showFormError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup.querySelector('.form-error');
    
    input.classList.add('is-invalid');
    
    if (errorElement) {
        errorElement.textContent = message;
    } else {
        const error = document.createElement('div');
        error.className = 'form-error text-danger';
        error.textContent = message;
        formGroup.appendChild(error);
    }
}

/**
 * Clear form error messages
 * @param {HTMLElement} form - Form element to clear errors in
 */
function clearFormErrors(form) {
    const errorElements = form.querySelectorAll('.form-error');
    const invalidInputs = form.querySelectorAll('.is-invalid');
    
    errorElements.forEach(el => el.textContent = '');
    invalidInputs.forEach(input => input.classList.remove('is-invalid'));
}

/**
 * Calculate total price for booking
 * @param {string} type - 'room', 'package', or 'tour'
 */
function calculateTotalPrice(type) {
    let basePrice, totalPrice, nights, guests;
    
    if (type === 'room') {
        basePrice = parseFloat(document.getElementById('room_price').value);
        nights = calculateNights(
            document.getElementById('check_in_date').value,
            document.getElementById('check_out_date').value
        );
        guests = parseInt(document.getElementById('guests').value);
        
        // Simple calculation (can be made more complex with additional fees)
        totalPrice = basePrice * nights;
        
    } else if (type === 'package') {
        basePrice = parseFloat(document.getElementById('package_price').value);
        guests = parseInt(document.getElementById('guests').value);
        
        // Packages typically have a fixed price per person
        totalPrice = basePrice * guests;
        
    } else if (type === 'tour') {
        basePrice = parseFloat(document.getElementById('tour_price').value);
        guests = parseInt(document.getElementById('guests').value);
        
        // Tours are priced per person
        totalPrice = basePrice * guests;
    }
    
    // Update total price display
    const totalPriceElement = document.getElementById('total_price');
    if (totalPriceElement) {
        totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
    }
    
    return totalPrice;
}

/**
 * Calculate number of nights between two dates
 * @param {string} checkIn - Check-in date string
 * @param {string} checkOut - Check-out date string
 * @return {number} - Number of nights
 */
function calculateNights(checkIn, checkOut) {
    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);
    const timeDiff = checkOutDate.getTime() - checkInDate.getTime();
    return Math.ceil(timeDiff / (1000 * 3600 * 24));
}

/**
 * Initialize Back to Top Button
 */
function initBackToTop() {
    const backToTopBtn = document.querySelector('.back-to-top');
    
    if (!backToTopBtn) return;
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('active');
        } else {
            backToTopBtn.classList.remove('active');
        }
    });
    
    backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
} 