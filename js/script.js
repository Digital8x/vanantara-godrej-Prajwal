document.addEventListener('DOMContentLoaded', () => {
    // 1. Anti-Copy Security
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.addEventListener('dragstart', event => {
        if (event.target.tagName.toLowerCase() === 'img') event.preventDefault();
    });
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 's' || e.key === 'u' || e.key === 'p')) e.preventDefault();
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) e.preventDefault();
    });

    // 2. Initialize intl-tel-input
    const phoneInputs = [
        document.querySelector("#phone"), 
        document.querySelector("#popup-phone"),
        document.querySelector("#footer-phone")
    ];
    const itiInstances = [];

    phoneInputs.forEach((input, index) => {
        if(input) {
            // Error message element (hidden by default)
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-msg';
            errorMsg.innerText = 'Invalid number length for selected country.';
            errorMsg.style.display = 'none';
            input.parentElement.appendChild(errorMsg);

            const iti = window.intlTelInput(input, {
                initialCountry: "auto",
                geoIpLookup: function(callback) {
                    fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("in"));
                },
                separateDialCode: true,
                preferredCountries: ["in", "ae", "us", "gb"],
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js"
            });
            itiInstances.push(iti);

            input.addEventListener('input', function() { 
                this.value = this.value.replace(/\D/g, ''); 
                if (this.value.length > 0) {
                    if (iti.isValidNumber()) {
                        this.closest('.input-wrap').classList.remove('invalid');
                        errorMsg.style.display = 'none';
                    } else {
                        this.closest('.input-wrap').classList.add('invalid');
                        errorMsg.style.display = 'block';
                    }
                } else {
                    this.closest('.input-wrap').classList.remove('invalid');
                    errorMsg.style.display = 'none';
                }
            });
        }
    });

    // 3. Form Submissions
    const forms = [
        { el: document.getElementById('hero-lead-form'), itiIndex: 0 },
        { el: document.getElementById('popup-lead-form'), itiIndex: 1 },
        { el: document.getElementById('footer-lead-form'), itiIndex: 2 }
    ];

    forms.forEach(formObj => {
        if(formObj.el) {
            formObj.el.addEventListener('submit', function(e) {
                e.preventDefault();
                const iti = itiInstances[formObj.itiIndex];
                if (!iti.isValidNumber()) {
                    const countryData = iti.getSelectedCountryData();
                    alert(`Invalid number for ${countryData.name}. Exactly 10 digits required for India.`);
                    return;
                }

                const formData = new FormData(this);
                formData.set('phone', iti.getNumber());
                if(!formData.get('project')) formData.set('project', 'Godrej Vanantara General');

                const btn = this.querySelector('.btn-submit');
                const oldText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
                btn.disabled = true;

                fetch('backend/submit_lead.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        if (formData.get('project') === 'Unlock Walkthrough Video') {
                            window.dispatchEvent(new Event('walkthroughUnlocked'));
                            btn.innerHTML = '<i class="fa-solid fa-check"></i> Unlocked';
                        } else {
                            window.location.href = 'thankyou.html';
                        }
                    }
                    else { alert(data.message); btn.innerHTML = oldText; btn.disabled = false; }
                }).catch(() => { alert('Server error.'); btn.innerHTML = oldText; btn.disabled = false; });
            });
        }
    });

    // 4. Scroll Reveal & Header
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) header.classList.add('scrolled');
        else header.classList.remove('scrolled');

        const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
        reveals.forEach(r => {
            const rt = r.getBoundingClientRect().top;
            if (rt < window.innerHeight - 80) r.classList.add('active');
        });
    });
    window.dispatchEvent(new Event('scroll'));

    // 5. Mobile Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const desktopNav = document.querySelector('.desktop-nav');
    
    if(menuToggle && desktopNav) {
        menuToggle.addEventListener('click', () => {
            desktopNav.classList.toggle('active');
            menuToggle.querySelector('i').classList.toggle('fa-bars');
            menuToggle.querySelector('i').classList.toggle('fa-xmark');
        });

        // Close on link click
        desktopNav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                desktopNav.classList.remove('active');
                menuToggle.querySelector('i').classList.add('fa-bars');
                menuToggle.querySelector('i').classList.remove('fa-xmark');
            });
        });
    }
});

// Modal Logic
function openPopup(ctx) {
    const modal = document.getElementById("leadModal");
    const modalTitle = document.getElementById("modalTitle");
    const popupProject = document.getElementById("popup-project");
    if(modal) { modal.style.display = "block"; modalTitle.innerText = ctx; popupProject.value = ctx; }
}
function closePopup() { 
    const modal = document.getElementById("leadModal");
    if(modal) modal.style.display = "none"; 
}

// Lightbox Logic
function openLightbox(src) {
    const lb = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightbox-img');
    if(lb && lbImg) { lb.style.display = "block"; lbImg.src = src; }
}
function closeLightbox() { const lb = document.getElementById('lightbox'); if(lb) lb.style.display = "none"; }

window.onclick = e => { 
    if (e.target == modal) closePopup(); 
    if (e.target == document.getElementById('lightbox')) closeLightbox(); 
}
