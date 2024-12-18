document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.nav-list > li > a');
    const dropdowns = document.querySelectorAll('.dropdown');
    const hamburger = document.getElementById('hamburger');
    const navList = document.querySelector('.nav-list');

    // Helper function to check if we're in the responsive state
    const isMobile = () => window.innerWidth < 768;

    // Handle dropdown toggle for mobile
    navItems.forEach(item => {
        item.addEventListener('click', function (event) {
            // If not in responsive mode, do nothing
            if (!isMobile()) return;

            const parent = item.parentElement; // The parent <li>
            const dropdown = parent.querySelector('.dropdown');

            // Prevent default navigation behavior for links with dropdowns
            if (dropdown) {
                event.preventDefault();

                // Close all other dropdowns
                dropdowns.forEach(dd => {
                    if (dd !== dropdown) {
                        dd.classList.remove('active');
                        dd.parentElement.classList.remove('active');
                    }
                });

                // Toggle the current dropdown
                dropdown.classList.toggle('active');
                parent.classList.toggle('active');
            }
        });
    });

    // Handle hamburger menu toggle
    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        navList.classList.toggle('active');
    });

    // Ensure nav resets on window resize
    window.addEventListener('resize', function () {
        if (!isMobile()) {
            // Reset all dropdowns and navigation state
            hamburger.classList.remove('active');
            navList.classList.remove('active');
            dropdowns.forEach(dd => dd.classList.remove('active'));
            navItems.forEach(item => item.parentElement.classList.remove('active'));
        }
    });

    // Close dropdowns when clicking outside (mobile only)
    document.addEventListener('click', function (event) {
        if (isMobile() && !event.target.closest('.nav-list')) {
            dropdowns.forEach(dd => dd.classList.remove('active'));
            navItems.forEach(item => item.parentElement.classList.remove('active'));
        }
    });
});

// FAQ dropdown functionality
document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', () => {
        const faqItem = item.closest('.faq-item'); // Get the parent .faq-item
        const arrow = faqItem.querySelector('.dropdown-arrow'); // Get the arrow

        // Toggle the 'open' class on the .faq-item to show/hide the answer
        faqItem.classList.toggle('open');

        // Rotate the arrow based on whether the item is open
        if (faqItem.classList.contains('open')) {
            arrow.style.transform = 'rotate(180deg)'; // Rotate when open
        } else {
            arrow.style.transform = 'rotate(0deg)'; // Rotate back when closed
        }
    });
});
