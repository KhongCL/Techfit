document.addEventListener('DOMContentLoaded', function () {

    const navItems = document.querySelectorAll('.nav-list > li > a');
    const dropdowns = document.querySelectorAll('.dropdown');
    const hamburger = document.getElementById('hamburger');
    const navList = document.querySelector('.nav-list');

    navItems.forEach(item => {
        item.addEventListener('click', function (event) {
            const parent = item.parentElement;
            const dropdown = parent.querySelector('.dropdown');

            if (dropdown) {
                event.preventDefault();
                parent.classList.toggle('active');
                dropdown.classList.toggle('active');
                closeAllDropdowns(parent);
            }
        });

        item.addEventListener('touchstart', function (event) {
            event.preventDefault();
            item.click();
        });
    });

    const closeAllDropdowns = (exceptParent = null) => {
        dropdowns.forEach(dropdown => {
            const parent = dropdown.parentElement;
            if (parent !== exceptParent) {
                dropdown.classList.remove('active');
                parent.classList.remove('active');
            }
        });
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.nav-list')) {
            closeAllDropdowns();
        }
    });

    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        navList.classList.toggle('active');
        closeAllDropdowns();
    });

    window.addEventListener('resize', function () {
        closeAllDropdowns();
        hamburger.classList.remove('active');
        navList.classList.remove('active');
    });

    const profileLink = document.getElementById('profile-link');
    const logoutPopup = document.getElementById('logout-popup');
    const logoutConfirmButton = document.getElementById('logout-confirm-button');
    const logoutCancelButton = document.getElementById('logout-cancel-button');
    const profileDropdown = document.getElementById('profile-dropdown');


    const logoutDropdownLink = profileDropdown.querySelector('li a[href="#"]');

    if (logoutDropdownLink) {
        logoutDropdownLink.addEventListener('click', function (event) {
            event.preventDefault();
            openPopup('logout-popup');
            profileDropdown.classList.remove('active');
        });
    }

    logoutConfirmButton.addEventListener('click', function () {
        logoutUser();
    });

    logoutCancelButton.addEventListener('click', function () {
        closePopup('logout-popup');
    });

    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', function () {
            const faqItem = item.closest('.faq-item');
            const arrow = faqItem.querySelector('.dropdown-arrow');

            faqItem.classList.toggle('open');

            if (faqItem.classList.contains('open')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        });
    });

    function createFaqElement(faq) {
        const faqItem = document.createElement('div');
        faqItem.classList.add('faq-item');
        faqItem.innerHTML = `
            <button class="faq-question" aria-expanded="false">
                <span>${faq.question}</span>
                <span class="dropdown-arrow">&#9660;</span>
            </button>
            <div class="faq-answer" aria-hidden="true">
                <p>${faq.answer}</p>
            </div>
        `;
        return faqItem;
    }

    function createLinkElement(link) {
        const linkElement = document.createElement('a');
        linkElement.href = link.link;
        linkElement.textContent = link.title || link.link;
        linkElement.target = "_blank";
        return linkElement;
    }

    function openPopup(popupId) {
        document.getElementById(popupId).style.display = 'block';
    }

    function closePopup(popupId) {
        document.getElementById(popupId).style.display = 'none';
    }

    function logoutUser() {
        window.location.href = '/Techfit';
    }
});