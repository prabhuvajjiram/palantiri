// Main JavaScript for Palantiri Website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initNavbar();
    initAnimations();
    
    // Form validation if forms exist
    if (document.querySelector('form')) {
        initFormValidation();
    }
});

// Navbar scroll effect
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
}

// Scroll animations
function initAnimations() {
    // Add fade-in class to elements when they come into view
    const fadeElements = document.querySelectorAll('.service-card, .why-us-item, .client-info, .section-title');
    
    if (fadeElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        
        fadeElements.forEach(element => {
            observer.observe(element);
        });
    }
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
}

// Portfolio modal functionality (if needed)
function openPortfolioModal(id) {
    const modal = document.getElementById('portfolioModal' + id);
    if (modal) {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

// Contact form submission
function handleContactSubmit(event) {
    event.preventDefault();
    const form = event.target;
    
    // Get form data
    const name = form.querySelector('[name="name"]').value;
    const email = form.querySelector('[name="email"]').value;
    const message = form.querySelector('[name="message"]').value;
    
    // Normally you would send this to a server
    // For now, we'll just show a success message
    const formContainer = form.parentElement;
    formContainer.innerHTML = `
        <div class="alert alert-success">
            <h4>Thank you for your message, ${name}!</h4>
            <p>We've received your inquiry and will get back to you soon at ${email}.</p>
        </div>
    `;
}

// Career form submission
function handleCareerSubmit(event) {
    event.preventDefault();
    const form = event.target;
    
    // Get form data
    const name = form.querySelector('[name="name"]').value;
    const email = form.querySelector('[name="email"]').value;
    
    // Normally you would send this to a server
    // For now, we'll just show a success message
    const formContainer = form.parentElement;
    formContainer.innerHTML = `
        <div class="alert alert-success">
            <h4>Thank you for your interest, ${name}!</h4>
            <p>We've received your application and will review it shortly. We'll contact you at ${email} if there's a potential fit.</p>
        </div>
    `;
}

// Cookie consent - similar to angelgranites.com
function initCookieConsent() {
    // Check if cookie consent has been given
    if (!localStorage.getItem('cookieConsent')) {
        // Create cookie consent banner
        const consentBanner = document.createElement('div');
        consentBanner.className = 'cookie-consent';
        consentBanner.innerHTML = `
            <div class="container">
                <p>We use cookies to improve your experience on our website. By continuing to browse, you agree to our use of cookies.</p>
                <div class="cookie-buttons">
                    <button class="btn btn-light me-2" id="declineCookies">Decline</button>
                    <button class="btn btn-primary" id="acceptCookies">Accept</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(consentBanner);
        
        // Handle accept button
        document.getElementById('acceptCookies').addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'true');
            consentBanner.remove();
        });

        // Handle decline button
        document.getElementById('declineCookies').addEventListener('click', function() {
            localStorage.setItem('cookieConsent', 'false');
            consentBanner.remove();
            // Disable any analytics or non-essential cookies here
        });
    }
}

// Call cookie consent init
document.addEventListener('DOMContentLoaded', function() {
    initCookieConsent();
});
