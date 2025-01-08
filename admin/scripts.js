document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.querySelectorAll('.nav-list > li > a'); // Top-level links
    const dropdowns = document.querySelectorAll('.dropdown'); // Dropdown menus
    const hamburger = document.getElementById('hamburger');
    const navList = document.querySelector('.nav-list');

    // Toggle dropdown visibility
    navItems.forEach(item => {
        item.addEventListener('click', function (event) {
            const parent = item.parentElement; // The parent <li>
            const dropdown = parent.querySelector('.dropdown');

            // Prevent default link behavior for links with dropdowns
            if (dropdown) {
                event.preventDefault();

                // Toggle the current dropdown
                const isActive = dropdown.classList.contains('active');
                closeAllDropdowns(); // Close all open dropdowns
                if (!isActive) {
                    dropdown.classList.add('active');
                    parent.classList.add('active');
                }
            }
        });

        // Optional: Add `touchstart` for better mobile compatibility
        item.addEventListener('touchstart', function (event) {
            event.preventDefault(); // Prevent ghost clicks
            item.click(); // Trigger the click event manually
        });
    });

    // Close all dropdowns
    const closeAllDropdowns = () => {
        dropdowns.forEach(dd => dd.classList.remove('active'));
        navItems.forEach(item => item.parentElement.classList.remove('active'));
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.nav-list')) {
            closeAllDropdowns();
        }
    });

    // Handle hamburger menu toggle
    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        navList.classList.toggle('active');
    });

    // Reset state on resize (still useful for removing active states)
    window.addEventListener('resize', function () {
        closeAllDropdowns();
        hamburger.classList.remove('active');
        navList.classList.remove('active');
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


