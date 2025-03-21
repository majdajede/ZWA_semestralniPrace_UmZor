document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('changePrispevekForm');
    if (!form) return;

    const artnameInput = document.getElementById('artname');

    function showError(element, message) {
        let errorElement = element.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('error')) {
            errorElement = document.createElement('p');
            errorElement.classList.add('error');
            element.insertAdjacentElement('afterend', errorElement);
        }
        errorElement.textContent = message;
    }

    function clearError(element) {
        const errorElement = element.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error')) {
            errorElement.textContent = '';
        }
    }

    function validateArtname() {
        const artname = artnameInput.value.trim();
        clearError(artnameInput);

        if (artname.length < 3) {
            showError(artnameInput, 'Název díla musí mít alespoň 3 znaky.');
            return false;
        }
        if (artname.length >=16) {
            showError(artnameInput, 'Název díla nesmí přesáhnout 15 znaků.');
            return false;
        }

        return true;
    }

    // validacka pred odeslanim
    form.addEventListener('submit', (event) => {
        const action = event.submitter.name;
        if (action === 'update') {
            let isValid = true;
            if (!validateArtname()) isValid = false;
            if (!isValid) event.preventDefault();
        }
    });

    artnameInput.addEventListener('input', validateArtname);
});
