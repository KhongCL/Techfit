document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-list > li');
    const hamburger = document.getElementById('hamburger');
    const navList = document.querySelector('.nav-list');
    const assessmentLink = document.querySelector('li > a[href="#"]'); // The "Assessment" link
    
    // Handle dropdown menu
    navItems.forEach(item => {
        item.addEventListener('click', function(event) {
            // Prevent click from closing the dropdown
            event.stopPropagation();

            // Close any open dropdowns except the current one
            navItems.forEach(navItem => {
                if (navItem !== item) {
                    navItem.classList.remove('active');
                }
            });

            // Toggle the clicked dropdown
            item.classList.toggle('active');
        });
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
        navList.classList.toggle('active');
    });
});
