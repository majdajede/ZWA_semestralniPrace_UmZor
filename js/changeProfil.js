document.addEventListener('DOMContentLoaded', () => {
    const changeProfilForm = document.querySelector('form[action="changeProfil.php"]');
    if (!changeProfilForm) return;  

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    function showError(input, message) {
        let errorElement = input.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('error')) {
            errorElement = document.createElement('p');
            errorElement.className = 'error';
            input.insertAdjacentElement('afterend', errorElement);
        }
        errorElement.textContent = message;
    }

    function clearError(input) {
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error')) {
            errorElement.textContent = '';
        }
    }

    async function checkAvailability(field, value) {
        try {
            const response = await fetch(`checkAvailability.php?field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`);
            const result = await response.json();
            return result.valid ? true : result.message; 
        } catch (error) {
            console.error(`Chyba při ověřování ${field}:`, error);
            return 'Nepodařilo se ověřit dostupnost.';
        }
    }

    async function validateUsername() {
        if (!usernameInput) return true;

        const username = usernameInput.value.trim();
        clearError(usernameInput);

        const oldUsername = usernameInput.dataset.oldUsername || '';

        if (username === '') {
            showError(usernameInput, 'Uživatelské jméno je povinné.');
            return false;
        } else if (username.length < 3) {
            showError(usernameInput, 'Uživatelské jméno musí mít alespoň 3 znaky.');
            return false;
        } else if (username.length > 16) {
            showError(usernameInput, 'Uživatelské jméno musí mít maximálně 16 znaků.');
            return false;
        }

        if (username === oldUsername) {
            return true; 
        }

        const availability = await checkAvailability('username', username);
        if (availability !== true) {
            showError(usernameInput, availability);
            return false;
        }
        return true;
    }

    async function validateEmail() {
        if (!emailInput) return true;

        const email = emailInput.value.trim();
        clearError(emailInput);

        const oldEmail = emailInput.dataset.oldEmail || '';

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === '') {
            showError(emailInput, 'Email je povinný.');
            return false;
        } else if (!emailRegex.test(email)) {
            showError(emailInput, 'Neplatný formát emailu.');
            return false;
        }

        if (email === oldEmail) {
            return true;
        }

        const availability = await checkAvailability('email', email);
        if (availability !== true) {
            showError(emailInput, availability);
            return false;
        }
        return true;
    }

    function validatePassword() {
        if (!passwordInput) return true;
        clearError(passwordInput);

        const password = passwordInput.value.trim();
        if (password === '') {
            return true;  
        }

        if (password.length < 6) {
            showError(passwordInput, 'Heslo musí mít alespoň 6 znaků.');
            return false;
        } else if (!/[A-Z]/.test(password)) {
            showError(passwordInput, 'Heslo musí obsahovat alespoň jedno velké písmeno.');
            return false;
        } else if (!/[a-z]/.test(password)) {
            showError(passwordInput, 'Heslo musí obsahovat alespoň jedno malé písmeno.');
            return false;
        } else if (!/[0-9]/.test(password)) {
            showError(passwordInput, 'Heslo musí obsahovat alespoň jedno číslo.');
            return false;
        } else if (password.length > 30) {
            showError(passwordInput, 'Heslo nesmí mít více než 30 znaků.');
            return false;
        }
        return true;
    }

    function validateConfirmPassword() {
        if (!confirmPasswordInput || !passwordInput) return true;
        clearError(confirmPasswordInput);

        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();

        if (password === '' && confirmPassword === '') {
            return true;
        }
        if (password !== confirmPassword) {
            showError(confirmPasswordInput, 'Hesla se neshodují.');
            return false;
        }
        return true;
    }

    changeProfilForm.addEventListener('submit', async (event) => {
        let isValid = true;

        if (!await validateUsername()) isValid = false;
        if (!await validateEmail()) isValid = false;
        if (!validatePassword()) isValid = false;
        if (!validateConfirmPassword()) isValid = false;

        if (!isValid) {
            event.preventDefault();
        }
    });

    if (usernameInput) {
        usernameInput.addEventListener('input', validateUsername);
    }
    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', validatePassword);
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    }
});
