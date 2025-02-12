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
                const isActive = dropdown.classList.contains('active');
                closeAllDropdowns();
                if (!isActive) {
                    dropdown.classList.add('active');
                    parent.classList.add('active');
                }
            }
        });

        item.addEventListener('touchstart', function (event) {
            event.preventDefault();
            item.click();
        });
    });

    const closeAllDropdowns = () => {
        dropdowns.forEach(dd => dd.classList.remove('active'));
        navItems.forEach(item => item.parentElement.classList.remove('active'));
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.nav-list')) {
            closeAllDropdowns();
        }
    });

    hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        navList.classList.toggle('active');
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

    profileLink.addEventListener('click', function(event) {
        event.preventDefault();
        profileDropdown.classList.toggle('active');
    });

    const logoutDropdownLink = profileDropdown.querySelector('li a[href="#"]');

    if(logoutDropdownLink) {
        logoutDropdownLink.addEventListener('click', function(event) {
            event.preventDefault();
            openPopup('logout-popup');
            profileDropdown.classList.remove('active');
        });
    }

    logoutConfirmButton.addEventListener('click', function() {
        logoutUser();
    });

    logoutCancelButton.addEventListener('click', function() {
        closePopup('logout-popup');
    });
});


document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', () => {
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

// Preliminary questions
document.addEventListener('DOMContentLoaded', function() {
    // Only run this code if we're on the preliminary page
    const checkbox = document.getElementById('agree');
    if (checkbox) { // Check if checkbox exists before trying to use it
        checkbox.checked = false;

        const startButton = document.getElementById('start-assessment-button');
        if (startButton) {
            startButton.disabled = true;

            checkbox.addEventListener('change', function() {
                startButton.disabled = !this.checked;
            });

            startButton.addEventListener('click', function() {
                window.location.href = 'assessment_question.php';
            });
        }
    }
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