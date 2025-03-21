document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('form[action="login.php"]');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

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

    function validateUsername() {
        const username = usernameInput.value.trim();
        clearError(usernameInput);

        if (username === '') {
            showError(usernameInput, 'Uživatelské jméno je povinné.');
            return false;
        }
        else if (username.length < 3) {
            showError(usernameInput, 'Uživatelské jméno musí mít alespoň 3 znaky.');
            return false;
        }
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value.trim();
        clearError(passwordInput);

        if (password === '') {
            showError(passwordInput, 'Heslo je povinné.');
            return false;
        }
        if(password.length < 6) {
            showError(passwordInput, 'Heslo musí mít minimálně 6 znaků.');
            return false;}
        if(password.length > 30) {
                showError(passwordInput, 'Heslo musí mít maximálně 30 znaků.');
                return false;}
        return true;}

    loginForm.addEventListener('submit', (event) => {
        let isValid = true;

        if (!validateUsername()) isValid = false;
        if (!validatePassword()) isValid = false;

        if (!isValid) {
            event.preventDefault();
        }
    });

    usernameInput.addEventListener('input', validateUsername);
    passwordInput.addEventListener('input', validatePassword);
});