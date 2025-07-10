// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Example: Simple console log to confirm script is loaded
    console.log('main.js loaded successfully!');

    // Add any global interactive JavaScript here
    // For instance, if you had a mobile navigation toggle:
    // const mobileMenuButton = document.getElementById('mobile-menu-button');
    // const sidebar = document.getElementById('sidebar');
    // if (mobileMenuButton && sidebar) {
    //     mobileMenuButton.addEventListener('click', function() {
    //         sidebar.classList.toggle('hidden'); // Or toggle a 'show' class
    //     });
    // }

    // Example: Fade out success/error messages after a few seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 1s ease-out';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 1000); // Remove after transition
        }, 5000); // Fade out after 5 seconds
    });
});
