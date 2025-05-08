document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modal = document.getElementById('cancelBookingModal');
    const cancelBtns = document.querySelectorAll('.cancel-booking');
    const closeBtn = document.querySelector('.close-modal');
    const confirmCancelBtn = document.getElementById('confirmCancel');
    let currentBookingData = null;

    // Open modal
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentBookingData = {
                type: this.dataset.type,
                id: this.dataset.id
            };
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    // Close modal
    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        currentBookingData = null;
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    // Handle booking cancellation
    confirmCancelBtn.addEventListener('click', async function() {
        if (!currentBookingData) return;

        try {
            const response = await fetch('api/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: currentBookingData.type,
                    id: currentBookingData.id
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Show success message
                const successMessage = document.createElement('div');
                successMessage.className = 'alert alert-success';
                successMessage.textContent = data.message;
                document.querySelector('.page-header').insertAdjacentElement('afterend', successMessage);

                // Update booking status in UI
                const bookingItem = document.querySelector(`[data-id="${currentBookingData.id}"]`).closest('.booking-item');
                bookingItem.querySelector('.status-badge').textContent = 'Cancelled';
                bookingItem.querySelector('.status-badge').className = 'status-badge cancelled';
                bookingItem.querySelector('.cancel-booking').remove();

                // Auto remove success message after 3 seconds
                setTimeout(() => successMessage.remove(), 3000);

                // Close modal
                closeModal();

                // Refresh page after a short delay
                setTimeout(() => window.location.reload(), 3000);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-error';
            errorMessage.textContent = error.message || 'An error occurred while cancelling the booking';
            modal.querySelector('.modal-content').insertAdjacentElement('afterbegin', errorMessage);
            
            setTimeout(() => errorMessage.remove(), 3000);
        }
    });

    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const langParam = new URLSearchParams(window.location.search).get('lang') || '';
            
            let url = 'bookings.php?';
            if (statusFilter) url += `status=${statusFilter}&`;
            if (typeFilter) url += `type=${typeFilter}&`;
            if (langParam) url += `lang=${langParam}`;
            
            window.location.href = url.replace(/&$/, '');
        });
    });

    // Animate booking items on scroll
    const bookingItems = document.querySelectorAll('.booking-item');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    bookingItems.forEach(item => observer.observe(item));
});
