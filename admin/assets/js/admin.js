/**
 * Vinpearl Resort Nha Trang - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminContent = document.querySelector('.admin-content');
    
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            adminSidebar.classList.toggle('collapsed');
            adminContent.classList.toggle('expanded');
        });
    }
    
    // Confirmation dialogs for delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Handle status changes
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('admin-search');
    const searchForm = document.getElementById('search-form');
    
    if (searchInput && searchForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
    
    // Sort table columns
    const sortHeaders = document.querySelectorAll('th[data-sort]');
    sortHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sort = this.getAttribute('data-sort');
            const currentSort = new URLSearchParams(window.location.search).get('sort');
            const currentOrder = new URLSearchParams(window.location.search).get('order');
            
            let order = 'asc';
            if (currentSort === sort && currentOrder === 'asc') {
                order = 'desc';
            }
            
            const url = new URL(window.location);
            url.searchParams.set('sort', sort);
            url.searchParams.set('order', order);
            window.location = url.toString();
        });
    });
    
    // Image Preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = this.nextElementSibling;
            if (preview && preview.classList.contains('image-preview')) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });
    
    // Date range picker
    const dateRangeInputs = document.querySelectorAll('.date-range');
    dateRangeInputs.forEach(input => {
        if (typeof flatpickr !== 'undefined') {
            flatpickr(input, {
                mode: "range",
                dateFormat: "Y-m-d"
            });
        }
    });
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
});

function initializeCharts() {
    // Revenue Chart
    const revenueChart = document.getElementById('revenue-chart');
    if (revenueChart) {
        const labels = revenueChart.getAttribute('data-labels').split(',');
        const roomData = revenueChart.getAttribute('data-room').split(',').map(Number);
        const packageData = revenueChart.getAttribute('data-package').split(',').map(Number);
        const tourData = revenueChart.getAttribute('data-tour').split(',').map(Number);
        
        new Chart(revenueChart, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Room Bookings',
                        data: roomData,
                        backgroundColor: 'rgba(13, 110, 110, 0.8)'
                    },
                    {
                        label: 'Package Bookings',
                        data: packageData,
                        backgroundColor: 'rgba(74, 165, 155, 0.8)'
                    },
                    {
                        label: 'Tour Bookings',
                        data: tourData,
                        backgroundColor: 'rgba(0, 77, 64, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue ($)'
                        }
                    }
                }
            }
        });
    }
    
    // Occupancy Chart
    const occupancyChart = document.getElementById('occupancy-chart');
    if (occupancyChart) {
        const labels = occupancyChart.getAttribute('data-labels').split(',');
        const data = occupancyChart.getAttribute('data-values').split(',').map(Number);
        
        new Chart(occupancyChart, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: data,
                    borderColor: 'rgba(13, 110, 110, 1)',
                    backgroundColor: 'rgba(13, 110, 110, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Occupancy Rate (%)'
                        }
                    }
                }
            }
        });
    }
    
    // Booking Types Chart
    const bookingTypesChart = document.getElementById('booking-types-chart');
    if (bookingTypesChart) {
        const labels = ['Rooms', 'Packages', 'Tours'];
        const data = [
            parseInt(bookingTypesChart.getAttribute('data-rooms')), 
            parseInt(bookingTypesChart.getAttribute('data-packages')), 
            parseInt(bookingTypesChart.getAttribute('data-tours'))
        ];
        
        new Chart(bookingTypesChart, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(13, 110, 110, 0.8)',
                        'rgba(74, 165, 155, 0.8)',
                        'rgba(0, 77, 64, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
}
