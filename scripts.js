document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-list > li');
    const hamburger = document.getElementById('hamburger');
    const navList = document.querySelector('.nav-list');
    const assessmentLink = document.querySelector('li > a[href="#"]'); // The "Assessment" link

    // Handle dropdown menu
    navItems.forEach(item => {
        // Function to toggle active state for mobile dropdowns
        const handleToggle = (event) => {
            // Prevent click from closing the dropdown if on mobile
            event.stopPropagation();

            // Close any open dropdowns except the current one
            navItems.forEach(navItem => {
                if (navItem !== item) {
                    navItem.classList.remove('active');
                }
            });

            // Toggle the clicked dropdown
            item.classList.toggle('active');
        };

        // Add click event listener for both desktop and mobile
        item.addEventListener('click', handleToggle);
        item.addEventListener('touchend', handleToggle); // Handle touch events for mobile
    });

    // Handle Assessment dropdown behavior for hover and click
    assessmentLink.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default behavior of the link
        const assessmentDropdown = event.target.nextElementSibling;
        assessmentDropdown.classList.toggle('active'); // Toggle visibility of the dropdown on click
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.nav-list')) {
            navItems.forEach(item => {
                item.classList.remove('active');
            });
        }
    });

    // Handle hamburger menu toggle
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navList.classList.toggle('active');
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth > 900) {
            hamburger.classList.remove("active");
            navList.classList.remove("active");
        }
    });
});

// FAQ dropdown functionality (unchanged)
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
