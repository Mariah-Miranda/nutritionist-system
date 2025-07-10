// assets/js/form-validation.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('form-validation.js loaded.');

    // Generic form validation example
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('[required]');

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444'; // Highlight empty required fields
                    // You could also add a specific error message span next to the input
                    let errorMessage = input.nextElementSibling;
                    if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                        errorMessage = document.createElement('p');
                        errorMessage.classList.add('error-message');
                        input.parentNode.insertBefore(errorMessage, input.nextSibling);
                    }
                    errorMessage.textContent = 'This field is required.';
                } else {
                    input.style.borderColor = '#d1d5db'; // Reset border color
                    const errorMessage = input.nextElementSibling;
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.remove(); // Remove error message if field is valid
                    }
                }
            });

            // Example for email validation
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput && emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
                isValid = false;
                emailInput.style.borderColor = '#ef4444';
                let errorMessage = emailInput.nextElementSibling;
                if (!errorMessage || !errorMessage.classList.contains('error-message')) {
                    errorMessage = document.createElement('p');
                    errorMessage.classList.add('error-message');
                    emailInput.parentNode.insertBefore(errorMessage, emailInput.nextSibling);
                }
                errorMessage.textContent = 'Please enter a valid email address.';
            }

            if (!isValid) {
                event.preventDefault(); // Stop form submission if validation fails
                alert('Please fill in all required fields and correct any errors.'); // Use a custom modal in production!
            }
        });

        // Clear error messages on input focus
        form.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#d1d5db';
                const errorMessage = this.nextElementSibling;
                if (errorMessage && errorMessage.classList.contains('error-message')) {
                    errorMessage.remove();
                }
            });
        });
    });
});
