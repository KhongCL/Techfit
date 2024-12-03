document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-list > li');

    navItems.forEach(item => {
        item.addEventListener('click', function(event) {
            // Close any open dropdowns
            navItems.forEach(navItem => {
                if (navItem !== item) {
                    navItem.classList.remove('active');
                }
            });

            // Toggle the clicked dropdown
            item.classList.toggle('active');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.nav-list')) {
            navItems.forEach(item => {
                item.classList.remove('active');
            });
        }
    });
});