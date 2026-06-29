// Reusable validation functions
const showError = (input, message) => {
    const formGroup = input.parentElement;
    let errorDisplay = formGroup.querySelector('.form-error');
    if (!errorDisplay) {
        errorDisplay = document.createElement('div');
        errorDisplay.className = 'form-error';
        formGroup.appendChild(errorDisplay);
    }
    errorDisplay.innerText = message;
    errorDisplay.style.display = 'block';
    input.style.borderColor = '#ff3366';
};

const clearError = (input) => {
    const formGroup = input.parentElement;
    const errorDisplay = formGroup.querySelector('.form-error');
    if (errorDisplay) {
        errorDisplay.style.display = 'none';
    }
    input.style.borderColor = 'rgba(255, 255, 255, 0.1)';
};

const isValidEmail = (email) => {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
};

document.addEventListener('DOMContentLoaded', () => {
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let isValid = true;
            
            const email = document.getElementById('email');
            const password = document.getElementById('password');

            if (!email.value.trim() || !isValidEmail(email.value)) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError(email);
            }

            if (password.value.trim().length < 6) {
                showError(password, 'Password must be at least 6 characters');
                isValid = false;
            } else {
                clearError(password);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const passwordInput = document.getElementById('password');
        const strengthDisplay = document.getElementById('passwordStrength');
        
        if (passwordInput && strengthDisplay) {
            passwordInput.addEventListener('input', () => {
                const val = passwordInput.value;
                if (val.length === 0) strengthDisplay.innerText = '';
                else if (val.length < 6) strengthDisplay.innerText = 'Weak';
                else if (val.length < 10) strengthDisplay.innerText = 'Medium';
                else strengthDisplay.innerText = 'Strong';
            });
        }

        registerForm.addEventListener('submit', (e) => {
            let isValid = true;
            
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');

            if (username.value.trim().length < 3) {
                showError(username, 'Username must be at least 3 characters');
                isValid = false;
            } else clearError(username);

            if (!isValidEmail(email.value)) {
                showError(email, 'Please enter a valid email');
                isValid = false;
            } else clearError(email);

            if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters');
                isValid = false;
            } else clearError(password);

            if (password.value !== confirmPassword.value || confirmPassword.value === '') {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
            } else clearError(confirmPassword);

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            let isValid = true;
            
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const message = document.getElementById('message');

            if (name.value.trim().length < 2) {
                showError(name, 'Please enter your name');
                isValid = false;
            } else clearError(name);

            if (!isValidEmail(email.value)) {
                showError(email, 'Please enter a valid email');
                isValid = false;
            } else clearError(email);

            if (message.value.trim().length < 10) {
                showError(message, 'Message must be at least 10 characters');
                isValid = false;
            } else clearError(message);

            if (isValid) {
                alert('Message sent successfully! (Simulation)');
                contactForm.reset();
            }
        });
    }
});
