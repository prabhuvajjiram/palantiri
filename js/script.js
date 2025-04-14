// Main JavaScript for Palantiri Website

$(document).ready(function() {
    // Initialize components
    initNavbar();
    initCarousel();
    
    // Form validation if forms exist
    if ($('form').length) {
        $('form').each(function() {
            initFormValidation($(this));
        });
    }
});

// Navbar scroll effect
function initNavbar() {
    const $navbar = $('.navbar');
    if (!$navbar.length) return;

    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 50) {
            $navbar.addClass('scrolled');
        } else {
            $navbar.removeClass('scrolled');
        }
    });

    // Initial check
    $(window).trigger('scroll');
}

// Initialize carousel with jQuery
function initCarousel() {
    $('#clientSpotlightCarousel').carousel({
        interval: 5000,
        pause: 'hover'
    });
}

// Form validation
function initFormValidation($form) {
    if (!$form || !$form.length) return;

    $form.on('submit', function(event) {
        event.preventDefault();
        
        const isValid = validateForm($(this));
        if (isValid) {
            handleFormSubmit($(this));
        }
    });
}

// Validate form fields
function validateForm($form) {
    let isValid = true;
    const $inputs = $form.find('input, textarea');
    
    $inputs.each(function() {
        const $input = $(this);
        if ($input.prop('required') && !$input.val().trim()) {
            isValid = false;
            $input.addClass('is-invalid');
        } else {
            $input.removeClass('is-invalid');
        }
    });
    
    return isValid;
}

// Handle form submission
function handleFormSubmit($form) {
    const $submitButton = $form.find('button[type="submit"]');
    
    if ($submitButton.length) {
        $submitButton.prop('disabled', true)
                    .html('Sending...');
    }

    // Simulate form submission (replace with actual AJAX call)
    setTimeout(() => {
        $form[0].reset();
        if ($submitButton.length) {
            $submitButton.prop('disabled', false)
                        .html('Send Message');
        }
        alert('Thank you for your message. We will get back to you soon!');
    }, 1000);
}
