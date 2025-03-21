document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll('.slider-container');
    sliders.forEach(slider => {
        //nactem photos
        const photosDir = slider.dataset.photosdir || 'photos';
        //názvy fotek
        const photosData = slider.dataset.photos ? JSON.parse(slider.dataset.photos) : [];
        
        //sipky a <img>
        const leftArrow = slider.querySelector('.slider-arrow.left');
        const rightArrow = slider.querySelector('.slider-arrow.right');
        const imgTag = slider.querySelector('img');

        let currentIndex = 0;

        function updateImage(index) {
            // Slozim URL  "photos/soubor.jpg"
            imgTag.src = photosDir + '/' + photosData[index];
        }

        if (leftArrow && rightArrow) {
            leftArrow.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + photosData.length) % photosData.length;
                updateImage(currentIndex);
            });
            rightArrow.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % photosData.length;
                updateImage(currentIndex);
            });
        }
    });
});
