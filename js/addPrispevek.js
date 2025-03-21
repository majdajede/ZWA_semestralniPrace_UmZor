document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action="addPrispevek.php"]');
    const artnameInput = document.getElementById('artname');
    const techniqueSelect = document.getElementById('technique');
    const photoInput = document.getElementById('photo');

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

    function validateTechnique() {
        clearError(techniqueSelect);
        if (techniqueSelect.value === '') {
            showError(techniqueSelect, 'Musíte vybrat uměleckou techniku.');
            return false;
        }
        return true;
    }

    // fotky
    function validatePhoto() {
        clearError(photoInput);
        const files = photoInput.files;
        if (!files || files.length === 0) {
            showError(photoInput, 'Musíte nahrát alespoň jednu fotku.');
            return false;
        }
        for (const file of files) {
            if (file.size > 4 * 1024 * 1024) {
                showError(photoInput, 'Fotky nesmí přesáhnout velikost 4MB.');
                return false;
            }
            if (file.type !== 'image/jpeg' && file.type !== 'image/png') {
                showError(photoInput, 'Fotky musí být ve formátu JPG nebo PNG.');
                return false;
            }
        }
        return true;
    }

    //validacka
    form.addEventListener('submit', (event) => {
        let isValid = true;

        if (!validateArtname()) isValid = false;
        if (!validateTechnique()) isValid = false;
        if (!validatePhoto()) isValid = false;

        if (!isValid) {
            event.preventDefault();
        }
    });

    artnameInput.addEventListener('input', validateArtname);
    techniqueSelect.addEventListener('change', validateTechnique);
    photoInput.addEventListener('change', validatePhoto);
});
